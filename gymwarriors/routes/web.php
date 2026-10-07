<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BodyMetricController;

Route::middleware('auth')->group(function () {
    Route::get('/api/body-metrics', [BodyMetricController::class, 'index']);
    Route::post('/api/body-metrics', [BodyMetricController::class, 'store']);
});

Route::get('/api/user', function () {
    if (!Auth::check()) {
        return response()->json([
            'authenticated' => false,
        ], 401);
    }

    $user = Auth::user();

    return response()->json([
        'authenticated' => true,
        'user' => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
        ],
    ]);
});

Route::get('/register', function () {
    return view('welcome');
})->name('register');

Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Vue SPA
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');