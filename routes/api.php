<?php

use App\Http\Controllers\AgencyComplaintController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working in Laravel 12!']);
});


    Route::post('register', [UserController::class, 'register']);
    Route::post('verify_otp', [UserController::class, 'verify']);
    Route::post('login', [UserController::class, 'login']);
    // Route::post('logout', [UserController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
Route::middleware('auth:sanctum')->group(function () {
    // شكاوى المواطن
    Route::post('complaints', [ComplaintController::class,'submit']);
    Route::get('complaints', [ComplaintController::class,'allComplaints']);

    // شكاوى الجهة الحكومية
    Route::prefix('agency')->group(function () {
        Route::get('/complaints', [AgencyComplaintController::class, 'index']);
        Route::get('/complaints/{id}', [AgencyComplaintController::class, 'show']);
        Route::post('/complaints/{id}/lock', [AgencyComplaintController::class, 'lock']);
        Route::post('/complaints/{id}/unlock', [AgencyComplaintController::class, 'unlock']);
        Route::post('/complaints/{id}/status', [AgencyComplaintController::class, 'updateStatus']);
        Route::post('/complaints/{id}/note', [AgencyComplaintController::class, 'addNote']);
        Route::post('/complaints/{id}/request-info', [AgencyComplaintController::class, 'requestMoreInfo']);
    });
});
