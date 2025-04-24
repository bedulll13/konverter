<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Simr4Controller extends Controller
{
    public function index()
    {
        $data = "sim4";
        return view('konverter', compact('data'));
    }
}
