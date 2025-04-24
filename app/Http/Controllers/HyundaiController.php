<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HyundaiController extends Controller
{
    public function index()
    {
        $data = "hyundai";
        return view('konverter', compact('data'));
    }
}
