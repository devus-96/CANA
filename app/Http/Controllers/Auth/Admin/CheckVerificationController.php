<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;

use App\Models\Admin;
use App\Http\Controllers\Controller;
use App\Services\JWTService;

use Carbon\Carbon;

class CheckVerificationController extends Controller
{
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

        $admin->is_verified = true;
        $admin->save();

        return redirect()->to(env('WEB_CLIENT_URL') . "/auth/login?t=s&m=Account verified successfully");
    }
}
