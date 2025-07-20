@extends('layouts.app')

@section('content')


@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<h2> アカウント　Your Account </h2>
		<ul class="nav">
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#">Order History</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#">Shipping Address</a></h3>
		  </li>
			@php
			    $open_shops = App\Models\Shop::where('user_id', auth()->user()->id)->first();
			@endphp

			@if($open_shops && $open_shops->is_draft == 1)
			    <li class="nav-item">
			        <h3><a class="nav-link link-secondary" href="{{ route('shops.create') }}">店舗開設申請</a></h3>
			    </li>
			@elseif($open_shops)
			    <li class="nav-item">
			        <h3><a class="nav-link link-secondary">あなたの店舗は開設済みです。</a></h3>
			    </li>
			@else
			    <li class="nav-item">
			        <h3><a class="nav-link link-secondary" href="{{ route('shops.create') }}">店舗を登録してください</a></h3>
			    </li>
			@endif

		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="{{ route('inquiries.create', ['id' => auth()->user()->id]) }}">お問合せをする</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#wishlist">欲しいものに登録</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#payment-methods">-----</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#account-settings">Accountの設定</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#support">サポート</a></h3>
		  </li>
		</ul>
 <br><br> 
       
<div class="container">
    <style>
        #auction-history table {
            width: 100%;
            border-collapse: collapse;
        }

        #auction-history th, #auction-history td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: center;
        }
        #auction_order_img{
        	width: 50px;
        	height: 50px;
        }
    </style>
    <main>
    	<section id="auction-history" class="section_auction">
    		<h2>オークション購入履歴</h2>
    		<br>
    		<button type="button" class="btn btn-sm btn-info" id="hide_button4">Hide/Display</button>
		    	<br>
				<ul id="auction-order-history">
			    
			    @if(empty($auction_orders) || $auction_orders->isEmpty())
			    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<p><h5>&nbsp;&nbsp;&nbsp;&nbsp;当サイトのオークションでの購入履歴がありません</h5></p><br><br><br>
			    @else
			    	<table>
		    			<head>
		    				<tr>
		    					<th></th>
		    					<th>オークション名</th>
		    					<th>決済日</th>
		    					<th>決済額 内)配送料</th>
		    					<th>配送状況</th>
		    					<th>配送業者名</th>
		    					<th>受付番号</th>
		    					<th>更新日</th>
		    				<tr>
		    			</head>
		    			<tbody>
		    				@foreach($auction_orders as $auction_order)
		    				<tr>
		    					<td><img class="" src="{{ asset( 'storage/'.$auction_order->cover_img1 ) }}" alt=""  width="100" height="80"></td>
		    					<td>{{$auction_order->name}}</td>
		    					<td>{{$auction_order->payment_at}}</td>
		    					<td>¥{{$auction_order->final_price}}&nbsp;<small>(¥{{$auction_order->shipping_fee}})</small></td>

		    					@if($auction_order->delivery_status == 0)
		    						<td>準備中</td>

		    					@elseif($auction_order->delivery_status == 1)
		    						<td>配送手配中</td>

		    					@elseif($auction_order->delivery_status == 2)

		    						<td>配送手配済</td>

		    					@else($auction_order->delivery_status == 3)

		    						<td>
		    							配達完了
		    							@php
										    $confirmedAt = $auction_order->arrival_confirmed_at;
										    $showForm = !$confirmedAt || Carbon\Carbon::parse($confirmedAt)->addWeek()->isFuture();

										    // モーダル表示の追加条件：メール送信から1週間以内
										    $mailSentAt = $auction_order->mail_sent_at;
										    $withinMailLimit = !$mailSentAt || Carbon\Carbon::parse($mailSentAt)->addWeek()->isFuture();
										@endphp
										@if($auction_order->arrival_status==1)
											{{ Carbon\Carbon::parse($auction_order->arrival_confirmed_at)->format('Y年m月d日') }} に到着確認済

										@elseif($auction_order->arrival_status==0 && $showForm && $withinMailLimit)
			    							<!-- モーダルを開くトリガーボタン -->
										    <button type="button" class="btn btn-success btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#confirmArrivalModal-{{ $auction_order->id }}">
										        到着を確認する
										    </button>

										    <!-- モーダル本体 -->
										    <div class="modal fade" id="confirmArrivalModal-{{ $auction_order->id }}" tabindex="-1" aria-labelledby="confirmArrivalModalLabel-{{ $auction_order->id }}" aria-hidden="true">
										        <div class="modal-dialog modal-dialog-centered">
										            <div class="modal-content">
										                <form method="POST" action="{{ route('auction.delivery.confirm', $auction_order->id) }}">
										                    @csrf
										                    <div class="modal-header">
										                        <h5 class="modal-title" id="confirmArrivalModalLabel-{{ $auction_order->id }}">商品の到着を確認</h5>
										                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
										                    </div>
										                    <div class="modal-body">
										                        <div class="form-group mb-2">
										                            <label for="arrival_message">コメント</label>
										                            <textarea name="arrival_message" class="form-control" rows="3" placeholder="商品の到着についてコメントを入力"></textarea>
										                        </div>
										                        <div class="form-group">
																    <label for="arrival_limit" style="color: tomato;">到着の回答期限は</label><br>

																    @if($auction_order->mail_sent_at)
																        <small>
																            {{ \Carbon\Carbon::parse($auction_order->mail_sent_at)->format('Y年m月d日') }} の（+7日後の）
																        </small>
																        <h4 style="color: tomato;">
																            → {{ \Carbon\Carbon::parse($auction_order->mail_sent_at)->addDays(7)->format('Y年m月d日') }} です。
																        </h4>
																    @else
																        <small>メールがまだ送信されていません。</small>
																    @endif
																</div>
										                    </div>
										                    <div class="modal-footer">
										                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
										                        <button type="submit" class="btn btn-success">到着を確認する</button>
										                    </div>
										                </form>
										            </div>
										        </div>
										    </div>
								        @endif    
		    						</td>	

		    					@endif
		    					<td>{{$auction_order->shipping_company}}</td>
		    					<td>{{$auction_order->reception_number}}</td>
		    					<td>{{$auction_order->updated_at}}</td>
		    					
		    				</tr>
		    				@endforeach
		    			</tbody>
		    		</table><br><br><br>
		    		
			    @endif
				<ul>
				<br>	
    	</section><br>

        <section id="order-history" class="section_accocnt">
            <h2>注文履歴　Order history</h2>
            <ul>
			<h4>Search</h4>

			{{-- 日付フィルタ（formの外） --}}
			<input id="myInput" type="date" class="form-control mr-2 mb-2" style="width: 200px; display: inline-block;">

			{{-- 検索フォーム --}}
			<div class="d-flex flex-wrap align-items-center mb-4">
			    
			    <div class="d-flex flex-wrap align-items-center mb-3">
				    <input type="text" id="productInput" class="form-control mr-2 mb-2" placeholder="商品名" style="width: 200px;">
				    
				    <select id="statusSelect" class="form-control mr-2 mb-2" style="width: 150px;">
				        <option value="">発送状況</option>
				        <option value="*配送前（オーダー確認中を含む）">*配送前（オーダー確認中を含む）</option>
				        <option value="**配送前（配送準備中）">**配送前（配送準備中）</option>
				        <option value="***配送会社へ手配中">***配送会社へ手配中</option>
				        <option value="*配達完了* ">*配達完了* </option>
				    </select>
				</div>
			    	
			</div>

			{{-- Hide/Display ボタン --}}
			<button type="button" class="btn btn-sm btn-info" id="hide_button">Hide/Display</button>

				<br><br>
            	<table class="table table-bordered table-striped" id="table_order1">
	            	<thead>	
		        	    <tr>

		        	    	<td>発送状況</td>
		        	    	<td>配達業者</td>
		        	    	<td>Invoice Number　会社によって異なる</td>
					        <td>Order Number</td>
					        <td>購入日</td>
					        <td>名前</td>
					        <td>郵便番号</td>
					        <td>住所</td>
					        <td>商品名/個数</td>
					        
				        </tr>
			        </thead>
		        	@foreach($order_histories as $order_history)
		        	    
		        	<tbody id="myTable">
		        		<tr>
		        			@if($order_history->status=="pending" && $order_history->payment_status=="")
		        				
		        				<td class="status-cell">*配送前（オーダー確認中を含む）</td>

		        			@elseif($order_history->status=="pending" && $order_history->payment_status=="accepted")
		        				
		        				<td class="status-cell">**配送前（配送準備中）</td>	

		        			@elseif($order_history->status=="pending" && $order_history->payment_status=="arranging delivery")
		        				
		        				<td class="status-cell">***配送会社へ手配中</td>

		        			@elseif($order_history->status=="processing" && $order_history->payment_status=="delivery arranged")

		        				<td class="status-cell">****配送会社へ手配済み</td>

		        			@elseif($order_history->status=="completed")	
		        				<td class="status-cell">*配達完了* 
			        				<a class="btn btn-warning btn-sm" id="hide_arrival_button" style="margin: 2px;">到着確認</button></a><br> 
	                                <div class="form_arrival">
								    	@php

										    $arrival = App\Models\SubOrdersArrivalReport::where('sub_order_id', $order_history->id)->first();
										    $today = Carbon\Carbon::today();
										@endphp

										@if($arrival && $arrival->arrival_reported == 1)
										    <p>すでに確認済みです（{{ $arrival->created_at->format('Y年m月d日') }}）</p>

										@elseif($arrival && Carbon\Carbon::parse($arrival->confirmation_deadline)->lt($today))
										    <p>到着確認の期限は（{{ Carbon\Carbon::parse($arrival->confirmation_deadline)->format('Y年m月d日') }}）でした。期限超過ため到着確認済</p>

										@else
										    <form method="POST" action="{{ route('account.arrival', Auth::user()->id) }}">
										        @csrf
										        <p>この商品の到着を確認したら、下のボタンを押してください。</p>
										        <input type="hidden" name="sub_order_id" value="{{ $order_history->id }}">
										        <input type="hidden" name="arrival_reported" value="1">
										        <div>
										            <label for="comments">コメント（任意）:</label><br>
										            <textarea name="comments" id="comments" rows="4" cols="50">{{ old('comments') }}</textarea>
										        </div>

										        <button type="submit" class="btn btn-info">到着を報告する</button>
										    </form>
										@endif

    
	                                </div>
                                </td>
		        			@else
		        				<td>Details Unknown</td>
		        			@endif

		        			<td>{{ $order_history->shipping_company }}</td>
		        			<td>{{ $order_history->invoice_number }}</td>
		        			<td>{{ $order_history->order_id }}</td>
		        			<td>{{ $order_history->created_at }}</td>
		        			<td>{{ $order_history->order->shipping_fullname }}</td>
		        			<td>{{ $order_history->order->shipping_zipcode }}</td>
		        			<td>{{ $order_history->order->shipping_state }} {{ $order_history->order->shipping_city }} {{ $order_history->order->shipping_address }}</td>
						    @php
						        $items = $itemsGrouped[$order_history->id] ?? collect();
						    @endphp
						    
			                {{-- 商品名／個数 --}}
			                <td>
			                    @foreach($items as $item)
			                        @php
			                            $product = \App\Models\Product::find($item->product_id);
			                        @endphp
			                        <div>
			                            {{ $product->name ?? '商品ID:'.$item->product_id }}
			                            <span class="badge bg-secondary">x{{ $item->quantity }}</span>
			                        </div>
			                    @endforeach
			                </td>
						    
		        		</tr>
			        </tbody>
			        @endforeach
		    	</table>
                
                <br>
                
            </ul>
        </section>

        <section class="review-list111">
            <h2>あなたのレビュー</h2>
            <h4>Search</h4><input id="myInput" type="date">&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-info" id="hide_button3">Hide/Display</button><br><br>
            <ul id="foavoriteItems">
            	@foreach($favaoriteItems as $favaoriteItem)
            		@foreach($favaoriteItem->user_favo as $favoItem)
            			@if($favoItem->user_id == auth()->user()->id)
            			<li>
            				<div class="review-item111">
		                        <div class="review-info111">
		                            <h3>商品名：{{ $favaoriteItem->name }}</h3>
		                            <p>評価: {{ $favoItem->wants }}</p>
		                            <p>投稿日: {{ ($favoItem->created_at)->format('Y年n月j日')}}</p>
		                        </div>
		                       	<p class="review-content111">
	                        		{{ $favoItem->review }}
	                        	</p>
                    		</div>
            			</li>
		                @endif
		            @endforeach    
                @endforeach
            </ul>
        </section>

        <section id="addresses" class="section">
            <h2>Shipping address</h2>
            @if($firstDelis)
            <p>Latest Address: 〒{{ $firstDelis->shipping_zipcode }}
            {{ $firstDelis->shipping_state }}
            {{ $firstDelis->shipping_city }}
            {{ $firstDelis->shipping_address }}</p>
            @foreach($savedDelis as $savedDeli)
            	<p>Other Addresses: 〒{{ $savedDeli->shipping_zipcode }} {{ $savedDeli->shipping_state }} {{ $savedDeli->shipping_city }} {{ $savedDeli->shipping_address }}</p>
            @endforeach
            @else
            <button type="button" class="btn btn-info" id="hide_button2">Hide/Display Register new address</button><br><br>
			<form class="h-adr" id="address1" action="{{route('account.addresses', Auth::user()->id)}}" method="post">@csrf

            	<h4>The Other Address</h4>
	            <div class="form-group">
			        <label for="">Full Name</label>
			        <input type="text" name="shipping_fullname" id="" class="form-control" required>
			    </div>

			    <div class="form-group">
			        <label for="location_1"> <h3>Location * </h3><small>⭐️Please enter the address after entering the postal code.</small></label><br>
			        <span class="p-country-name" style="display:none;">Japan</span>
			        <label for="post-code">Postal Code:</label>
			        <input type="text" class="form-control p-postal-code" name="shipping_zipcode" size="8" maxlength="8" required><br>
			        
		    	</div>

			    <div class="form-group">
			        <label for="">State</label>
			        <input type="text" name="shipping_state" id="" class="form-control p-region" readonly>
			    </div>

			    <div class="form-group">
			        <label for="">City</label>
			        <input type="text" name="shipping_city" id="" class="form-control p-locality" readonly>
			    </div>

			    <div class="form-group">
			        <label for="">Full Address</label>
			        <input type="text" name="shipping_address" id="" class="form-control p-street-address p-extended-address" required>
			    </div>

			    <div class="form-group">
			        <label for="">Mobile</label>
			        <input type="text" name="shipping_phone" id="" class="form-control" required>
			    </div>
		    	<button type="submit" class="btn btn-primary mt-3">save address</button>

            </form>
            @endif
        </section>

        <section id="payment-methods" class="section">
            <h2>支払い方法</h2>
            <p>クレジットカード: xxxx-xxxx-xxxx-1234</p>
            <p>PayPalアカウント: example@email.com</p>
            <button>支払い方法を追加</button>
        </section>

        <section id="account-settings" class="section">
            <h2>Account Settings</h2>
            <p><strong>Username: {{ Auth::user()->name }}</strong></p>
            <p><strong>Email: {{ Auth::user()->email }}</strong></p>
            <div id="account-settings" class="section_accocnt">
    		<h3>Enter it and click the Update button to change it.</h3>
				<form action="{{route('account.account', Auth::user()->id)}}" method="post">@csrf
					<p>Your Name:<input type="text" name="name" value="{{ ($profiles->name) }}"></p>
					<p>Email: <input type="text" name="email" value="{{ ($profiles->email) }}"></p>
					<p>Password: <input type="text" name="password" value=""></p>
					<p>Start: {{ ($profiles->created_at) }}</p>
					<p>Latest: {{ ($profiles->updated_at) }}</p>
			        <button class="btn btn-primary" type="submit">Update</button>
				</form>
			</div><br>
        </section>

        <section id="support" class="section">
            <h2>サポート</h2>
            <p>FAQ | お問い合わせフォーム | カスタマーサポート電話番号: 123-456-7890</p>
        </section>
    </main>
