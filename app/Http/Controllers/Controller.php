<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

abstract class Controller
{
     use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

     public const USER_ROLE_MEMBERS                   = 'member';
     public const USER_ROLE_ADMIN                     = "admin";
     public const USER_ROLE_STATELIVEMANAGER          = "state_live_manager";
     public const USER_ROLE_VISITOR                   = "visitor";

    public static function generateOpaqueToken()
    {
        // secret aléatoire que l'on retourne au client
        $secret = Str::random(60);

        // stocker un hash du secret (comme mot de passe)
        $tokenHash = Hash::make($secret);

        return [$secret, $tokenHash];
    }

     /**
     * used to create a token for a specific user
     * @param \App\Models\User $user
     *
     * @return {String} $token
     */
    public static function createUserToken(User $user){
        $token = Hash::make($user->email)
                . time()
                . Hash::make($user->nom."@".$user->role)
                . time();

        return Hash::make($token);
    }

    public static function uploadImages($data, $store, $type = 'iamge')
    {
        if (request()->hasFile($type)) {
            $store->{$type} = current(request()->file($type))->store('store/'.$store->id);

            $store->save();
        } else {
            if (! isset($data[$type])) {
                if (! empty($data[$type])) {
                    Storage::delete($store->{$type});
                }

                $store->{$type} = null;

                $store->save();
            }
        }
    }
}
