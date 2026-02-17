<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ApplicationController;

/*
|--------------------------------------------------------------------------
| AUTH PUBLIC
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/register/user', [AuthController::class, 'registerUser']);
    Route::post('/register/company', [AuthController::class, 'registerCompany']);
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| PUBLIC JOBS
|--------------------------------------------------------------------------
*/

Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'getAll']);
    Route::get('/{id}', [JobController::class, 'publicShow']);
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    | AUTH USER
    */
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    /*
    |--------------------------------------------------------------------------
    | COMPANY AREA (ROLE: COMPANY)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:company')->prefix('company')->group(function () {

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

    /*
    |--------------------------------------------------------------------------
    | JOB SEEKER APPLICATIONS
    |--------------------------------------------------------------------------
    */
    Route::prefix('applications')->group(function () {
        Route::post('/apply/{jobId}', [ApplicationController::class, 'store']);
        Route::get('/{jobId}/{applicationId}', [ApplicationController::class, 'detail']);
    });

});
