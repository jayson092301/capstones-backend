<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\PcDevicesController;
use App\Http\Controllers\AnnouncementController;

//User Routes
Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/login', [UserController::class, 'login']);
Route::get('user/{id?}', [UserController::class, 'fetchAccountDetails']);

//Scan Routes
Route::post('/scan', [ScanController::class, 'scanQr']);
Route::get('/history/{id?}', [ScanController::class, 'fetchAllHistoryScans']);

//Pc Devices Routes
Route::get('/devices', [PcDevicesController::class, 'fetchAllDevices']);
Route::put('/devices/edit', [PcDevicesController::class, 'editMacAddress']);
Route::post('/devices/is_exist', [PcDevicesController::class, 'checkDevice']);

//Announcement Routes
Route::post('/announcement', [AnnouncementController::class, 'createAnnouncement']);
Route::get('/announcement', [AnnouncementController::class, 'fetchAnnouncement']);

// desktop kill switch
Route::post('/desktop/keep-alive', [ScanController::class, 'keepAlive']);