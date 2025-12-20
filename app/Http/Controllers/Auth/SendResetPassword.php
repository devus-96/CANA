<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Menber;
use App\Services\JWTService;
use App\Mail\PasswordReset;
use Illuminate\Support\Facades\Mail;

class SendResetPassword extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = Admin::where('email', $request->email)->first() ?? Menber::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No user found with this email.'], 404);
        }

        $token = JWTService::generate([
            'id' => $user->id
        ]);

        $user->link = url("/verify/password?token=".$token);

        Mail::to($user->email)->send(new PasswordReset($user));

        return response()->json(['message' => 'Password reset email sent.']);
    }

}
