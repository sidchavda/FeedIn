<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SmtpController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NewsController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// START: Auth routes
Route::get('/login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'App\Http\Controllers\Auth\LoginController@login');
Route::get('/register', 'App\Http\Controllers\Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'App\Http\Controllers\Auth\RegisterController@register');
Route::get('/password/reset', 'App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('/password/reset/{token}', 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset');
Route::post('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');
// END: Auth routes


Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\DashboardsController::class, 'index'])->name('admin.dashboard.index');

    /**
     * User Routes
     */
    // User routes
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/user/add', [App\Http\Controllers\UserController::class, 'add'])->name('admin.user.add');
    Route::post('/user/store', [App\Http\Controllers\UserController::class, 'store'])->name('admin.user.store');
    Route::get('/user/edit/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('admin.user.edit');
    Route::post('/user/update/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('admin.user.update');
    Route::get('/user/delete/{id}', [App\Http\Controllers\UserController::class, 'delete'])->name('admin.user.delete');

    /**
     * Category Routes
     */
    Route::group(['prefix'=>'category','as'=>'admin.category.'], function() {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/add', [CategoryController::class, 'add'])->name('add');
        Route::post('/store', [CategoryController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [CategoryController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [CategoryController::class, 'delete'])->name('delete');
    });

    //SMTP

    // Route::group(['prefix'=>'smtp','as'=>'admin.smtp.'], function() {
    //     Route::get('/', [SmtpController::class, 'index'])->name('index');
    //     Route::get('/add', [SmtpController::class, 'add'])->name('add');
    //     Route::post('/store', [SmtpController::class, 'store'])->name('store');
    //     Route::get('/edit/{id}', [SmtpController::class, 'edit'])->name('edit');
    //     Route::post('/update/{id}', [SmtpController::class, 'update'])->name('update');
    //     Route::get('/delete/{id}', [SmtpController::class, 'delete'])->name('delete');
    // });

    //Lead
    Route::group(['prefix'=>'lead','as'=>'admin.lead.'], function() {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/delete/{id}', [LeadController::class, 'delete'])->name('delete');
        Route::get('/details/{id}', [LeadController::class, 'details'])->name('detail');
    });

    //News
    Route::group(['prefix'=>'news','as'=>'admin.news.'], function() {
        Route::get('/', [NewsController::class, 'index'])->name('index');
        Route::get('/add', [NewsController::class, 'add'])->name('add');
        Route::post('/store', [NewsController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [NewsController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [NewsController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [NewsController::class, 'delete'])->name('delete');
    });

    // Categories by language API endpoint
    Route::get('/categories-by-language/{languageId}', [CategoryController::class, 'getCategoriesByLanguage'])->name('admin.categories.byLanguage');

    // States by country API endpoint
    Route::get('/states-by-country/{countryId}', [App\Http\Controllers\StateController::class, 'getStatesByCountry'])->name('admin.states.byCountry');

    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('admin.profile');
    Route::post('/profile/update', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('admin.profile.update');

    // Password Routes
    Route::get('/password', [App\Http\Controllers\UserController::class, 'password'])->name('admin.password');
    Route::post('/password/update', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('admin.password.update');

    //Language
    Route::group(['prefix'=>'language','as'=>'admin.language.'], function() {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('/add', [LanguageController::class, 'add'])->name('add');
        Route::post('/store', [LanguageController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [LanguageController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [LanguageController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [LanguageController::class, 'delete'])->name('delete');
    });
});


