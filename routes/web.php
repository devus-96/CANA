<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\Admin\RegisterController;
use App\Http\Controllers\Auth\Admin\CheckVerificationController;
use App\Http\Controllers\Auth\Admin\LoginController;

use App\Http\Controllers\Auth\Member\RegisterMenberController;
use App\Http\Controllers\Auth\Member\CheckVerification;
use App\Http\Controllers\Auth\Member\LoginMenberController;

use App\Http\Controllers\Auth\SendResetPassword;
use App\Http\Controllers\Auth\PasswordResetToken;
use App\Http\Controllers\Auth\ResetPassword;
use App\Http\Controllers\Auth\RefreshTokenController;



Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    // auth admin
    Route::post('admin/register', RegisterController::class);
    Route::get('/verify/email', CheckVerificationController::class);
    Route::post('/admin/login', [LoginController::class, 'login']);
    Route::post('/admin/connexion', [LoginController::class, 'create']);
    // auth menber

    Route::post('member/register', RegisterMenberController::class);
    Route::get('verify/member/email', CheckVerification::class);
    Route::post('member/login', LoginMenberController::class);
    Route::get('/auth/refresh', RefreshTokenController::class);

    Route::post('auth/forgot-password', SendResetPassword::class);
    Route::get('auth/reset-password', PasswordResetToken::class);
    Route::post('auth/reset-password', ResetPassword::class);
});
