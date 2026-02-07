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

            return back()
                ->with('import_failures', $e->failures())
                ->withErrors([
                    'csv_error' => 'CSVに不正な行があるため、インポートを中止しました',
                ]);

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
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=product_csv_template.csv',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // ★ Excel 文字化け防止（UTF-8 BOM）
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー
            fputcsv($handle, [
                'name',
                'description',
                'status',        // 1=Active / 0=InActive
                'price',
                'shipping_fee',
                'stock',
            ]);

            // サンプル行（1件のみ）
            fputcsv($handle, [
                'サンプル商品',
                '商品説明を入力してください',
                0,          // InActive（初期登録推奨）
                1000,
                500,
                10,
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }



}