<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoomController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
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

Route::post('/language', function (Request $request) {
    $validated = $request->validate([
        'locale' => ['required', 'in:en,th'],
    ]);

    $request->session()->put('locale', $validated['locale']);

    return back();
})->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/admin/sign-in', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/sign-in', [AuthController::class, 'adminLogin'])->middleware('throttle:5,1')
        ->name('admin.login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/zoom', [ZoomController::class, 'index'])->name('zoom.index');
    Route::get('/zoom/connect', [ZoomController::class, 'connect'])->name('zoom.connect');
    Route::get('/zoom/callback', [ZoomController::class, 'callback'])->name('zoom.callback');
    Route::delete('/zoom/connection', [ZoomController::class, 'disconnect'])->name('zoom.disconnect');
    Route::post('/zoom/meetings', [ZoomController::class, 'store'])->name('zoom.meetings.store');
    Route::get('/zoom/meetings/{meetingId}/edit', [ZoomController::class, 'edit'])->name('zoom.meetings.edit');
    Route::put('/zoom/meetings/{meetingId}', [ZoomController::class, 'update'])->name('zoom.meetings.update');
    Route::delete('/zoom/meetings/{meetingId}', [ZoomController::class, 'destroy'])->name('zoom.meetings.destroy');

    Route::get('/link2', [UserController::class, 'index']);
    Route::get('/users', [UserController::class, 'index'])->name('user');
});
