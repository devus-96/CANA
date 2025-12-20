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
     public const USER_ROLE_SUPER_ADMIN               = 'super_admin';
     public const USER_ROLE_ADMIN                     = "admin";
     public const USER_ROLE_STATELIVEMANAGER          = "state_live_manager";
     public const USER_ROLE_VISITOR                   = "visitor";
     public const BOOTSTRAP_ADMIN_STATUS_LIMIT        = 5;

    public static function generateOpaqueToken()
    {
        // secret aléatoire que l'on retourne au client
        $secret = Str::random(60);

        // stocker un hash du secret (comme mot de passe)
        $tokenHash = Hash::make($secret);

        return [$secret, $tokenHash];
    }

    /**
     * used to create six random digits to authenticate a specific user
     *
     * @return $code
     */
    public static function generateCode(){
        return mt_rand(100000,999999);
    }

    /**
     * used to check if confirmation code is always usefull to log user
     *
     * @return true or false
     */
    public static function checkLoginCode(LoginConfirmation $confirm){
        // confirmation exists
        if($confirm)
            return true; // return true

        return false; // otherwise false
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

    public static function uploadImages($data, $store, $type = 'profile_photo_url')
    {
        if (request()->hasFile($type)) {
            $store->{$type} = current(request()->file($type))->store("$type/".$store->id);

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
