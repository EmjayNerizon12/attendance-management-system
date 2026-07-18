<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-app-key', function () {
    return [
        'app_key_env' => env('APP_KEY'),
        'app_key_config' => config('app.key'),
        'app_url' => env('APP_URL'),
    ];
});