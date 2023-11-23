<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\atendanceController;
use App\Http\Controllers\eventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register',[UserAuthController::class,'register']);
Route::post('login',[UserAuthController::class,'login']);

//Route::apiResource('employees',EmployeeController::class)->middleware('auth:api');

Route::apiResource('atendances',atendanceController::class)->middleware('auth:api');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/event',[eventController::class,'index']);
