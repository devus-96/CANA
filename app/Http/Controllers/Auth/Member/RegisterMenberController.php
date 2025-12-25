<?php

namespace App\Http\Controllers\Auth\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

use App\Models\Member;
use App\Models\StateOfLive;

use App\Mail\AccountCreated;
use App\Http\Controllers\Controller;
use App\Services\JWTService;
use App\Rules\PhoneNumber;

class RegisterMenberController extends Controller
{
    public function __invoke(Request $request){
        //validation des donnees entrantes
        $validator = Validator::make($request->all(), [
            'first_name'    => 'string|required',
            'last_name'     => 'string|required',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'gender'        => 'required',
            'email'         => 'required|string|lowercase|email|max:255|unique:'.Member::class,
            'phone'         => ['required', 'string', 'unique:'.Member::class, new PhoneNumber],
            'date_of_birth' => 'date|before:today',
            'member_image'       => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
            "stateOfLive"     => "nullable|string",
            "city"          => "nullable|string",
            "parish"        => "nullable|string"
        ]);
        //si la validation echoue
        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }
        // find existing user with same email address
        $member = Member::where("email", "=", $request->email)->first();

        if (!$member) {
            // find existing user with same phone number
            $member = Member::where("phone", "=", $request->telephone)->first();
             //si le member n'existe pas on le cree
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

                Controller::uploadImages(['member_image' => $request->member_image], $member, 'member_image');
                if ($request->stateOfLive) {
                    // assignation de l'etat de vie
                    $stateOfLive = StateOfLive::where('name', $request->stateOfLive)->first();
                    if ($stateOfLive) {
                        $member->stateOfLive_id = $stateOfLive->id;
                    } else {
                        $member->stateOfLive_id =null;
                    }
                }
                //assignation du role au member
                $member->role_id = Controller::USER_ROLE_MEMBERS;
                $member->save();
                //envoi de l'email de verification
                $emailToken = JWTService::generate([
                    'id' => $member->id
                ]);

                $member->link = url('/verify/email?token='.$emailToken);

                Mail::to($member->email)->send(new AccountCreated($member));

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
