<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;

use App\Models\Admin;
use App\Mail\SendInvitation;
use App\Models\Invitation;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    public function index (Request $request) {
        try {
            $query = Invitation::with('invited_by')->orderBy('expires_at', 'desc');

            if ($request->filled('status', $request->status)) {
                $query->where('status');
            }
            // Recherche par nom ou description
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%");
                });
            }
            // Pagination avec paramètre optionnel
            $perPage = $request->get('per_page', 10);
            $invitation = $query->paginate($perPage);


            return response()->json([
                'message' => "list of activities",
                'data' => $invitation,
                'meta' => [
                    'current_page' => $invitation->currentPage(),
                    'total' => $invitation->total(),
                    'per_page' => $invitation->perPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
             Log::error('Activity index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store (Request $request) {
        try {
             /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();

            Validator::make($request->all(), [
                 'email'  => 'required|string|lowercase|email|max:255|unique:'.Admin::class,
                 'fonction'      => 'nullable|string|max:255',
            ]);

            $role = Role::where('name', Controller::USER_ROLE_ADMIN)->first();

            if (!$role) {
                $role = Role::create([
                    'name' => Controller::USER_ROLE_ADMIN,
                    'description' => 'Gestion complète du site : blog, méditations, médias, états de vie, événements, réservations et utilisateurs',
                    'permissions' => json_encode([
                        'blog.create', 'blog.edit', 'blog.delete', 'blog.publish',
                        'meditations.create', 'meditations.edit', 'meditations.delete', 'meditations.schedule',
                        'media.upload', 'media.edit', 'media.delete', 'media.manage',
                        'etats_vie.manage', 'etats_vie.assign_responsables',
                        'events.create', 'events.edit', 'events.delete', 'events.publish',
                        'reservations.view', 'reservations.manage', 'reservations.export',
                        'payments.view', 'payments.manage',
                        'donations.view', 'donations.export',
                        'users.create', 'users.edit', 'users.delete', 'users.assign_roles',
                        'activities.create', 'activities.edit', 'activities.delete',
                        'projects.create', 'projects.edit', 'projects.delete',
                        'settings.manage', 'site.manage'
                    ]),
                ]);
            }

            // 3. Générer le token
            [$secret, $tokenHash] = Controller::generateOpaqueToken();

            $invitation = $admin->invitation()->create([
                'email' => $request->email,
                'fonction' => $request->fonction,
                'token' => $tokenHash,
                'expired_at' =>  now()->addDays(3),
                'role_id' => $role->id
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
