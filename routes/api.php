<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActualityController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\DailyReadingController;

// route public
Route::middleware()->group(function () {
    //activities routes
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{activity}', [ActivityController::class, 'show']);
    // actuality routes
    Route::get('/actualities', [ActualityController::class, 'index']);
    Route::get('/actualities/{actuality:slug}', [ActualityController::class, 'show']);
    // articles routes
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{article:slug}', [ArticleController::class, 'show']);
    // categories routes
    Route::get('/categories', [CategorieController::class, 'index']);
    // daily reading routes
    Route::get('/dailyReadings/today', [DailyReadingController::class, 'dispaly']);
    Route::get('/dailyReadings', [DailyReadingController::class, 'index']);
    Route::get('/dailyReadings/{dailyReading}', [DailyReadingController::class, 'show']);
});

// routes for only auth user
Route::middleware()->group(function () {

});


// les routes destinees o admin et super_admin
Route::middleware()->group(function () {
    //activities routes
    Route::post('/activities', [ActivityController::class, 'store']);
    // actuality routes
    Route::post('/actualities', [ActualityController::class, 'store']);
    // articles routes
    Route::post('/articles', [ArticleController::class, 'store']);
    // daily reading routes
    Route::post('/dailyReadings', [DailyReadingController::class, 'store']);
});

// chemins destinees aux super_admin et aux admins auteurs de la lignes crees
Route::middleware()->group(function () {
    //activities routes
    Route::patch('/activities/{activity}', [ActivityController::class, 'update']);
    Route::delete('/activities/{id}', [ActivityController::class, 'destroy']);
    // actuality routes
    Route::patch('/actualities/{actuality:slug}', [ActualityController::class, 'update']);
    Route::delete('/actualities/{actuality:slug}', [ActualityController::class, 'destroy']);
    // articles routes
    Route::patch('/articles/{article:slug}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article:slug}', [ArticleController::class, 'destroy']);
    // daily reading routes
    Route::patch('/dailyReadings/{dailyReading}', [DailyReadingController::class, 'update']);
    Route::delete('/dailyReadings/{dailyReading}', [DailyReadingController::class, 'destroy']);
});

// les routes destinees aux super_admin et aux responsables
Route::middleware()->group(function () {

});


// chemin reserver au admin
Route::middleware()->group(function () {
     //activities routes
    Route::patch('/activities/{id}/restore', [ActivityController::class, 'restore']);
    Route::get('/activities/trashed', [ActivityController::class, 'trashed']);
    // actuality routes
    Route::patch('/actualities/{actuality:slug}/restore', [ActualityController::class, 'restore']);
    Route::get('/actualities/trashed', [ActualityController::class, 'trashed']);
    // articles routes
    Route::patch('/articles/{article:slug}/restore', [ArticleController::class, 'restore']);
    Route::get('/articles/trashed', [ArticleController::class, 'trashed']);
});
