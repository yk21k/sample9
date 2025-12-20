<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupOrderItem;
use App\Models\TaxRate;
use App\Models\Commition;


class PickUpOrderController extends Controller
{
    public function index()
    {
        // 店舗ユーザーがログインしている前提
        $shop = auth()->user()->shops()->first(); // ユーザーが複数店舗持つなら first()

        if ($shop) {

            // 店舗の取り扱い商品IDを取得（null 対策つき）
            $productIds = optional($shop->pickup_products)->pluck('id') ?? [];

            // 商品が1つも無い場合 → 空コレクション
            if ($productIds->isEmpty()) {
                $pickOrders = collect();
            } else {
                // 商品が紐づく受取オーダーを取得
                $pickOrders = PickupOrderItem::whereIn('product_id', $productIds)
                    ->with(['order', 'product']) // 必要に応じて
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            $pre_tax = TaxRate::where('is_active', 1)->first();
            $tax = $pre_tax->rate;

            $feeRate = Commition::current()?->rate ?? 0; // 10% 手数料

        } else {
            // 店舗がない場合の空データ
            $pickOrders = collect();
        }

        return view('sellers.pickup.orders.index', compact('pickOrders', 'shop', 'tax', 'feeRate'));
    }

    public function exportCsv()
    {
        $shop = auth()->user()->shops()->first();

        if (!$shop) {
            abort(404, 'ショップが見つかりません');
        }

        // 一覧と同じデータ取得
        $pickOrders = PickupOrderItem::with(['order.user', 'product'])
            ->whereIn('product_id', $shop->pickup_products->pluck('id'))
            ->get();

        // CSV ヘッダー
        $csvHeader = [
            '注文ID',
            '購入者',
            '商品名',
            '数量',
            '価格',
            '消費税',
            '手数料',
            '入金額（予定）',
            '受取予定日時',
            'ステータス',
        ];

        // CSV 内容を生成
        $csvData = [];

        foreach ($pickOrders as $item) {

            // 時間フォーマット
            $pickupDate = "'" . \Carbon\Carbon::parse($item->pickup_date)->setTimeFromTimeString($item->pickup_time)->format('Y/m/d H:i');


            // 税・手数料 計算
            $tax = 0.1;
            $feeRate = 0.05; // 例：5%

            if ($shop->invoice_number) {
                $taxAmount = $item->price * (1/(1+$tax)) * $tax;
                $feeAmount = $item->price * (1/(1+$tax)) * $feeRate;
                $deposit = $item->price - $feeAmount;
            } else {
                $taxAmount = 0;
                $feeAmount = $item->price * $feeRate;
                $deposit = $item->price - $feeAmount;
            }

            // ▼▼ ステータスを CSV 表示用に変換 ▼▼
            if ($item->status === 'received') {
                $statusText = '受取確認済（' . optional($item->received_at)->format('Y/m/d H:i') . '）';
            } elseif ($item->status === 'pending_confirmation') {
                $statusText = '受渡済';
            } else {
                $statusText = '未渡し';
            }
            // ▲▲ ステータス変換ここまで ▲▲

            $csvData[] = [
                $item->pickup_order_id,
                $item->order->user->name,
                $item->product->name,
                $item->quantity,
                $item->price,
                round($taxAmount),
                round($feeAmount),
                round($deposit),
                $pickupDate,
                $statusText,
            ];
        }

        // CSV 出力
        $filename = 'pickup_orders_' . now()->format('Ymd_His') . '.csv';

        $handle = fopen('php://temp', 'r+');

        // BOM（Excel文字化け対策）
        fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // ヘッダー行
        fputcsv($handle, $csvHeader);

        // データ行
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return response()->streamDownload(function () use ($handle) {
            fpassthru($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }




}
