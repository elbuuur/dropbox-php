<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrashController;

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

Route::post('register', [Auth\RegisterController::class, 'register']);
Route::post('login', [Auth\LoginController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('user-info', [Auth\LoginController::class, 'info']);

    Route::post('folder/create', [FolderController::class, 'create']);
    Route::post('folder/{folder}', [FolderController::class, 'index']);
    Route::apiResource('folder', FolderController::class)->except([
        'create',
        'store'
    ]);

    Route::post('file/upload', [FileController::class, 'upload'])->middleware('checkFileLimit');
    Route::apiResource('file', FileController::class)->except([
        'create',
        'store'
    ]);

    Route::get('download/{mediaUuid}', [DownloadController::class, 'downloadFile']);

    Route::post('home', [HomeController::class, 'getStructureFromRoot']);

    Route::controller(TrashController::class)->group(function () {
        Route::post('/trash', 'index');

        Route::delete('/trash/delete', 'deleteItems');
        Route::delete('/trash/delete-all', 'deleteAll');

        Route::post('/trash/restore', 'restoreItems');
    });
});
