<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('login'); // Applying throttle
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
    Route::get('/users/{user}', [AuthController::class, 'getOtherUserProfile'])->middleware('auth:api')->name('get_other_user_profile');
    Route::get('/users', [AuthController::class, 'index']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount'])->middleware('auth:api')->name('delete_account');
    Route::patch('/me', [AuthController::class, 'updateProfile'])->middleware('auth:api')->name('update_profile');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

Route::middleware('auth:api')->group(function () {
    Route::post('/recipes', [RecipeController::class, 'store']);
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
    Route::patch('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
    Route::get('/search', [RecipeController::class, 'search']);
});

Route::prefix('categories')->middleware('auth:api')->group(function () {
    Route::get('/', [CategoryController::class, 'index']); // List all categories
    Route::post('/', [CategoryController::class, 'store']); // Create a new category
    Route::get('{category}', [CategoryController::class, 'show']); // Get a specific category
    Route::put('{category}', [CategoryController::class, 'update']); // Update a specific category
    Route::delete('{category}', [CategoryController::class, 'destroy']); // Delete a specific category
});




Route::middleware('auth:api')->group(function () {
    Route::post('/recipes/{recipe}/comments', [CommentController::class, 'store']);
    Route::get('/recipes/{recipe}/comments', [CommentController::class, 'index']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']); 
});
