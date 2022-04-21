<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollingRequest;
use App\Models\Answer;
use App\Models\Polling;
use App\Models\Voter;
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

    function substr5($value, $isUsedMD5 = false, $lenght = 5)
    {
        if ($isUsedMD5) {
            return substr($this->conv_md5($value), random_int(0, 25), $lenght);
        } else {
            $val_length = strlen($value);
            if ($val_length <= $lenght) {
                return substr($this->conv_md5($value), random_int(0, 25), $lenght);
            } else {
                return substr($value, random_int(1, $val_length - $lenght), $lenght);
            }
        }
    }

    public function substr5_test(Request $request)
    {

        $isUsedMD5 = intval($request->isMd5) == 0 ? false : true;
        $value = $request->val;
        $lenght = intval($request->length);
        $all_req = [
            'md5' => $isUsedMD5,
            'val' => $value,
            'length' => $lenght
        ];

        // return response(Polling::where('dir', 'e82asd53')->count());

        try {
            if ($isUsedMD5) {
                return response([
                    'result' => substr($this->conv_md5($value), random_int(0, 25), $lenght),
                    'data' => $all_req,
                    'res' => 'kondisi 1'
                ]);
            } else {
                $val_length = strlen($value);
                if ($val_length <= $lenght) {
                    return response([
                        'result' => substr($this->conv_md5($value), random_int(0, 25), $lenght),
                        'data' => $all_req,
                        'res' => 'kondisi 2'
                    ]);
                } else {
                    return response([
                        'result' => substr($value, random_int(1, $val_length - $lenght), $lenght),
                        'data' => $all_req,
                        'res' => 'kondisi 3',
                        'var_leng' => $val_length
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return response(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function getPollUrl()
    {
        $condition = true;
        $result  = null;

        while ($condition) {
            $url = $this->substr5($this->CSPRNG(10), true);
            $recordExist = Polling::where('dir', $url)->count();
            if ($recordExist == 0) {
                $condition = false;
                $result = $url;
            }
        }

        return $result;
    }

    public function store(PollingRequest $request)
    {
        $url = $this->getPollUrl();
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
                    $format = $file->getClientOriginalExtension();
                    $idx = substr($originalName, 0, strpos($originalName, "."));

                    $storeName = $this->substr5(random_int(100000000000, 999999999999), true, 10) . '.' . $format;

                    array_push($a_file_collection, [
                        'indx' => intval($idx),
                        'format' => $format,
                        'storeName' =>  $storeName
                    ]);

                    $file->storeAs('public/img/answers', $storeName);
                }
            }

            // modified the answers array to include the filename
            // add property img_file to answers array that contain file name
            // ['img_file' => 'asdasd.jpg']
            foreach ($a_file_collection as $key => $value) {
                $answers[$value['indx']]->img_file = $value['storeName'];
            }

            // create answers data
            foreach ($answers as $key => $answer) {
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

    public function update(Polling $polling, PollingRequest $request)
    {
        $answers = json_decode($request->answers);
        $a_file_collection = [];
        $newPolling = [];
        try {
            // img question handler
            if ($request->file('q_img')) {
                $q_img = $request->file('q_img');
                if ($polling->q_img == null) {
                    $file_name =  $polling->dir . '.' . $q_img->getClientOriginalExtension();
                    $q_img->storeAs('public/img', $file_name);
                    $newPolling = array_merge($newPolling, ['q_img' => $file_name]);
                } else {
                    $q_img->storeAs('public/img', $polling->q_img);
                    $newPolling = array_merge($newPolling, ['q_img' => $polling->q_img]);
                }
            } else {
                if ($request->q_img == 'null') {
                    $newPolling = array_merge($newPolling, ['q_img' => null]);
                    // return response("foto hilang");
                } else {
                    $newPolling = array_merge($newPolling, ['q_img' => $polling->q_img]);
                }
            }

            if ($request->with_password == 1) {
                $password = Hash::make($request->password);
                $newPolling = array_merge($newPolling, ['password' => $password]);
            } else {
                $newPolling = array_merge($newPolling, ['with_password' => $polling->with_password]);
            }

            $polling->update(array_merge($request->all(), $newPolling));

            // answers img handler
            if ($request->file('a_img') !== null) {

                foreach ($request->file('a_img') as $key => $file) {
                    $originalName = $file->getClientOriginalName();
                    $format = $file->getClientOriginalExtension();
                    $idx = substr($originalName, 0, strpos($originalName, "."));

                    // divide into 2 conditions
                    // first handle new img (replacement image) from existing answer
                    // second one is from new answer.
                    // ## weird algorithm, i know. :) but it work. (y)
                    if (property_exists($answers[$idx], 'a_img') && $answers[$idx]->a_img !== null) {
                        $file->storeAs('public/img/answers', $answers[$idx]->a_img);
                    } else {
                        $storeName = $this->substr5(random_int(100000000000, 999999999999), true, 10) . '.' . $format;
                        $file->storeAs('public/img/answers', $storeName);

                        // Make new statement array just to tell the next step about the file 
                        // that has been stored
                        array_push($a_file_collection, [
                            'indx' => intval($idx),
                            'format' => $format,
                            'storeName' =>  $storeName
                        ]);
                    }
                }
            }

            // modified the answers array to include the filename
            // add property a_img to answers array that contain file name
            // E.g ['a_img' => 'asdasd.jpg']
            // ## another weird algorithm.
            foreach ($a_file_collection as $key => $value) {
                $answers[$value['indx']]->a_img = $value['storeName'];
            }

            // handle if there existing id on DB, then just update the record
            // if not, so create one.
            // but we cant handle if the user delete the old answer and make new one :)))))
            // i have an idea. let throw this problem to another api :)
            foreach ($answers as $key => $value) {
                Answer::updateOrCreate(['id' => $value->id], [
                    'polling_id' => $polling->id,
                    'text' => $value->text,
                    'a_img' =>  $request->old_a_img[$key] == 'null' || $request->old_a_img[$key] == null ? null : $value->a_img
                ]);
            }
        } catch (\Throwable $th) {
            return response(['success' => false, 'message' => $th->getMessage()], 500);
        }
        return response(['success' => true]);
    }

    public function submitPoll(Answer $answer, Request $request)
    {
        // return response(['data' => $request->all()]);
        try {
            $voter = Voter::create([
                'answer_id' => $answer->id,
                'name' => $request->name,
                'email' => $request->email,
                'device_id' => $request->device_id,
                'is_verified' => 1
            ]);
        } catch (\Throwable $th) {
            return response(['success' => false, 'message' => $th->getMessage()], 500);
        }
        return response(['success' => true]);
    }

    public function show($url)
    {
        $polling = Polling::where('dir', $url)->with('answers')->firstOrFail();
        return response(['data' => $polling]);
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
    public function edit($deviceID, $dir)
    {
        $data = Polling::where('owner_id', $deviceID)->where('dir', $dir)->with('answers.voters')->firstOrFail();

        return response(['data' => $data]);
    }
}
