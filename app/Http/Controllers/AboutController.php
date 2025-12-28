<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $target = 'seller'; // VoyagerのFAQカテゴリで seller を割り当てたID

        $faqs = Faq::where('target', $target)
            ->where('is_approved', true)
            ->whereNull('shop_id')
            ->get();

        return view('landing_section_variant.about', compact('faqs'));
    }
}

