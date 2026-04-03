<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ApiTokenController;

// Token generation (public - no auth required)
Route::post('/token/generate', [ApiTokenController::class, 'generate'])->name('api.token.generate');

// Protected routes that require token authentication
// Route::middleware(['api.token'])->group(function () {
    
    Route::post('/token/verify', [ApiTokenController::class, 'verify'])->name('api.token.verify');
    Route::post('/token/revoke', [ApiTokenController::class, 'revoke'])->name('api.token.revoke');
    Route::get('/tokens', [ApiTokenController::class, 'list'])->name('api.token.list');
    
    // Category API Routes
    Route::group(['prefix'=>'categories','as'=>'api.category.'], function() {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('show');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::get('/by-language/{languageId}', [CategoryController::class, 'getByLanguage'])->name('byLanguage');
    });

    // Language API Routes
    Route::group(['prefix'=>'languages','as'=>'api.language.'], function() {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('/{id}', [LanguageController::class, 'show'])->name('show');
        Route::post('/', [LanguageController::class, 'store'])->name('store');
        Route::put('/{id}', [LanguageController::class, 'update'])->name('update');
        Route::delete('/{id}', [LanguageController::class, 'destroy'])->name('destroy');
    });

    // News Listing API Routes
    Route::group(['prefix'=>'news','as'=>'api.news.'], function() {
        Route::get('/', [NewsController::class, 'index'])->name('index');
        Route::get('/latest', [NewsController::class, 'latest'])->name('latest');
        Route::get('/{id}', [NewsController::class, 'show'])->name('show');
        Route::get('/by-language/{languageId}', [NewsController::class, 'getByLanguage'])->name('byLanguage');
        Route::get('/by-category/{categoryId}', [NewsController::class, 'getByCategory'])->name('byCategory');
        Route::get('/by-country/{countryId}', [NewsController::class, 'getByCountry'])->name('byCountry');
    });
// });


