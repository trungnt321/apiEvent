<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\atendanceController;
use App\Http\Controllers\eventController;
use App\Http\Controllers\notificationController;
use App\Http\Controllers\participantsController;
use App\Http\Controllers\feedbackController;
use App\Http\Controllers\resourceController;

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
Route::get('atendances/join/{id}',[atendanceController::class,'index']);
//Route::post('atendances/join/{id}',[atendanceController::class,'index']);
Route::apiResource('atendances',atendanceController::class)->middleware('auth:api');
Route::apiResource('feedback',feedbackController::class)->middleware('auth:api');
Route::post('notification/send',[notificationController::class,'create'])->middleware('auth:api');
Route::get('notification/test',[notificationController::class,'test'])->middleware('auth:api');
Route::apiResource('notification',notificationController::class)->middleware('auth:api');

Route::get('/event',[eventController::class,'index']);
//Test api in swagger donn't need token
Route::apiResource('participants',participantsController::class)->middleware('auth:api');
Route::apiResource('event',eventController::class)->middleware('auth:api');
Route::get('resourceByEventID/{event_id}',[resourceController::class,'GetRecordByEventId'])->middleware('auth:api');
Route::apiResource('resource',resourceController::class)->middleware('auth:api');

//Search
Route::post('searchUser',[participantsController::class,'getUserByEmailAndPhone'])->middleware('auth:api');
Route::post('searchEvent',[eventController::class,'searchEvent'])->middleware('auth:api');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
