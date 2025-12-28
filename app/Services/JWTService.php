<?php

namespace App\Services;


use App\Models\Admin;
use App\Models\Member;
use App\Models\RefreshToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Auth;


class JWTService
{
    /**
     *  Generate a token
     *
     *  @param mixed $data
     *  @param integer $expirationTime
     *  @return
     */
    static function generate($data, $expirationTime = null)
    {
        $key = config('session.secret');

        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "data" => $data,
        ];

        if($expirationTime != null){
            $payload["exp"] = time() + $expirationTime;
        }

        return JWT::encode($payload, $key, "HS256");
    }

    /**
     *  decode token
     *
     *  @param string token
     *  @return mixed data
     */

    static function decode($token)
    {
        $key = config('session.secret');
        try {
            $decoded = JWT::decode($token, new Key($key,"HS256"));
            return $decoded->data;
        }catch(ExpiredException $e){
            return false;
        }
    }

    static function refresh ($request) {
        $token = $request->bearerToken();

        if (!$token || !str_contains($token, '.')) {
            return false;
        }

        [$id, $secret] = explode('.', $token, 2);

        $refresh_token = RefreshToken::find($id);

        if (!$refresh_token) {
            return false;
        }

         // vérifier l'expiration
        if (Carbon::parse($refresh_token->expired_at)->isPast()) {
            $refresh_token->delete();
            return false;
        }

        //$user = $refreshToken->user;

        // comparer le secret fourni et le hash stocké
        if (!Hash::check($secret, $refresh_token->token)) {
           return false;
        }

        $userId = $refresh_token->user_id;

        return $userId;
    }

    /**
     *  Email is use as identifier.
     */
    static function verify($request)
    {
        $key = config('session.secret');

        $bearerHeader = $request->header("Authorization");

        if ($bearerHeader == null) {
            return false;
        }

        $bearer = explode(" ", $bearerHeader);

        if (empty($bearer)) {
            return false;
        }

        if ($bearer[0] != "Bearer") {
            return false;
        }

        $bearerToken = $bearer[1];

        $decoded = JWT::decode($bearerToken, new Key($key, "HS256"));

        $data = $decoded->data;

        $user = Admin::find($data->id);

        if ($user == null) {
            $user = Member::find($data->id);
        }

        if ($user == null) {
            return false;
        } else {
           // Attacher l'utilisateur à la requête
            $request->setUserResolver(fn() => $user);
            return true;
        }

    }

}
