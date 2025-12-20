@extends('layouts.seller')

@section('content')
<div class="container">
    <h2>{{ $shop->name }} の受取商品のオーダー</h2>

    <style>
        table.pickup-orders {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
            color: black;
        }
        table.pickup-orders th, table.pickup-orders td {
            border: 1px solid #cdd;
            padding: 10px 14px;
            text-align: left;
        }

        /* ヘッダー：薄いシアン */
        table.pickup-orders th {
            background-color: #CCF3F1;
            color: #003d3c;
            font-weight: bold;
        }

        /* ストライプ（交互の背景色） */
        table.pickup-orders tbody tr:nth-child(odd) {
            background-color: #6da6a2;
        }
        table.pickup-orders tbody tr:nth-child(even) {
            background-color: #E8F8F7;
        }

        /* ホバー時 */
        table.pickup-orders tbody tr:hover {
            background-color: #D4F2F0;
        }

        /* ボタン装飾（必要なら） */
        .pickup-orders .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
        }
    </style>
    <form action="{{ route('seller.shop.pickup.csv') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-primary mb-3">
            CSV出力
        </button>
    </form>

    <table class="pickup-orders">
        <thead>
            <tr>
                <th>注文ID</th>
                <th>購入者</th>
                <th>商品名</th>
                <th>数量</th>
                <th>価格</th>
                <th>消費税</th>
                <th>手数料</th>
                <th>入金額（予定）</th>
                <th>予定</th>
                <th>状況</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pickOrders as $item)
                <tr>
                    <td>{{ $item->pickup_order_id }}</td>
                    <td>{{ $item->order->user->name }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>¥{{ number_format($item->price) }}</td>
                    @if($shop->invoice_number)
                        <td>¥{{ $item->price * 1/(1 +$tax) * $tax }}</td>
                        <td>¥{{ $item->price * 1/(1 +$tax) * $feeRate }}</td>
                        <td>¥{{ $item->price - ($item->price * 1/(1 +$tax)  * $feeRate) }}</td>

                    @else
                        <td>非課税事業者</td>
                        <td>¥{{ $item->price * $feeRate }}</td>
                        <td>¥{{ $item->price - ($item->price * $feeRate) }}</td>
                    @endif

                    <td>
					    {{ \Carbon\Carbon::parse($item->pickup_date)
					        ->setTimeFromTimeString($item->pickup_time)
					        ->format('Y/m/d H:i') }}
					</td>
                    <td>
                        @if($item->status === 'picked_up')
                            <div style="margin-top: 6px;">
                            	受渡済
                                <form action="{{ route('shop.sendPickupConfirmation', $item->id) }}" method="POST">
                                    @csrf
                                    {{ $item->received_at }}
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="voyager-mail"></i> 受け渡しメール
                                    </button>
                                </form>
                            </div>
                        @elseif($item->status === 'pending_confirmation')
                            <div style="margin-top: 6px;">
                                受渡済
                                <form action="" method="POST">
                                    @csrf
                                    {{ $item->received_at }}
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="voyager-mail"></i> 送金依頼
                                    </button>
                                </form>
                            </div>
                        
                        @else
                        	<div style="margin-top: 6px;">
                        		未渡し	
                        	</div>    
                        @endif
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        受取オーダーがありません
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
