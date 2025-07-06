<?php

use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');

    Route::prefix('settings')->name('settings.')->group(function(){
        Route::prefix('roles')->name('roles.')->group(function(){
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/loader', [RoleController::class, 'tableLoader'])->name('loader');
            Route::get('/show', [RoleController::class, 'show'])->name('show');
            Route::post('/store', [RoleController::class, 'store'])->name('store');
            Route::post('/update', [RoleController::class, 'update'])->name('update');
            Route::post('/destroy', [RoleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/loader', [UserController::class, 'loader'])->name('loader');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::get('/edit', [UserController::class, 'edit'])->name('edit');
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::post('/update', [UserController::class, 'update'])->name('update');
            Route::post('/destroy', [UserController::class, 'destroy'])->name('destroy');
        });
    });
});
