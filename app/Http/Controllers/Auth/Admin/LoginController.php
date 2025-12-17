<?php

namespace App\Http\Controllers\Auth\Admin;

use Carbon\Carbon;

use App\Models\Admin;
use App\Models\VoteValidationAdmin;
use App\Models\RefreshToken;
use App\Http\Controllers\Controller;
use App\Services\JWTService;
use App\Mail\AccountCreated;
use App\Models\LoginConfirmation;
use App\Models\Connexion;
use App\Mail\SendCodeConfirmation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Factory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|lowercase|email|max:255|unique:'.Admin::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // get admin from phone
        $admin = Admin::where("email", "=", $request->email)->first();

        if ($admin) {

            if($user->status == "BLOCKED"){
                return response()->json(["message" => 'vous avez ete blocker'], 404);
            }

            if ($admin->verify_at) {

                if (! $admin || ! Hash::check($request->password, $admin->password)) {
                    return response()->json(['message' => 'Invalid credentials'], 401);
                }

                if ($admin->status !== 'ACTIVE') {
                    switch ($admin->status) {
                        case "PENDING":
                            return response()->json(['message' => "votre compte est en attente d'activation"], 404);
                        case "REJECTED":
                            return response()->json(['message' => "votre requete a ete rejete"], 403);
                            break;
                    }
                } else{
                   $code = Controller::generateCode();

                    // if confirmation exists
                    if($confirm){
                        // update confirmation
                        $confirm->update([
                            "appareil"          => $request->appareil,
                            "email"             => $user->email,
                            "code"              => $code
                        ]);

                    }else{
                        // else create a new confirmation
                        $confirm = LoginConfirmation::create([
                            'appareil'          => $request->appareil,
                            "email"             => $user->email,
                            'code'              => $code,
                        ]);
                    }

                    // send code to email
                    Mail::to($request->email)->send(new SendCodeConfirmation($code));

                    // return success
                    return response()->json(["data" => ["email" => $user->email]], 200);

                }
            } else {

                $emailToken = JWTService::generate([
                     'id' => $admin->id
                ]);

                $admin->link = url('/verify/email?token='.$emailToken);

                Mail::to($admin->email)->send(new AccountCreated($admin));

                return response()->json(['message' => 'Verification email resent.', 200]);

            }

        } else {
            return response()->json(["data" => "0"], 404);
        }
    }


    public function create (Request $request) {
        // get login confirmation
        $confirm = LoginConfirmation::where("appareil", "=", $request->appareil)
                                    ->where("email", "=", $request->email)
                                    ->where("code", "=", $request->code)
                                    ->first();


        if ($request->code === $confirm->code)  {
            [$secret, $tokenHash] = Controller::generateOpaqueToken();

            // find existing connection
            $con = Connexion::where("appareil", "=", $request->appareil)
                            ->where("navigateur", "=", $request->navigateur)
                            ->first();
            // if connection exists
            if($con){
                // update token
                $con->update([
                    "status"            => "ACTIF",
                    "token"             => $tokenHash,
                ]);

                $refreshCookie = Cookie::make(
                    'token',
                    $secret,
                    60 * 24 * 30, // Durée de 30 jours
                    '/',
                    null,
                    true, // Secure (nécessite HTTPS)
                    true  // HttpOnly (empêche JS d'y accéder)
                );

            } else {
                $con = Connexion::create([
                    'appareil'          => $request->appareil,
                    'ip_address'        => $user_infos->ip_address,
                    'ville'             => $user->ville,
                    'navigateur'        => $user->navigateur,
                    'expires_at'        => $user->expires_at,
                    "status"            => $user_infos->status,
                    'token'             => $tokenHash,
                ]);

                $refreshCookie = Cookie::make(
                    'token',
                     $secret,
                    60 * 24 * 30, // Durée de 30 jours
                    '/',
                    null,
                    true, // Secure (nécessite HTTPS)
                    true  // HttpOnly (empêche JS d'y accéder)
                );
            }
            // delete confirmation
            $confirm->delete();

            // return infos user
            return response()->json(["data" => ""], Response::HTTP_OK);
        } else {
             return response()->json(["message" => "code incorret"], 404);
        }
    }
}
