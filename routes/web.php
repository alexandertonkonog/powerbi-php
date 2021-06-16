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

Route::post('/api/report/set', [ReportController::class, 'setReports']);
Route::post('/api/report/group/set', [ReportController::class, 'setReportGroup']);
Route::post('/api/report/group/fill', [ReportController::class, 'setReportIntoGroup']);
Route::get('/api/report/get', [ReportController::class, 'getReports']);
Route::get('/api/report/user/get', [ReportController::class, 'getReportsForUser']);
Route::get('/api/report/group/get', [ReportController::class, 'getReportGroups']);

Route::post('/api/user/group/set', [UserController::class, 'setUserGroup']);
Route::post('/api/user/group/fill', [UserController::class, 'setUsersIntoGroup']);
Route::post('/api/user/set', [UserController::class, 'setUsers']);
Route::post('/api/user/report/fill', [UserController::class, 'setReportIntoUser']);
Route::post('/api/user/group/report/fill', [UserController::class, 'setReportIntoUserGroup']);
Route::get('/api/user/get', [UserController::class, 'getUsers']);
Route::get('/api/user/group/get', [UserController::class, 'getUserGroups']);
Route::get('/api/token/get', [UserController::class, 'getToken']);


