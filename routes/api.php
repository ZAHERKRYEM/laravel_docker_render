<?php

use App\Http\Controllers\AuthController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\VideoController;
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




Route::post('/v1.1/register/', [AuthController::class, 'register']);
Route::post('/v1.1/login/', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {

    Route::post('/v1.1/logout/', [AuthController::class, 'logout']);
    Route::post('/v1.1/token/refresh/', [AuthController::class, 'refresh']);

    Route::post('/v1.1/subject/', [SubjectController::class, 'store']);

    Route::get('/v1.1/videos/{course_id}/', [VideoController::class, 'index']);
    Route::post('/v1.1/videos/', [VideoController::class, 'store']);


    Route::post('/v1.1/courses/', [CourseController::class, 'store']);
    Route::get('/v1.1/courses/all/', [CourseController::class, 'index']);
    Route::get('/v1.1/courses/search/', [CourseController::class, 'search']);
    Route::get('/v1.1/courses/by-year/', [CourseController::class, 'byyear']);
    Route::get('/v1.1/courses/one-per-year/', [CourseController::class, 'oneperyear']);

});

