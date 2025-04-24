<?php

use App\Http\Controllers\AlvaController;
use App\Http\Controllers\HinoController;
use App\Http\Controllers\HpmController;
use App\Http\Controllers\HyundaiController;
use App\Http\Controllers\KmiController;
use App\Http\Controllers\KonverterController;
use App\Http\Controllers\Simr2Controller;
use App\Http\Controllers\Simr4Controller;
use App\Http\Controllers\TmminController;
use App\Http\Controllers\YimmController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route :: get('/adm',[KonverterController::class,'index']);
Route :: post('/adm',[KonverterController::class,'upload'])->name('excel.upload');
Route :: get('/tmmin',[TmminController::class,'index']);
Route :: post('/tmmin',[TmminController::class,'upload'])->name('tmmin.upload');
Route :: get('/yimm',[YimmController::class,'index']);
Route :: post('/yimm',[YimmController::class,'upload'])->name('yimm.upload');
Route :: get('/hpm',[HpmController::class,'index']);
Route :: post('/hpm',[HpmController::class,'upload'])->name('hpm.upload');
Route :: get('/simr2',[Simr2Controller::class,'index']);
Route :: post('/simr2',[Simr2Controller::class,'upload'])->name('simr2.upload');
Route :: get('/simr4',[Simr4Controller::class,'index']);
Route :: post('/simr4',[Simr4Controller::class,'upload'])->name('simr4.upload');
Route :: get('/hyundai',[HyundaiController::class,'index']);
Route :: post('/hyundai',[HyundaiController::class,'upload'])->name('hyundai.upload');
Route :: get('/hino',[HinoController::class,'index']);
Route :: post('/hino',[HinoController::class,'upload'])->name('hino.upload');
Route :: get('/kmi',[KmiController::class,'index']);
Route :: post('/kmi',[KmiController::class,'upload'])->name('kmi.upload');
Route :: get('/alva',[AlvaController::class,'index']);
Route :: post('/alva',[AlvaController::class,'upload'])->name('alva.upload');