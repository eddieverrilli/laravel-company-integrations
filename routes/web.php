<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', fn (Request $request) => to_route('companies.show', $request->user()->company_id))->name('home');

    Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
});
