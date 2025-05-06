<?php

use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\TaskController;
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

// Board Routes
Route::apiResource('boards', BoardController::class);

// Column Routes
Route::get('boards/{board}/columns', [ColumnController::class, 'index']);
Route::post('boards/{board}/columns', [ColumnController::class, 'store']);
Route::get('columns/{column}', [ColumnController::class, 'show']);
Route::put('columns/{column}', [ColumnController::class, 'update']);
Route::delete('columns/{column}', [ColumnController::class, 'destroy']);
Route::post('boards/{board}/column-positions', [ColumnController::class, 'updatePositions']);
Route::post('columns/{column}/move', [ColumnController::class, 'moveColumn']);

// Task Routes
Route::get('columns/{column}/tasks', [TaskController::class, 'index']);
Route::post('columns/{column}/tasks', [TaskController::class, 'store']);
Route::get('tasks/{task}', [TaskController::class, 'show']);
Route::put('tasks/{task}', [TaskController::class, 'update']);
Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
Route::post('tasks/{task}/move', [TaskController::class, 'move']);
Route::post('columns/{column}/task-positions', [TaskController::class, 'updatePositions']);
