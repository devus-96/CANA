<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\AdminRequest;

class RegisterController extends Controller
{
    public function __invoke (AdminRequest $request) {
        $validator = $request->validated();

        if ($validator->fails()) {
            return $response->json([
                'statut' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        // find existing user with same email address
        $user = User::where("email", "=", $validator->email)->first();

        if (!$user) {

            // find existing confirmation
            $confirm = User::where("email", "=", $validator->email)
                            ->where("role", "=", $validator->role)
                                ->first();

            $user = [
                'name' => $validator->name,
                'email' => $validator->email,
                'password' => Hash::make($validator->password),
                'phone' => $validator->phone_number,
                "role" => $validator->role,
                "fonction" => $validator->fonction
            ];

            if (!$confirm) {
                $user = User::query()->create($user);
            } else {
                $user = User::query()->update($user);
            }

            Controller::uploadImages(['image' => $request->image], $user);

            $role = Role::where('name', '=', $validator->role)->fisrt();
            $user->role_id = $role->id;


            $emailToken = JWTService::generate([
                'id' => $user->id
            ]);

            $user->link = url('/verify/email?token='.$emailToken);

            Mail::to($user->email)->send(new AccountCreated($user));

            return response()->json(['message' => 'Verification email resent.']);

        } else {
                return response()->json(["data" => "-2"], 404);     // ce compte existe déjà (téléphone déjà utilisé)
        }



    }
}
