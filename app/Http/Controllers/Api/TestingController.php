<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollingRequest;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function store(PollingRequest $request)
    {
        // $sad = $request->file('question_img')->getRealPath();

        // $bytes = substr(md5(time()), random_int(0,9), random_int(5,5));
        // $bytes =md5(mt_rand());

        $answers = json_decode($request->answers);
        $tes = '';
        foreach ($answers as $key => $value) {
            $tes = $value->text;
        }

        return response([
            // 'image1' => $request->file('question_img')->getClientOriginalName(),
            // 'image2' => $request->file('img2')->getClientOriginalName()
            // 'req' => $tes,
            'req' => $request->all()
        ]);
        // return response(['data' => $request->all()]);
    }
}
