<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;

class ProductImportController extends Controller
{
    public function showForm()
    {
        return view('admin.product.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $sellerId = auth()->user()->shop->id;  // ここでショップIDを取得

        Excel::import(new ProductImport($sellerId), $request->file('csv_file'), null, \Maatwebsite\Excel\Excel::CSV);



        return redirect()->back()->with('message', 'CSVインポート成功しました');

    }
}