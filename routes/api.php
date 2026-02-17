<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ApplicationController;

Route::prefix('auth')->group(function () {
    Route::post('/register/user', [AuthController::class, 'registerUser']);
    Route::post('/register/company', [AuthController::class, 'registerCompany']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'getAll']);
    Route::get('/{id}', [JobController::class, 'publicShow']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::prefix('company')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::put('/', [CompanyController::class, 'update']);
        Route::delete('/', [CompanyController::class, 'destroy']);

        Route::prefix('jobs')->group(function () {
            Route::get('/', [JobController::class, 'index']);
            Route::post('/', [JobController::class, 'store']);
            Route::get('/{id}', [JobController::class, 'show']);
            Route::put('/{id}', [JobController::class, 'update']);
            Route::delete('/{id}', [JobController::class, 'destroy']);
        });

        Route::prefix('applications')->group(function () {
            Route::get('/', [ApplicationController::class, 'getAllApplications']);
            Route::put('/{jobId}/{applicationId}', [ApplicationController::class, 'updateStatus']);
        });
    });

    Route::prefix('applications')->group(function () {
        Route::post('/apply/{jobId}', [ApplicationController::class, 'store']);
        Route::get('/{jobId}/{applicationId}', [ApplicationController::class, 'detail']);
    });

});
