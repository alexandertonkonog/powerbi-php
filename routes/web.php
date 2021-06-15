<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApiController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/report/set', [ReportController::class, 'setReports']);
Route::post('/report/group/set', [ReportController::class, 'setReportGroup']);
Route::post('/report/group/fill', [ReportController::class, 'setReportIntoGroup']);
Route::get('/report/get', [ReportController::class, 'getReports']);
Route::get('/report/group/get', [ReportController::class, 'getReportGroups']);

Route::post('/user/group/set', [UserController::class, 'setUserGroup']);
Route::post('/user/group/fill', [UserController::class, 'setUsersIntoGroup']);
Route::post('/user/set', [UserController::class, 'setUsers']);
Route::post('/user/report/fill', [UserController::class, 'setReportIntoUser']);
Route::post('/user/group/report/fill', [UserController::class, 'setReportIntoUserGroup']);
Route::get('/user/get', [UserController::class, 'getUsers']);
Route::get('/user/group/get', [UserController::class, 'getUserGroups']);

Route::get('/api/token', [ApiController::class, 'getToken']);

