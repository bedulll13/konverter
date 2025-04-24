<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HpmController extends Controller
{
    public function index()
    {
        $data = "hpm";
        return view('konverter', compact('data'));
    }
}
