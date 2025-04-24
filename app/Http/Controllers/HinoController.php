<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HinoController extends Controller
{
    public function index()
    {
        $data = "hino";
        return view('konverter', compact('data'));
    }
}
