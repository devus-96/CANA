<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\Admin\RegisterController;
use App\Http\Controllers\Auth\Admin\LoginController;
use App\Http\Controllers\Auth\Admin\InvitationController;

use App\Http\Controllers\Auth\Member\RegisterMemberController;
use App\Http\Controllers\Auth\Member\CheckVerification;
use App\Http\Controllers\Auth\Member\LoginMemberController;

use App\Http\Controllers\Auth\SendResetPassword;
use App\Http\Controllers\Auth\PasswordResetToken;
use App\Http\Controllers\Auth\ResetPassword;
use App\Http\Controllers\Auth\RefreshTokenController;
use Inertia\Inertia;



Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/auth/password-forgot', function () {
    return Inertia::render('auth/password-forgot');
})->name('home');

Route::get('/auth/reset-password', function () {
    return Inertia::render('auth/reset-password');
})->name('home');

Route::get('/auth/verify-email', function () {
    return Inertia::render('auth/verify-email');
})->name('home');

Route::get('/admin/auth/verify-code', function () {
    return Inertia::render('admin/auth/verify-code');
})->name('home');


Route::get('/auth/login', function () {
    return Inertia::render('auth/login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/auth/refresh', RefreshTokenController::class);
    // auth admin
    Route::get('/admin/auth/register', [RegisterController::class, 'create'])->name('create.admin');
    Route::get('/admin/auth/connexion', [LoginController::class, 'create_connexion'])->name('create.connexion');
    Route::get('/admin/auth/login', [LoginController::class, 'create'])->name('create.login');

    Route::post('/admin/register', [RegisterController::class, 'store'])->name('admin.register');
    Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login');
    Route::post('/admin/connexion', [LoginController::class, 'connexion'])->name('admin.connexion');
    // auth menber
    Route::get('/auth/register', [RegisterMemberController::class, 'create']);
    Route::get('/auth/login', [LoginMemberController::class, 'create']);

    Route::post('/member/register', [RegisterMemberController::class, 'store'])->name('member.register');
    Route::get('/verify/member/email', CheckVerification::class)->name('member.emailVerify');
    Route::post('member/login', [LoginMemberController::class, 'store'])->name('member.login');

    Route::post('auth/forgot-password', SendResetPassword::class);
    Route::get('auth/reset-password', PasswordResetToken::class);
    Route::post('auth/reset-password', ResetPassword::class);
});

Route::middleware('session', 'only_superAdmin')->group(function () {
    //invitation route
    Route::post('/invitation', [InvitationController::class, 'store']);
});
