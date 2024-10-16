<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/user/{id}', [UserController::class, 'show']);
    Route::get('/admin/user/{id}/tasks', [UserController::class, 'getUserTasks']);
    Route::post('/admin/user', [UserController::class, 'store']);
    Route::put('/admin/user/{id}', [UserController::class, 'update']);
    Route::delete('/admin/user/{id}', [UserController::class, 'destroy']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks-by-role', [TaskController::class, 'roleTasks']);
    Route::get('/task/{id}', [TaskController::class, 'show']);
    Route::post('/task', [TaskController::class, 'store']);
    Route::put('/task/{id}', [TaskController::class, 'update']);
    Route::delete('/task/{id}', [TaskController::class, 'destroy']);
});
