<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PartController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehiclePartController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', [AuthController::class, 'store']);
Route::post('/tokens/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->name('api.')->group(function () {
    Route::get('/user', [AuthController::class, 'show']);
    Route::delete('/tokens/current', [AuthController::class, 'destroy']);

    Route::apiResource('teams', TeamController::class);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('parts', PartController::class);

    Route::post('vehicles/{vehicle}/parts', [VehiclePartController::class, 'store']);
    Route::patch('vehicles/{vehicle}/parts/{vehiclePart}', [VehiclePartController::class, 'update']);
    Route::delete('vehicles/{vehicle}/parts/{vehiclePart}', [VehiclePartController::class, 'destroy']);
});
