<?php

namespace App\Http\Controllers\Auth\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

use App\Models\Member;
use App\Models\StateOfLive;
use App\Models\Role;

use App\Mail\AccountCreated;
use App\Http\Controllers\Controller;
use App\Mail\SendCodeConfirmation;
use App\Services\JWTService;
use App\Rules\PhoneNumber;

class RegisterMenberController extends Controller
{
    public function __invoke(Request $request){

        $validator = Validator::make($request->all(), [
            'first_name'    => 'string|required',
            'last_name'     => 'string|required',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'gender'        => 'required',
            'email'         => 'required|string|lowercase|email|max:255|unique:'.Member::class,
            'phone'         => ['required', 'string', 'unique:'.Member::class, new PhoneNumber],
            'date_of_birth' => 'date|before:today',
            'profile'       => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
            "stateOfLive"     => "nullable|string",
            "city"          => "nullable|string",
            "parish"        => "nullable|string"
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // find existing user with same email address
        $member = Member::where("email", "=", $request->email)->first();

        if (!$member) {
            // find existing user with same phone number
            $member = Member::where("phone", "=", $request->telephone)->first();

            if (!$member) {

                $member = Member::query()->create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'gender' => $request->gender,
                    'date_of_birth' => $request->date_of_birth,
                    'city' => $request->city,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    "parish" => $request->parish,
                ]);

                Controller::uploadImages(['profile' => $request->image], $member);

                $stateOfLive = StateOfLive::where('name', "=", $validator->stateOfLive)->fisrt();
                $role = Role::where('name', '=', $validator->role)->fisrt();

                $member->stateOfLive_id = $stateOfLive->id;
                $member->role_id = $role->id;

                $member->save();

                $emailToken = JWTService::generate([
                    'id' => $member->id
                ]);

                $member->link = url('/verify/email?token='.$emailToken);

                Mail::to($member->email)->send(new AccountCreated($member));

                return response()->json(["data" => '1', 'message' => 'Verification email resent.'], 200);

            } else {
                return response()->json(["data" => "-2", 'message' => 'phone number is already use'], 404);
            }
        } else {
              return response()->json(["data" => "-1", 'message' => 'account already exist !'], 404);
        }
    }
}
