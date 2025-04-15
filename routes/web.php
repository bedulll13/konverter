<?php

use App\Http\Controllers\KonverterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route :: get('/adm',[KonverterController::class,'index']);
Route :: post('/adm',[KonverterController::class,'upload'])->name('excel.upload');
