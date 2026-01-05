<?php

namespace App\Http\Controllers\Auth\Admin;

use Carbon\Carbon;

use App\Models\Admin;
use App\Models\RefreshToken;
use App\Http\Controllers\Controller;
use App\Services\JWTService;
use App\Models\LoginConfirmation;
use App\Mail\SendCodeConfirmation;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        //validation des donnees entrantes
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|lowercase|email|max:255',
            'password' => ['required', Rules\Password::defaults()],
            'device' => 'required|string'
        ]);
        //si la validation echoue
        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }
        // find existing admin with same email address
        $admin = Admin::where("email", "=", $request->email)->first();

        if (!$admin) {
            // check if admin is blocked
            if($admin->blocked_at){
                return response()->json(["message" => 'vous avez ete blocker'], 404);
            }
            // verify password
            if (! $admin || ! Hash::check($request->password, $admin->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
             // generate and send login code for 2FA
            $code = Controller::generateCode();

            $confirm = LoginConfirmation::where("device", "=", $request->device)
                                ->where("email", "=", $admin->email)
                                ->first();
            // if confirmation exists
            if($confirm){
                // update confirmation
                $confirm->update([
                    "device"            => $request->device,
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
        } else {
            return response()->json([
                'message' => 'Admin not found',
                'status' => 'error'
            ], 404);
        }
    }


    public function create (Request $request) {
        // get login confirmation
        $confirm = LoginConfirmation::where("device", "=", $request->device)
                                    ->where("email", "=", $request->email)
                                    ->where("code", "=", $request->code)
                                    ->first();
        // if confirmation exists and code is valid
        if ($confirm && Controller::checkLoginCode($confirm))  {
            try {
                DB::beginTransaction();

                $admin = Admin::where("email", "=", $request->email)->first();

                [$secret, $tokenHash] = Controller::generateOpaqueToken();

                // 2. Générer le JWT
                $token = JWTService::generate([
                    "id" => $admin->id,
                ], 3600);

                $connexion = RefreshToken::where("refreshable_id", "=", $admin->id)
                                        ->where("refreshable_type", "=", "admins")->first();

                if ($connexion) {
                    // update token
                    $connexion->update([
                        "token"  => $tokenHash,
                        "expired_at" => now()->addDays(7)
                    ]);

                } else {
                    // create new token
                    $connexion = RefreshToken::create([
                        "refreshable_id" => $admin->id,
                        "refreshable_type" => "admins",
                        "token" => $tokenHash,
                        "expired_at" => now()->addDays(7)
                    ]);
                }
                // create refresh cookie
                $refreshCookie = Cookie::make(
                    'token',
                    $connexion->id . '.' . $secret,
                    60 * 24 * 30, // Durée de 30 jours
                    '/',
                    null,
                    true, // Secure (nécessite HTTPS)
                    true  // HttpOnly (empêche JS d'y accéder)
                );
                // delete confirmation
                $confirm->delete();
                // 4. Retourner JSON (pas de redirection)
                return response()->json([
                    'status' => 'success',
                    'user' => $admin,
                    'token' => $token
                ], 200 )->withCookie($refreshCookie);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'failed', 'error' => $e->getMessage()], 500);
            }
        } else {
             return response()->json(["message" => "code incorret"], 404);
        }
    }
}
