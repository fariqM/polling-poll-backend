<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Polling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class VotersController extends Controller
{
    public function checkDevice($dir, $deviceId)
    {
        $data = Polling::where('dir', $dir)->with(['answers' => function ($query) use ($deviceId) {
            $query->with(['voters' => function ($votq) use ($deviceId) {
                $votq->where('device_id', $deviceId);
            }]);
        }])->get();
        return response()->json(['data' => $data], 200, [],JSON_NUMERIC_CHECK);
        // return response(['data' => $data]);
    }
    public function checkPassword($dir, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $polling = Polling::where('dir', $dir)->firstOrFail();

        if (Hash::check($request->password, $polling->password)) {
            return response(['success' => true]);
        } else {
            return response(['success' => false, 'errors' => ['password' => ["Password doesn't match"]], 'dir' => $dir], 403);
        }
    }
}
