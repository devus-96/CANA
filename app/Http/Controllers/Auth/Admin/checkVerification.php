<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;
use App\Models\Admin;

use App\Http\Controllers\Controller;
use App\Services\JWTService;
use App\Models\VoteValidationAdmin;
use App\Repositories\AdminRepository;

class VerifyEmail extends Controller
{
    public function __construct(protected AdminRepository $adminRepository) {}

    public function __invoke(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/admin/login?t=e&m=Verification link has expired");
        }

        $data = JWTService::decode($token);

        if (!$data) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/admin/login?t=e&m=Verification link has expired");
        }

        $admin = Admin::find($data->id); // Fixed typo: it was "tokenabled_id"

        if (!$admin) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/admin/login?t=e&m=admin not found");
        }

        $admin->verified_at = now();
        $admin->email_verified_at = now();

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
    }
}
