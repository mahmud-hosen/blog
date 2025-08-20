<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\SFTPFileUploadController;

Route::get('/', function () {

    // dd('Welcome to the Laravel application!');

    return view('mahmud');
});

Route::post('/person', [PersonController::class, 'store'])->name('person.store');



Route::get('/sftp-upload', [SFTPFileUploadController::class, 'index']);
Route::post('/sftp-upload', [SFTPFileUploadController::class, 'upload'])->name('sftp.upload');


