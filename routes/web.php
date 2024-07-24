<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/help/{id}', function ($id) {
    dd($id);

    return view('help');
});
