<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ApiTokenController;
use App\Http\Controllers\Api\UpdateProfileController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\PushNotificationController;

// Token generation (public - no auth required)
Route::post('/token/generate', [ApiTokenController::class, 'generate'])->name('api.token.generate');

// User Registration API (public - no auth required)
Route::post('/register', [RegistrationController::class, 'register'])->name('api.register');

// User Login API (public - no auth required)
Route::post('/login', [LoginController::class, 'login'])->name('api.login');

// Device Token Registration API (public - no auth required for registration)
Route::post('/device/register', [DeviceTokenController::class, 'register'])->name('api.device.register');

// Protected routes that require token authentication
Route::middleware(['api.token'])->group(function () {
    
    // Profile APIs
    Route::get('/profile', [ProfileController::class, 'getProfile'])->name('api.profile.get');
    Route::post('/profile/update', [UpdateProfileController::class, 'update'])->name('api.profile.update');

    // Device Token API Routes (protected)
    Route::get('/devices', [DeviceTokenController::class, 'list'])->name('api.device.list');
    Route::put('/device/{id}', [DeviceTokenController::class, 'update'])->name('api.device.update');
    Route::delete('/device/{id}', [DeviceTokenController::class, 'delete'])->name('api.device.delete');
});

// Push Notification API Routes
Route::post('/push/send/{newsId}', [PushNotificationController::class, 'sendNotification'])->name('api.push.send');
Route::post('/push/toggle/{newsId}', [PushNotificationController::class, 'toggleNotification'])->name('api.push.toggle');
Route::get('/push/pending', [PushNotificationController::class, 'getPendingNotifications'])->name('api.push.pending');
Route::post('/push/test', [PushNotificationController::class, 'sendTestNotification'])->name('api.push.test');

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



