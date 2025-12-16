<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class VerifyAdminSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->header(Controller::API_USER_TOKEN_HEADER_NAME);

        $secretHash = Hash::make($secret);

        $con = RefreshToken::where("token", "=", $secretHash)->first();

        if(!$con){
            return response()->json(["data" => "-99"], 401); // token invalide
        }

        return $next($request);
    }
}
