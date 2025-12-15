<?php

namespace App\Http\Controllers\Auth\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\StateOfLive;
use App\Models\Role;

use App\Http\Controllers\Controller;
use App\Mail\SendCodeConfirmation;
use App\Services\JWTService;
use App\Http\Requests\MemberRequest;

class RegisterController extends Controller
{
    public function __invoke(MemberRequest $request, Validator $validator){

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

            DB::beginTransaction();

            // find existing user with same phone number
            $user = User::where("phone", "=", $validator->telephone)->first();

            if (!user) {

                // find existing confirmation
                $confirm = User::where("email", "=", $validator->email)
                                ->where("role", "=", $validator->role)
                                 ->first();

                $user = [
                    'first_name' => $validator->first_name,
                    'last_name' => $validator->last_name,
                    'gender' => $validator->gender,
                    'date_of_birth' => $validator->date_of_birth,
                    'city' => $validator->city,
                    'email' => $validator->email,
                    'password' => Hash::make($validator->password),
                    'phone_number' => $validator->phone_number,
                    "parish" => $validator->parish,
                ];

                //si l'utilisateur existe alors on le modifie
                if (!$confirm) {
                    $user = User::query()->create($user);
                } else {
                    $user = User::query()->update($user);
                }

                Controller::uploadImages(['profile' => $validator->image], $user);

                $stateOfLive = StateOfLive::where('name', "=", $validator->stateOfLive)->fisrt();
                $role = Role::where('name', '=', $validator->role)->fisrt();

                $user->stateOfLive_id = $stateOfLive->id;
                $user->role_id = $role->id;

                $user->save();

                $emailToken = JWTService::generate([
                    'id' => $user->id
                ]);

                $user->link = url('/verify/email?token='.$emailToken);

                Mail::to($user->email)->send(new AccountCreated($user));

                DB::commit();
                return response()->json(["data" => '1', 'message' => 'Verification email resent.'], 200);

            } else {
                 return response()->json(["data" => "-2"], 404);     // ce compte existe déjà (téléphone déjà utilisé)
            }

        } else {
              return response()->json(["data" => "-1"], 404);     // ce compte existe déjà (email déjà utilisée)
        }
    }
}
