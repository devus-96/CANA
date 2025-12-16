<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        $data = TokenService::decode($token);
        if (!$data) {
            return response()->json([
              'message' => __("auth.invalid_token")
              ], 422);
        }

        $user = User::find($data->id);
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
