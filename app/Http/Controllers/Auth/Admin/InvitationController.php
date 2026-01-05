<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;

use App\Models\Admin;
use App\Mail\SendInvitation;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    public function store (Request $request) {
        try {
             /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();

            Validator::make($request->all(), [
                 'email'  => 'required|string|lowercase|email|max:255|unique:'.Admin::class,
                 'role' => 'nullable|exists:roles,id'
            ]);

            // 3. Générer le token
            [$secret, $tokenHash] = Controller::generateOpaqueToken();

            $invitation = $admin->invitation()->create([
                'email' => $request->email,
                'token' => $tokenHash,
                'expired_at' =>  now()->addDays(3),
                'role_id' => $request->role
            ]);

            $admin->link_accept = url(route('/admin/auth/register', ['token' => $secret, 'invitation_id' => $invitation->id]));
            $admin->link_refuse = url("/invitation/$invitation->id?status=refused");

            Mail::to($admin->email)->send(new SendInvitation($admin, $request->email));

            return response()->json(['message' => 'invitation sent.'], 200);
        } catch (\Exception $e) {
             Log::error('Activity index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update (Request $request, int $id) {
         $status = $request->query('status');

         $invitation = Invitation::where('id', $id)->first();

         if (!$invitation) {
            return response()->json([
                'status' => 'error', 'message' => 'invitation not found'
            ], 404);
         }

         $invitation->update(['status' => $status]);
    }

}
