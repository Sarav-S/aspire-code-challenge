<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\TermController;

Route::post('login', [AuthController::class, 'login']);

Route::group(
    ['middleware' => 'auth:sanctum'],
    function () {
        Route::resource('loans', LoanController::class)
            ->only(['index', 'store', 'show']);

        Route::resource('loans/{loan}/term', TermController::class)
            ->only(['update']);
    }
);
