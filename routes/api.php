<?php

use App\Http\Controllers\Auth\UserController;
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
Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user');

    Route::get('/user/logout', function (Request $request) {
        $request->user()->tokens()->delete();
        return response([], 204);
    });

    Route::post('/get-running-process', [UserController::class,'getRunningProcesses'])->name('get-running-process');
    Route::post('/create-new-directory', [UserController::class,'createNewDirectory'])->name('create-new-directory');
    Route::post('/get-list-of-directories', [UserController::class,'getListOfDirectories'])->name('get-list-of-directories');
    Route::post('/get-list-of-files', [UserController::class,'getListOfFiles'])->name('get-list-of-files');
});

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');
