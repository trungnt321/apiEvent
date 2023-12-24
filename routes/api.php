<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\atendanceController;
use App\Http\Controllers\chatController;
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

//Route::apiResource('atendances',atendanceController::class)->middleware('auth:api');
Route::prefix('atendances')->group(function() {
    Route::get('/join/{id_event}/{id_user}',[atendanceController::class,'index']);
    Route::post('/add',[atendanceController::class,'addEmail']);
    Route::post('/',[atendanceController::class,'store']);
    Route::get('/{id}',[atendanceController::class,'show']);
    Route::put('/{id}',[atendanceController::class,'update']);
    Route::delete('/{id}/{id_user}',[atendanceController::class,'destroy']);
})->middleware('auth:api');
//Route::apiResource('feedback',feedbackController::class)->middleware('auth:api');
Route::prefix('feedback')->group(function() {
    Route::get('/{id_event}',[feedbackController::class,'index']);
    Route::get('/show/{id}',[feedbackController::class,'show']);
    Route::post('/',[feedbackController::class,'store']);
    Route::put('/{id}',[feedbackController::class,'update']);
    Route::delete('/{id}',[feedbackController::class,'destroy']);
})->middleware('auth:api');
//Route::apiResource('notification',notificationController::class)->middleware('auth:api');
Route::prefix('notification')->group(function() {
    Route::post('/send',[notificationController::class,'create']);
    Route::get('/{id}',[notificationController::class,'index']);
    Route::post('/',[notificationController::class,'store']);
    Route::get('/show/{id}',[notificationController::class,'show']);
    Route::put('/{id}',[notificationController::class,'update']);
    Route::delete('/{id}',[notificationController::class,'destroy']);
})->middleware('auth:api');
Route::get('/event',[eventController::class,'index']);
//Test api in swagger donn't need token
Route::apiResource('participants',participantsController::class)->middleware('auth:api');
Route::apiResource('event',eventController::class)->middleware('auth:api');
Route::post('recreateEvent',[eventController::class,'recreateEvent'])->middleware('auth:api');
Route::get('resourceByEventID/{event_id}',[resourceController::class,'GetRecordByEventId'])->middleware('auth:api');
Route::apiResource('resource',resourceController::class)->middleware('auth:api');

//Search
Route::post('searchUser',[participantsController::class,'getUserByEmailAndPhone'])->middleware('auth:api');
Route::post('searchEvent',[eventController::class,'searchEvent'])->middleware('auth:api');

//Real Time
Route::post('chat', [chatController::class, 'sendMessage']);

//Event statistics
Route::post('eventStatistics',[eventController::class,'eventStatistics'])->middleware('auth:api');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
