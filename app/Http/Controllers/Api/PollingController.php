<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollingRequest;
use App\Models\Answer;
use App\Models\Polling;
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
        //     $a_file_name = 'tes' . '.' . $file->getClientOriginalExtension();
        //     $file->storeAs('public/img/answers', $a_file_name);
        //     array_push($testArr, $file->getClientOriginalExtension());
        // }
        // return response($testArr);


        $url = $this->substr5($this->CSPRNG(10), true);
        $file_name = null;
        $answers = json_decode($request->answers);
        $a_file_name_collection = [];
        $final_url = '';

        try {
            // store question img if exist
            if ($request->file('q_img')) {
                $q_img = $request->file('q_img');
                $file_name =  $url . '.' . $q_img->getClientOriginalExtension();
                $q_img->storeAs('public/img', $file_name);
            }

            // create new poll data
            $create_poll = Polling::create(array_merge($request->all(), ['dir' => $url, 'q_img' =>  $file_name]));

            // create answers data
            foreach ($answers as $key => $value) {
                // $newRand = $this->substr5(time(), true);

                if ($answers[$key]->img_file) {
                    $a_img = $this->substr5(time(), true);
                    array_push($a_file_name_collection, $a_img);
                } else {
                    $a_img = null;
                }

                Answer::create([
                    'polling_id' => $create_poll->id,
                    'text' => $value->text,
                    'a_img' => $a_img
                ]);
            }

            foreach ($request->file('a_img') as $key => $file) {
                // if ($answers[$key]->img_file) {
                //     $a_file_name = $a_file_name_collection[$key] . '.' . $file->getClientOriginalExtension();
                //     $file->storeAs('public/img/answers', $a_file_name);
                //     return response($file);
                // }
                $a_file_name = $a_file_name_collection[$key] . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/img/answers', $a_file_name);
            }

            $final_url = $create_poll->dir;
        } catch (\Throwable $th) {
            return response(['success' => false, 'message' => $th->getMessage()], 500);
        }

        return response(['success' => true, 'url' => $final_url]);
    }
}
