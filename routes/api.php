<?php
use App\Http\Controllers\Api\ElasticsearchController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\ExamsController;
use App\Http\Controllers\AuthController;
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
Route::post('/account/register', [AccountController::class, 'register']);
Route::post('/account/login', [AccountController::class, 'login']);
Route::post('/account/forgot-password', [AccountController::class, 'forgotPassword']);
Route::post('/account/forgot-password-save', [AccountController::class, 'forgotPasswordSucess']);
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('/account/refresh', [AccountController::class, 'refresh']);
    Route::get('/account', [AccountController::class,'index']);
    Route::post('/account', [AccountController::class, 'store']);
    Route::post('/account/show', [AccountController::class, 'show']);
    Route::get('/account/getauthen',[AccountController::class, 'getAuthenticatedUser']);
    Route::post('/customer/list', [CustomerController::class, 'list']);

    Route::get('/home/index', [HomepageController::class, 'index']);

    Route::get('/department', [DepartmentController::class, 'list']);
    Route::get('/department/detail', [DepartmentController::class, 'detail']);

    Route::get('/exams', [ExamsController::class, 'list']);
    Route::get('/exams/detail', [ExamsController::class, 'detail']);
    Route::get('/get-time-stamp', [HomepageController::class, 'timeStamp']);

    Route::post('/exams/submit', [ExamsController::class, 'submit']);
   
});

 //elasticsearch
 Route::post('/elasticsearch/createindex', [ElasticsearchController::class, 'createindex']);
 Route::post('/elasticsearch/putmapping', [ElasticsearchController::class, 'put_mapping']);
 Route::post('/elasticsearch/getid', [ElasticsearchController::class, 'getid']);
 Route::post('/elasticsearch/searchaccount', [ElasticsearchController::class, 'searchaccount']);
 Route::post('/elasticsearch/dem', [ElasticsearchController::class, 'dem']);
 Route::post('/elasticsearch/updatekhachhang', [ElasticsearchController::class, 'updatekhachhang']);
 Route::post('/elasticsearch/search', [ElasticsearchController::class, 'search']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
