<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Http\Requests\AdminRequest;
use App\Rules\PhoneNumber;
use App\Services\JWTService;
use App\Mail\AccountCreated;


class RegisterController extends Controller
{
    public function __invoke (Request $request) {

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|lowercase|email|max:255|unique:'.Admin::class,
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'         => ['required', 'string', 'unique:'.Admin::class, new PhoneNumber],
            'profile'       => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // find existing admin with same email address
        $admin = Admin::where("email", "=", $request->email)->first();

        if (!$admin) {

            try {

                DB::beginTransaction();

                // find existing admin with same phone number
                $admin = Admin::where("phone", "=", $request->telephone)->first();

                if (!$admin) {

                    $admin = Admin::query()->create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'phone' => $request->phone,
                    ]);

                    Controller::uploadImages(['image' => $request->image], $admin);

                    //$admin->roles()->attach(
                    //    Role::where('name', Controller::USER_ROLE_ADMIN)->first()->id
                    //);

                    $emailToken = JWTService::generate([
                        'id' => $admin->id
                    ], 3600);

                    $admin->link = url('/verify/email?token='.$emailToken);

                    Mail::to($admin->email)->send(new AccountCreated($admin));

                    DB::commit();
                    return response()->json(['message' => 'Verification email resent.']);

                } else {
                    return response()->json(["data" => "-2", 'message' => 'phone number is already use'], 404);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
            }
        } else {
                return response()->json(["data" => "-2"], 404);     // ce compte existe déjà (téléphone déjà utilisé)
        }
    }
}
