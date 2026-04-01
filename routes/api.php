<?php

use Illuminate\Http\Request;
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

// Authentication routes (no middleware required)
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// Protected routes that require authentication
Route::middleware('api.token')->group(function () {
    Route::get('/profile', [App\Http\Controllers\Api\AuthController::class, 'profile']);
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
});

// Category API Routes
Route::group(['prefix'=>'categories','as'=>'api.category.'], function() {
    Route::get('/', [App\Http\Controllers\Api\CategoryController::class, 'index'])->name('index');
    Route::get('/by-language/{languageId}', [App\Http\Controllers\Api\CategoryController::class, 'getByLanguage'])->name('byLanguage');
});

// Language API Routes
Route::group(['prefix'=>'languages','as'=>'api.language.'], function() {
    Route::get('/', [App\Http\Controllers\Api\LanguageController::class, 'index'])->name('index');
});

// News Listing API Routes
Route::group(['prefix'=>'news','as'=>'api.news.'], function() {
    Route::get('/', [App\Http\Controllers\Api\NewsController::class, 'index'])->name('index');
    Route::get('/latest', [App\Http\Controllers\Api\NewsController::class, 'latest'])->name('latest');
    Route::get('/{id}', [App\Http\Controllers\Api\NewsController::class, 'show'])->name('show');
    Route::get('/by-language/{languageId}', [App\Http\Controllers\Api\NewsController::class, 'getByLanguage'])->name('byLanguage');
    Route::get('/by-category/{categoryId}', [App\Http\Controllers\Api\NewsController::class, 'getByCategory'])->name('byCategory');
    Route::get('/by-country/{countryId}', [App\Http\Controllers\Api\NewsController::class, 'getByCountry'])->name('byCountry');
});