</div>	

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>

<script>
$(function() {
    $("#hide_button").click(function() {
        $("#table_order1").slideToggle("");
    });
});

$(function() {
    $("#hide_button2").click(function() {
        $("#address1").slideToggle("");
    });
});

$(function() {
    $("#hide_button3").click(function() {
        $("#foavoriteItems").slideToggle("");
    });
});

$(function() {
    $("#hide_button4").click(function() {
        $("#auction-order-history").slideToggle("");
    });
});
</script>
<script>
    $(function() {
        $("#hide_arrival_button").click(function() {
            $(".form_arrival").slideToggle();
        });
    });
</script>
<script>
$(document).ready(function () {
    function filterTable() {
        var keyword = $('#productInput').val().toLowerCase();
        var status = $('#statusSelect').val();

        $("#table_order1 tbody tr").each(function () {
            var row = $(this);
            var statusText = row.find(".status-cell").text().trim();
            var productText = row.find("td:last").text().toLowerCase(); // 商品名/個数欄（最終列）

            var matchKeyword = !keyword || productText.indexOf(keyword) !== -1;
            var matchStatus = !status || statusText.includes(status);

            row.toggle(matchKeyword && matchStatus);
        });
    }

    $('#productInput, #statusSelect').on('input change keyup', filterTable);
});
</script>




@endsection