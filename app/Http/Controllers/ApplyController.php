<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ApplyController extends Controller
{
    public function index()
    {
        return view('landing_section_variant.apply');
    }

    public function bye()
    {
        return view('landing_section_variant.bye');
    }
}

