<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KmiController extends Controller
{
    public function index()
    {
        $data = "kmi";
        return view('konverter', compact('data'));
    }
}
