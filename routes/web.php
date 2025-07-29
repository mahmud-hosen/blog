<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;

Route::get('/mahmud', function () {

    // dd('Welcome to the Laravel application!');

    return view('mahmud');
});

Route::post('/person', [PersonController::class, 'store'])->name('person.store');

