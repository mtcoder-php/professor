<?php

use Illuminate\Support\Facades\Route;

// Barcha routelar Vue ga yo'naltiriladi
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

