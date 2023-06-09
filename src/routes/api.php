<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecipeController;
use App\Http\Middleware\ResponseHeaderMiddleware;
use App\Http\Middleware\VerifyHeaderMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health_check', function () {
    return 'laravel is alive.';
});
Route::middleware([VerifyHeaderMiddleware::class, ResponseHeaderMiddleware::class])->group(function() {
    Route::post('/users', [UserController::class, 'signUp']);
    Route::post('/users/login', [UserController::class, 'login']);
});
Route::middleware(['auth:sanctum', VerifyHeaderMiddleware::class, ResponseHeaderMiddleware::class])->group(function () {
    Route::get('/users/me', [UserController::class, 'getUser']);

    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{id}', [RecipeController::class, 'show']);
    Route::post('/recipes', [RecipeController::class, 'store']);
    Route::patch('/recipes/{id}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{id}', [RecipeController::class, 'destroy']);

    Route::post('/images', [ImageController::class, 'store']);
});

