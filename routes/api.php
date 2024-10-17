<?php

use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\AbsenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('permissions/detail', [PermissionController::class, 'show'])->name('api.permissions.show');
Route::get('leaves/detail', [LeaveController::class, 'show'])->name('api.leaves.show');
Route::get('absen/detail', [AbsenController::class, 'show'])->name('api.absen.show');
