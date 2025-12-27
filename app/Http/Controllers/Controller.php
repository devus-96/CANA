<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\LoginConfirmation;

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

   public static function uploadImages($data, $store, $type = 'profil')
    {
        $oldImage = $store->{$type};

        if (request()->hasFile($type)) {
            if ($oldImage && Storage::exists($oldImage)) {
                Storage::delete($oldImage);
            }

            $store->{$type} = current(request()->file($type))->store("$type/".$store->id);
        }
        elseif (array_key_exists($type, $data) && empty(trim($data[$type]))) {
            if ($oldImage && Storage::exists($oldImage)) {
                Storage::delete($oldImage);
            }
            $store->{$type} = null;
        }

        if ($store->isDirty($type)) {
            $store->save();
        }
    }

    public static function generateReservationCode($event, $phone): string
    {
        $timestamp = time();
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        $memberCode = strtoupper(substr($phone, 0, 4));

        return "CANA-{$event->id}-{$memberCode}-{$random}-{$timestamp}";
        // Exemple: CANA-15-DOEJ-X7B9F3-1702735200
    }

    public static function determineFileType(string $mimeType, string $extension): string
    {
        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $videoMimes = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'];
        $audioMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];

        if (in_array($mimeType, $imageMimes)) return 'image';
        if (in_array($mimeType, $videoMimes)) return 'video';
        if (in_array($mimeType, $audioMimes)) return 'audio';
        if (in_array($extension, $documentExtensions)) return 'document';
        if (in_array($extension, $archiveExtensions)) return 'archive';

        return 'other';
    }
}
