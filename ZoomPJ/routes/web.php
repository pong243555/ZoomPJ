<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoomController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/link1', function () {
    return view('link1');
});

Route::get('/link2', function () {
    return view('link2');
});

Route::get('/users', [UserController::class, 'index'])->name('user');

// Define a route for the Zoom OAuth authorization redirect
//Route::get('/zoom/authorize', [ZoomController::class, 'redirectToZoom']);

// Define a route for handling the Zoom OAuth callback
//Route::get('/zoom/callback', [ZoomController::class, 'handleZoomCallback']);

// Define a route for making a Zoom API request
//Route::get('/zoom/me', [ZoomController::class, 'makeApiRequest'])->middleware('auth');

