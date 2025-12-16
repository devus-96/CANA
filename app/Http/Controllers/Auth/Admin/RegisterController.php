<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\AdminRequest;
use App\Repositories\AdminRepository;

class RegisterController extends Controller
{
    public function __construct(protected AdminRepository $adminRepository) {}

    public function __invoke (AdminRequest $request) {
        $validator = $request->validated();

        if ($validator->fails()) {
            return $response->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // find existing admin with same email address
        $admin = Admin::where("email", "=", $validator->email)->first();

        if (!$admin) {

            try {

                DB::beginTransaction();

                // find existing admin with same phone number
                $admin = Admin::where("phone", "=", $validator->telephone)->first();

                if (!$admin) {

                    $admin = Admin::query()->create([
                        'name' => $validator->name,
                        'email' => $validator->email,
                        'password' => Hash::make($validator->password),
                        'phone' => $validator->phone_number,
                        "role" => $validator->role,
                        "fonction" => $validator->fonction
                    ]);

                    Controller::uploadImages(['image' => $request->image], $admin);

                    $role = Role::where('name', '=', $validator->role)->fisrt();
                    $admin->role_id = $role->id;
                    $adminRepository->CheckAdminBootstrapLimit(Controller::BOOTSTRAP_ADMIN_STATUS_LIMIT);

                    $emailToken = JWTService::generate([
                        'id' => $admin->id
                    ]);

                    $admin->link = url('/verify/email?token='.$emailToken);

                    Mail::to($admin->email)->send(new AccountCreated($admin));

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
