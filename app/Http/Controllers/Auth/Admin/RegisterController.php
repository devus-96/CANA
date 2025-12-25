<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Models\Admin;
use App\Models\Role;

use App\Http\Controllers\Controller;
use App\Rules\PhoneNumber;
use App\Services\JWTService;
use App\Mail\AccountCreated;


class RegisterController extends Controller
{
    public function __invoke (Request $request) {
        //validation des donnees entrantes
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|lowercase|email|max:255|unique:'.Admin::class,
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'         => ['required', 'string', 'unique:'.Admin::class, new PhoneNumber],
            'admin_image'       => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
        ]);
        //si la validation echoue
        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }
        // find existing admin with same email address
        $admin = Admin::where("email", "=", $request->email)->first();

        if (!$admin) {
            // find existing admin with same phone number
            $admin = Admin::where("phone", "=", $request->telephone)->first();
            //si l'admin n'existe pas on le cree
            if (!$admin) {

                $admin = Admin::query()->create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                ]);

                Controller::uploadImages(['admin_image' => $request->admin_image], $admin, 'admin_image');
                //assignation du role a l'admin
                $admin->role()->attach(
                    Role::where('name', Controller::USER_ROLE_ADMIN)->first()->id
                );
                //envoi de l'email de verification
                $emailToken = JWTService::generate([
                    'id' => $admin->id
                ], 3600);

                $admin->link = url('/verify/email?token='.$emailToken);

                Mail::to($admin->email)->send(new AccountCreated($admin));

                return response()->json(['message' => 'Verification email resent.'], 200);

            } else {
                // phone number is already used
                return response()->json([
                    'message' => 'phone number is already use',
                    'status' => 'error'
                ], 404);
            }
        } else {
                // ce compte existe déjà (email déjà utilisé)
                return response()->json([
                    'message' => 'email already used',
                    'status' => 'error'
                ], 404);
        }
    }
}
