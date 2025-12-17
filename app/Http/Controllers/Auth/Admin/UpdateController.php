<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    public function update (Request $request)  {

        $admin = auth()->guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'phone'         => ['sometimes', 'string', 'unique:'.Admin::class, new PhoneNumber],
            'profile'       => 'somtimes|mimes:bmp,jpeg,jpg,png,webp',
            "biography"     => "sometimes|nullable|string",
            "role"          => 'sometimes|string',
            "status"        => 'sometimes|string',
            "parish"        => "sometimes|string"
        ]);

        if ($request->status) {
            $roleNames = $admin->roles()->pluck('name');
            if ($roleNames !== Controller::USER_ROLE_SUPER_ADMIN) {
                return response()->json(["message" => "non autoriser"], 403);
            }
            $selected_admin = Admin::where('id', '=', $request->user_id)->first();
            $selected_admin->status = $request->status;
            $selected_admin->manager_id = $admin->id;
        }

        if ($request->role) {
            if ($roleNames !== Controller::USER_ROLE_SUPER_ADMIN) {
                return response()->json(["message" => "non autoriser"], 403);
            }
            $user->roles()->attach(
                Role::where('name', Controller::USER_ROLE_STATELIVEMANAGER)->first()->id
            );
        }

        $admin->update($validator);

        return response()->json(['message' => 'successfully updated.'], 200);

    }

}
