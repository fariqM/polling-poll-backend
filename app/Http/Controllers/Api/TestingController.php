<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollingRequest;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function store()
    {
        // $sad = $request->file('question_img')->getRealPath();

        $bytes = substr(md5(time()), random_int(0, 9), random_int(5, 5));
        // $bytes =md5(mt_rand());

        // $answers = json_decode($request->answers);
        // $tes = '';
        // foreach ($answers as $key => $value) {
        //     $tes = $value->text;
        // }

        return response([
            // 'image1' => $request->file('question_img')->getClientOriginalName(),
            'image2' => $bytes
            // 'req' => $tes,
            // 'req' => $request->all()
        ]);
        // return response(['data' => $request->all()]);
    }

    public function testing_app()
    {
        return response(['message' => "Try me, i dare you..."], 200);
    }
}
