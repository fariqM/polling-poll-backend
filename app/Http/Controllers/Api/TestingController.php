<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function store(Request $request)
    {
        // $sad = $request->file('question_img')->getRealPath();

        $bytes = random_int(1, 9999);

        return response([
            // 'image1' => $request->file('question_img')->getClientOriginalName(),
            // 'image2' => $request->file('img2')->getClientOriginalName()
            'address' => bin2hex(openssl_random_pseudo_bytes($bytes))
        ]);
        // return response(['data' => $request->all()]);
    }
}
