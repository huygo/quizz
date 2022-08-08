<?php
use App\Http\Controllers\AppController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;


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
Route::get('/login', [AppController::class,'login'])->name('login');
Route::post('/login', [AppController::class,'accountlogin']);

Route::group(['middleware' => ['auth']], function() {
    Route::get('/', [HomeController::class,'index'])->name('homepage');
    Route::get('/logout', [AppController::class,'logout'])->name('logout');
    Route::get('/customer', [CustomerController::class,'index']);
    Route::post('/customer/search', [CustomerController::class,'search'])->name('customer.search');
    Route::post('/customer/excel', [CustomerController::class,'excel']);

    
    Route::get('/news', function () {
        return view('layouts.news');
    });
});
