<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\JWTService;
use App\Models\Member;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->token;
        if (!$token) {
            return response()->json([
                'message' => __("auth.invalid_token")
                ], 422);
        }

        $data = JWTService::decode($token);
        if (!$data) {
            return response()->json([
              'message' => __("auth.invalid_token")
              ], 422);
        }

        $user = Admin::where('email', $data->id)->first() ?? Member::where('email', $data->id)->first();

        if (!$data) {
            return response()->json([
              'message' => __("auth.invalid_token")
              ], 422);
        }

        $user->update([
           'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'success'
        ], 200);
    }

}
