<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Simr2Controller extends Controller
{
    public function index()
    {
        $data = "simr2";
        return view('konverter', compact('data'));
    }
}
