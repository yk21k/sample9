@extends('layouts.seller')


@section('content')
<div>
	<h1>オークション　オーダー</h1>
	
<style>
    table.auction-orders {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    table.auction-orders th, table.auction-orders td {
        border: 1px solid #ccc;
        padding: 8px 12px;
        text-align: left;
    }
    table.auction-orders th {
        background-color: ;
    }
</style>

<table class="auction-orders">
    <thead>
        <tr>
            <th><small>決済日</small></th>
            <th><small>オークション名</small></th>
            <th><small>購入者</small></th>
            <th><small>配送状況</small></th>
            <th><small>到着確認(確認日)</small></th>
            <th><small>決済金額</small></th>
            <th><small>宛名（住所）</small></th>
            <th><small>電話番号</small></th>
            <th><small>配送会社</small></th>
            <th><small>伝票番号</small></th>
        </tr>
    </thead>
    <tbody>
        @foreach($auction_orders as $auction_order)
            <tr>
                <td><small>{{ \Carbon\Carbon::parse($auction_order->payment_at)->format('Y-m-d') ?? '未確認' }}</small></td>
                <td><small>{{ $auction_order->auction->name }}</small></td>
                <td><small>{{ $auction_order->winner->name }}</small></td>
                <td>
                    @php
                        $statuses = ['発送手配が必要です', '配送手配中', '配送手配済', '配達完了'];
                    @endphp
                    <small>{{ $statuses[$auction_order->delivery_status] ?? '不明' }}</small>
                    @if($auction_order->delivery_status == 0)
					    <form method="POST" action="{{ route('seller.auction.delivered_company', $auction_order->id) }}">
					        @csrf
					        <button type="submit" class="btn btn-warning btn-sm"><small>配送手配中へ</small></button>
					    </form>
					@endif
					@if ($auction_order->delivery_status == 1)
					    <button type="button" id="show-shipping-form-btn" class="btn btn-info btn-sm mt-2">
					        配送情報を入力する
					    </button>
					    <form method="POST" action="{{ route('seller.auction.delivered_arranged', $auction_order->id) }}">
					        @csrf
						    <div id="shipping-form" style="display: none; margin-top: 10px;">
						        <div class="form-group">
						            <label>配送業者名</label>
						            <input type="text" class="form-control" name="shipping_company" required>
						        </div>
						        <div class="form-group">
						            <label>受付番号</label>
						            <input type="text" class="form-control" name="reception_number" required>
						        </div>
						        <button type="submit" class="btn btn-success btn-sm"><small>内容を確定</small></button>
						    </div>
				        		
				    	</form>
				    	
					@endif
					@if($auction_order->delivery_status == 2)
						<form method="POST" action="{{ route('seller.auction.delivered', $auction_order->id) }}" class="confirm-delivery-form">
					        @csrf
					        <button type="submit" class="btn btn-primary btn-sm"><small>完了にする</small></button>
					    </form>
			        	
			        @endif
                </td>
                @php
                    $arrival_statuses = ['未着', '到着'];
                @endphp
                <td>
				    <small>
				        {{ $arrival_statuses[$auction_order->arrival_status] ?? '未着' }} /
				        {{ $auction_order->arrival_message ?: '未確認' }} /
				        {{
				            $auction_order->arrival_confirmed_at
				                ? \Carbon\Carbon::parse($auction_order->arrival_confirmed_at)->format('Y-m-d')
				                : '未確認'
				        }}
				    </small>
				</td>
                <td></small>¥{{ number_format($auction_order->final_price) }}</small></td>
                <td>
                    <small>{{ $auction_order->shipping_fullname }}<br>
                    （{{ $auction_order->shipping_zipcode }}<br>
                    {{ $auction_order->shipping_state }}<br>{{ $auction_order->shipping_city }}<br>{{ $auction_order->shipping_address }}</small>
                </td>
                <td><small>{{ $auction_order->shipping_phone }}</small></td>
                <td style="white-space: nowrap;"><small>{{ $auction_order->shipping_company }}</small></td>
                <td><small>{{ $auction_order->reception_number }}</small></td>
            </tr>
        @endforeach
    </tbody>
</table>

	
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const showFormBtn = document.getElementById('show-shipping-form-btn');
        const shippingForm = document.getElementById('shipping-form');

        if (showFormBtn && shippingForm) {
            showFormBtn.addEventListener('click', function () {
                shippingForm.style.display = 'block';
                showFormBtn.style.display = 'none'; // ボタンを非表示にする場合
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.confirm-delivery-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!confirm('この操作は取り消せません。本当に「完了にする」でよろしいですか？')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>

@endsection