<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Services\JWTService;
use Illuminate\Cookie\CookieJar;
use Illuminate\Validation\Factory;
use Illuminate\Contracts\Routing\ResponseFactory;

class LoginController extends Controller
{
    public function store(Request $request, Validator $validator, CookieJar $cookie, ResponseFactory $response): JsonResponse
    {
        // get user from phone
        $user = User::where("email", "=", $request->email)->first();

        if ($user) {

            if ($user->verify_at) {

                $validator = $validator->make($request->all(), [
                    'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ]);

                if ($validator->fails()) {
                    return $response->json([
                        'statut' => 'error',
                        'message' => $validator->errors(),
                    ], 422);
                }



            } else {

                $emailToken = TokenService::generate([
                     'id' => $user->id
                ]);

                $user->link = url('/verify/email?token='.$emailToken);

                Mail::to($user->email)->send(new AccountCreated($user));

                return response()->json(['message' => 'Verification email resent.']);


            }

        } else {
            return response()->json(["data" => "0"], 404);
        }
    }
}
