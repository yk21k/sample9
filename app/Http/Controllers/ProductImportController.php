<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;


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

        $sellerId = auth()->user()->shop->id;

        DB::beginTransaction();

        try {
            Excel::import(
                new ProductImport($sellerId),
                $request->file('csv_file')
            );

            DB::commit();

            return back()->with('message', 'CSVインポートが完了しました');

        } catch (ExcelValidationException $e) {

            // ★ CSV Validation エラー
            DB::rollBack();

             // ★ ここ重要：failures取得
            $failures = $e->failures();

            return back()
                ->with('import_failures', $failures)
                ->withErrors([
                    'csv_error' => 'CSVに不正な行があるため、インポートを中止しました',
                ])->with('download_template', true);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e); // ログに残す（重要）

            return back()->withErrors([
                'csv_error' => 'インポート中に予期しないエラーが発生しました',
            ]);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=product_csv_template.csv',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM（Excel対策）
            fwrite($handle, "\xEF\xBB\xBF");

            // ★ ヘッダー（絶対これ）
            fputcsv($handle, [
                'name',
                'description',
                'status',
                'price',
                'shipping_fee',
                'stock',
            ]);

            // ★ サンプル（複数）
            fputcsv($handle, [
                'サンプル商品A',
                '商品説明A',
                0,
                1000,
                250,
                10,
            ]);

            fputcsv($handle, [
                'サンプル商品B',
                '商品説明B',
                0,
                2000,
                500,
                5,
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


}