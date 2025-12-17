<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Admin\RegisterController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::post('admin/register', RegisterController::class);
});
