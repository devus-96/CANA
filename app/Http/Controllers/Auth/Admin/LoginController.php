<?php

namespace App\Http\Controllers\Auth\Admin;

use Carbon\Carbon;

use App\Models\Admin;
use App\Models\VoteValidationAdmin;
use App\Http\Controllers\Controller;
use App\Services\JWTService;
use App\Mail\AccountCreated;
use App\Models\LoginConfirmation;
use App\Models\Connexion;
use App\Mail\SendCodeConfirmation;

use Illuminate\Support\Facades\DB;
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
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|lowercase|email|max:255',
            'password' => ['required', Rules\Password::defaults()],
            'device' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // get admin from phone
        $admin = Admin::where("email", "=", $request->email)->first();

        if ($admin) {

            if($admin->status == "BLOCKED"){
                return response()->json(["message" => 'vous avez ete blocker'], 404);
            }

            if ($admin->is_verified) {

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

                    $confirm = LoginConfirmation::where("device", "=", $request->device)
                                        ->where("email", "=", $admin->email)
                                        ->first();

                    // if confirmation exists
                    if($confirm){
                        // update confirmation
                        $confirm->update([
                            "device"          => $request->device,
                            "email"             => $admin->email,
                            "code"              => $code
                        ]);

                    }else{
                        // else create a new confirmation
                        $confirm = LoginConfirmation::create([
                            'device'          => $request->device,
                            "email"             => $admin->email,
                            'code'              => $code,
                        ]);
                    }

                    // send code to email
                    Mail::to($request->email)->send(new SendCodeConfirmation($code));

                    return response()->json(["data" => ["email" => $admin->email]], 200);
                }
            } else {

                $emailToken = JWTService::generate([
                     'id' => $admin->id
                ], 3600);

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
        $confirm = LoginConfirmation::where("device", "=", $request->device)
                                    ->where("email", "=", $request->email)
                                    ->where("code", "=", $request->code)
                                    ->first();

        if ($confirm && Controller::checkLoginCode($confirm))  {

            $admin = Admin::where("email", "=", $request->email)->first();

            [$secret, $tokenHash] = Controller::generateOpaqueToken();

            // find existing connection
            $con = Connexion::where("device", "=", $request->device)
                            ->where("navigator", "=", $request->navigator)
                            ->first();
            // if connection exists
            if($con){
                // update token
                $con->update([
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
                $con = $admin->connexion()->create([
                    'device'          => $request->device,
                    'ip_address'        => $request->ip_address,
                    'city'             => $request->city,
                    'navigator'        => $request->navigator,
                    'expired_at'        => now()->addDays(7),
                    'token'             => $tokenHash
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
            return response()->json(["data" => "success"], 200)->withCookie($refreshCookie);;
        } else {
             return response()->json(["message" => "code incorret"], 404);
        }
    }
}
