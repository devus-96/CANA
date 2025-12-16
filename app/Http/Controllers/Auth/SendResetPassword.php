<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SendResetPassword extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No user found with this email.'], 404);
        }

        $token = TokenService::generate([
            'id' => $user->id
        ]);

        $user->link = url("/verify/password?token=".$token);

        Mail::to($user->email)->send(new PasswordReset($user));

        return response()->json(['message' => 'Password reset email sent.']);
    }

}
