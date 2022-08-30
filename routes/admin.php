<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\LoanController;

Route::post('login', [AuthController::class, 'login']);
Route::group(
    ['middleware' => 'auth:sanctum'],
    function () {
        Route::post('loans/{loan}', [LoanController::class, 'update']);
    }
);
