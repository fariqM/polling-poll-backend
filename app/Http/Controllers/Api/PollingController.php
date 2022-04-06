<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollingRequest;
use App\Models\Answer;
use App\Models\Polling;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PollingController extends Controller
{
    // CSPRNG function
    function CSPRNG(
        int $length = 64,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    function conv_md5($value)
    {
        return md5($value);
    }

    function substr5($value, $isUsedMD5 = false)
    {
        if ($isUsedMD5) {
            return substr($this->conv_md5($value), random_int(0, 25), 5);
        } else {
            $length = strlen($value);
            if ($length < 5) {
                return substr($this->conv_md5($value), random_int(0, 25), 5);
            } else {
                return substr($value, random_int(1, $length - 5), 5);
            }
        }
    }

    public function store(PollingRequest $request)
    {
        // $testArr = [];
        // foreach ($request->file('a_img') as $key => $file) {
        //     $originalName = $file->getClientOriginalName();
        //     $format = substr($originalName, strpos($originalName, ".") + 1);
        //     $name = substr($originalName, 0, strpos($originalName, "."));
        //     array_push($testArr, [
        //         'idx' => intval($name),
        //         'format' => $format 
        //     ]);
        // }
        // // for ($i=0; $i < 3; $i++) { 
        // //     array_push($testArr, $request->a_img[$i]);
        // // }
        // return response([
        //     // 'arr' => $testArr, 
        //     'req' => $request->all()
        // ]);



        $url = $this->substr5($this->CSPRNG(10), true);
        $file_name = null;
        $answers = json_decode($request->answers);
        $a_file_collection = [];
        $final_url = '';

        try {
            // store question img if exist
            if ($request->file('q_img')) {
                $q_img = $request->file('q_img');
                $file_name =  $url . '.' . $q_img->getClientOriginalExtension();
                $q_img->storeAs('public/img', $file_name);
            }

            // create new poll data
            if ($request->with_password == 1) {
                $password = Hash::make($request->password);
            } else {
                $password = $request->password;
            }
            $create_poll = Polling::create(array_merge($request->all(), [
                'dir' => $url,
                'q_img' =>  $file_name,
                'password' => $password
            ]));

            // store answer img
            if ($request->file('a_img') !== null) {
                foreach ($request->file('a_img') as $key => $file) {
                    $originalName = $file->getClientOriginalName();
                    return response([
                        // 'arr' => $testArr, 
                        'req' => $originalName
                    ]);
                    $format = $file->getClientOriginalExtension();
                    $idx = substr($originalName, 0, strpos($originalName, "."));
    
                    $storeName = $this->substr5(time(), true) . '.' . $format;
    
                    array_push($a_file_collection, [
                        'indx' => intval($idx),
                        'format' => $format,
                        'storeName' =>  $storeName
                    ]);
    
                    $file->storeAs('public/img/answers', $storeName);
                }
            }

            // modified the answers array to include the filename
            foreach ($a_file_collection as $key => $value) {
                $answers[$value['indx']]->img_file = $value['storeName'];
            }
            // return response($answers);

            // create answers data
            foreach ($answers as $key => $answer) {
                // return response(['data' => $answer->img_file]);

                Answer::create([
                    'polling_id' => $create_poll->id,
                    'text' => $answer->text,
                    'a_img' => $answer->img_file
                ]);
            }

            $final_url = $create_poll->dir;
        } catch (\Throwable $th) {
            return response(['success' => false, 'message' => $th->getMessage()], 500);
        }

        return response(['success' => true, 'url' => $final_url]);
    }

    public function show($url)
    {
        $polling = Polling::where('dir', $url)->firstOrFail();
        return response($polling);
    }

    public function index($deviceID)
    {
        if (Auth::check()) {
            try {
                $data = Polling::where('owner_id', Auth::id())->with('answers.voters')->get();
            } catch (\Throwable $th) {
                return response(['success' => false, 'message' => $th->getMessage()], 500);
            }
            return response(['data' => $data]);
        } else {
            try {
                $data = Polling::where('owner_id', $deviceID)->with('answers.voters')->get();
            } catch (\Throwable $th) {
                return response(['success' => false, 'message' => $th->getMessage()], 500);
            }
            return response(['data' =>  $data]);
        }
    }
}
