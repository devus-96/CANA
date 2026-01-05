<?php

namespace App\Http\Controllers\Auth\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

use Inertia\Inertia;
use Inertia\Response;

use App\Models\Member;
use App\Models\Role;

use App\Mail\AccountCreated;
use App\Http\Controllers\Controller;
use App\Services\JWTService;
use App\Rules\PhoneNumber;

class RegisterMemberController extends Controller
{

    /*
        Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }



    public function store (Request $request){
        //validation des donnees entrantes
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|required',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'gender'        => 'required',
            'email'         => 'required|string|lowercase|email|max:255|unique:'.Member::class,
            'phone'         => ['required', 'string', 'unique:'.Member::class, new PhoneNumber],
            'date_of_birth' => 'date|before:today',
            "city"          => "nullable|string",
        ]);
        //si la validation echoue
        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        $member = Member::query()->create([
            'name' => $request->name,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'city' => $request->city,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $role = Role::where('name', Controller::USER_ROLE_MEMBERS)->first();

        if (!$role) {
            $role = Role::create([
                'name' => Controller::USER_ROLE_MEMBERS,
                'description' => 'Consultation publique et réservation d\'événements',
                    'permissions' => json_encode([
                    'site.view',
                    'blog.read',
                    'meditations.read',
                    'media.view',
                    'events.view',
                    'reservations.create',
                    'reservations.view_own',
                    'payments.make',
                    'donations.make',
                    'contact.send',
                    'etats_vie.view'
                ]),
            ]);
        }

        //assignation du role au member
        $member->role_id =  $role->id;
        $member->save();
        //envoi de l'email de verification
        $emailToken = JWTService::generate([
            'id' => $member->id
        ]);

        $member->link = url(route('member.emailVerify', ['token' => $emailToken]));

        Mail::to($member->email)->send(new AccountCreated($member));

        return response()->json(['message' => 'Verification email resent.'], 200);
    }
}
