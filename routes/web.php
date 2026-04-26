<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;

Route::get('/users/search', [UserController::class, 'search']);
Route::post('/users',       [UserController::class, 'store']);

Route::post('/files/convert', [FileController::class, 'convert']);
Route::get('/files/show',     [FileController::class, 'show']);

Route::get('/search', function () {
    return view('search', [
        'query'   => request('q', ''),
        'results' => [],
    ]);
});
