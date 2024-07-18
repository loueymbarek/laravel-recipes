<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
    Route::get('/users/{user}', [AuthController::class, 'getOtherUserProfile'])->middleware('auth:api')->name('get_other_user_profile');

    Route::delete('/delete-account', [AuthController::class, 'deleteAccount'])->middleware('auth:api')->name('delete_account');
    
    Route::patch('/me', [AuthController::class, 'updateProfile'])->middleware('auth:api')->name('update_profile');;
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');



});
