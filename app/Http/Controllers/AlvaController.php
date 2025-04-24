<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AlvaController extends Controller
{
    public function index()
    {
        $data = "alva";
        return view('konverter', compact('data'));
    }
}