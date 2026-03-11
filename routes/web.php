<?php

use Illuminate\Support\Facades\Route;

// Vue Router catch-all route
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');