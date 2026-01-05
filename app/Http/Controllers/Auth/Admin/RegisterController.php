<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;

use Inertia\Inertia;
use Carbon\Carbon;

use App\Models\Admin;
use App\Models\Role;
use App\MOdels\Invitation;
use App\MOdels\RefreshToken;

use App\Http\Controllers\Controller;
use App\Rules\PhoneNumber;
use App\Services\JWTService;


class RegisterController extends Controller
{

    /*
        Show the registration page.
     */
    public function create(Request $request)
    {
        $token = $request->query('token');
        $invitationId = $request->query('invitation_id');

        if (!$token || !$invitationId) {
            return;
        }

        $invitation = Invitation::find($invitationId);

        if (!$invitation || !Hash::check($token, $invitation->token)) {
            return;
        }

        if (Carbon::parse($invitation->expired_at)->isPast()) {
            abort(403, 'Lien invalide ou expiré');
        }

        return Inertia::render('admin/auth/register');
    }

    public function store (Request $request) {
        //validation des donnees entrantes
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|lowercase|email|max:255|unique:'.Admin::class,
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'         => ['required', 'string', 'unique:'.Admin::class, new PhoneNumber],
            'admin_image'   => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
            'fonction'      => 'nullable|string|max:255',
        ]);
        //si la validation echoue
        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

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

        [$secret, $tokenHash] = Controller::generateOpaqueToken();

        // 2. Générer le JWT
        $token = JWTService::generate([
            "id" => $admin->id,
        ], 3600);
        // create new token
        $connexion = RefreshToken::create([
            "refreshable_id" => $admin->id,
            "refreshable_type" => "admins",
            "token" => $tokenHash,
            "expired_at" => now()->addDays(7)
        ]);
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
         // 4. Retourner JSON (pas de redirection)
        return response()->json([
            'status' => 'success',
            'user' => $admin,
            'token' => $token
        ], 200 )->withCookie($refreshCookie);
    }
}
