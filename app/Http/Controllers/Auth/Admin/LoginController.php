<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Models\Admin;
use App\Models\VoteValidationAdmin;
use App\Models\RefreshToken;
use App\Http\Controllers\Controller;
use App\Services\JWTService;
use App\Mail\AccountCreated;
use App\Repositories\AdminRepository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Cookie\CookieJar;
use Illuminate\Validation\Factory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Routing\ResponseFactory;

class LoginController extends Controller
{
    public function __construct(protected AdminRepository $adminRepository) {}

    public function store(Request $request, Validator $validator, CookieJar $cookie, ResponseFactory $response): JsonResponse
    {
        $validator = $validator->make($request->all(), [
            'email' => 'required|string|lowercase|email|max:255|unique:'.Admin::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $response->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // get admin from phone
        $admin = Admin::where("email", "=", $request->email)->first();

        if ($admin) {

            if ($admin->verify_at) {

                if (! $admin || ! Hash::check($request->password, $admin->password)) {
                    return response()->json(['message' => 'Invalid credentials'], 401);
                }

                if (!$admin->is_active) {
                    $candidateVote = VoteValidationAdmin::where('candidat_id', '=', $admin->id);
                    switch ($admin->status) {
                        case "ACTIF":
                            $admin->is_active = true;
                            $adminRepository->connectAdmint();
                            break;
                        case "EN_ATTENTE_VALIDATION":
                            if ($candidateVote->vote < 3) {
                                return response()->json(['message' => "votre compte est en attente d'activation"], 403);
                            } else {
                                $admin->status = 'ACTIF';
                                $admin->is_active = true;
                                $adminRepository->connectAdmint();
                            }
                            break;
                        case "REJETE":
                            return response()->json(['message' => "votre requete a ete rejete"], 403);
                            break;
                        case "BOOTSTRAP";
                            $admin->is_active = true;
                            $adminRepository->connectAdmint();
                            break;
                    }
                } else{
                    $adminRepository->connectAdmint();
                }

                 $admin->save();

            } else {

                $emailToken = JWTService::generate([
                     'id' => $admin->id
                ]);

                $admin->link = url('/verify/email?token='.$emailToken);

                Mail::to($admin->email)->send(new AccountCreated($admin));

                return response()->json(['message' => 'Verification email resent.', 200]);

            }

        } else {
            return response()->json(["data" => "0"], 404);
        }
    }
}
