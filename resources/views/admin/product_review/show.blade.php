@extends('voyager::master')

@section('content')

@php
	$photoFields = ['cover_img', 'cover_img2', 'cover_img3'];
	$fieldLabels = [
	    'name' => '商品名',
	    'description' => '説明',
	    'price' => '価格',
	    'shipping_fee' => '配送料',
	    'stock' => '在庫',
	    'cover_img' => 'カバー画像',
	    'movie' => '動画',
	];
@endphp

<style>
	/* =====================================
	   全体背景
	===================================== */

	body{
	    background:#f4f6fb;
	    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto;
	}

	/* =====================================
	   Panel（カード化）
	===================================== */

	.panel{
	    border:none;
	    border-radius:12px;
	    background:white;
	    box-shadow:0 3px 12px rgba(0,0,0,0.05);
	    margin-bottom:20px;
	}

	.panel-heading{
	    background:transparent;
	    border-bottom:1px solid #f1f1f1;
	    font-weight:600;
	    font-size:15px;
	}

	.panel-body{
	    padding:20px;
	}

	/* =====================================
	   画像グリッド
	===================================== */

	.image-row{
	    display:grid;
	    grid-template-columns:repeat(auto-fill,120px);
	    gap:14px;
	}

	.image-card{
	    width:120px;
	}

	.thumb{
	    width:120px;
	    height:120px;
	    border-radius:12px;
	    overflow:hidden;
	    border:1px solid #eee;
	    background:#fafafa;
	    transition:0.2s;
	}

	.thumb:hover{
	    transform:translateY(-2px);
	    box-shadow:0 5px 15px rgba(0,0,0,0.08);
	}

	.thumb img{
	    width:100%;
	    height:100%;
	    object-fit:cover;
	}

	/* =====================================
	   ボタン
	===================================== */

	.btn{
	    border-radius:8px;
	    padding:10px 20px;
	    font-weight:600;
	}

	.btn-success{
	    background:#00b894;
	    border:none;
	}

	.btn-danger{
	    background:#ff4757;
	    border:none;
	}

	.btn-primary{
	    background:#3d7eff;
	    border:none;
	}

	.btn-info{
	    background:#2ed573;
	    border:none;
	}

	/* =====================================
	   Seller / Shop
	===================================== */

	.seller-panel{
	    padding:20px;
	    text-align:center;
	}

	.seller-avatar img{
	    width:80px;
	    height:80px;
	    border-radius:50%;
	    object-fit:cover;
	    border:3px solid #f1f1f1;
	}

	/* =====================================
	   違反表示
	===================================== */

	.violation-danger{
	    background:#ffecec;
	    border-left:4px solid #ff4757;
	    padding:12px;
	}

	.violation-warning{
	    background:#fff4e5;
	    border-left:4px solid #ffa502;
	    padding:12px;
	}

	/* =====================================
	   差分表示
	===================================== */

	.diff-before{
	    background:#fff1f1;
	    color:#d63031;
	    padding:6px;
	    border-radius:4px;
	}

	.diff-after{
	    background:#e9fff0;
	    color:#00b894;
	    padding:6px;
	    border-radius:4px;
	}

	/* =====================================
	   Review Log
	===================================== */

	.review-log{
	    border-left:3px solid #3d7eff;
	    padding-left:12px;
	    margin-bottom:10px;
	}

	/* =====================================
	   テーブル
	===================================== */

	.table{
	    border-radius:8px;
	    overflow:hidden;
	}

	.table th{
	    background:#f7f9fc;
	    font-weight:600;
	}

	/* =====================================
	   モーダル
	===================================== */

	.image-modal{
	    display:none;
	    position:fixed;
	    z-index:9999;
	    left:0;
	    top:0;
	    width:100%;
	    height:100%;
	    background:rgba(0,0,0,0.9);
	}

	.modal-content{
	    margin:auto;
	    display:block;
	    max-width:80%;
	    max-height:80%;
	    margin-top:5%;
	    border-radius:10px;
	}

	.close-btn{
	    position:absolute;
	    top:20px;
	    right:40px;
	    color:white;
	    font-size:40px;
	    cursor:pointer;
	}

	.nav{
	    position:absolute;
	    top:50%;
	    color:white;
	    font-size:50px;
	    cursor:pointer;
	    padding:20px;
	}

	.prev{
	    left:30px;
	}

	.next{
	    right:30px;
	}
</style>

<div class="page-content container-fluid">
	{{-- キーボード操作 --}}
	<div class="panel panel-default">
		<div class="panel-body text-muted">

			<strong>キーボード操作</strong>

			<ul>
				<li>A : 承認</li>
				<li>R : 却下</li>
				<li>N : 次の商品</li>
				<li>← → : 画像切替</li>
			</ul>

		</div>
	</div>
	{{-- ⚠ 違反履歴 --}}
	<div class="panel panel-default">
		<div class="panel panel-bordered">

			<div class="panel-heading">
				<h3 class="panel-title">⚠ 違反履歴</h3>
			</div>

			<div class="panel-body">

				@if($violations->count()==0)

					<p class="text-success">違反履歴なし</p>

				@else

				<table class="table table-striped">

					<thead>
						<tr>
							<th>タイプ</th>
							<th>内容</th>
							<th>レベル</th>
							<th>日時</th>
						</tr>
					</thead>

					<tbody>

						@foreach($violations as $v)

						<tr>

							<td>{{ $v->violation_type }}</td>

							<td>{{ $v->reason }}</td>

							<td>

								@if($v->severity==3)

									<span class="label label-danger">重大</span>

								@elseif($v->severity==2)

									<span class="label label-warning">注意</span>

								@else

									<span class="label label-info">軽微</span>

								@endif

							</td>

							<td>{{ $v->created_at }}</td>

						</tr>

						@endforeach

					</tbody>

				</table>

				@endif

			</div>
		</div>
	</div>	
	{{-- 📷 商品画像 --}}
	<div class="panel panel-default">
		<h4>📷 商品画像</h4>
		<div class="image-row">

			@foreach($photoFields as $index => $field)

				@php
					$photo = $product->{$field} ?? null;
				@endphp

				<div class="image-card">

					@if($photo)

					<div class="thumb">
						<img src="{{ asset('storage/'.$photo) }}" class="review-image" data-index="{{$index}}">
					</div>

					@else

					<div class="thumb d-flex align-items-center justify-content-center">
						<small>なし</small>
					</div>

					@endif

				</div>

			@endforeach

			@if($product_movies)
			    @foreach($product_movies as $movie)
			        <video controls width=""
			            src="{{ asset('storage/'.$movie['download_link']) }}" muted class="contents_width">
			        </video>
			    @endforeach
			@endif
		</div>
	</div>
	{{-- 審査履歴 --}}
	<div class="panel panel-default">
		<h3>審査履歴</h3>

		@foreach($logs as $log)

			<div class="review-log">

				<strong>{{ $log->created_at }}</strong>

				<br>

				{{ $log->action }}

				<br>

				<small class="text-muted">
					{{ $log->comment }}
				</small>

			</div>

		@endforeach

		@if(!empty($diff))

			<h4>変更箇所</h4>

			<table class="table table-bordered">
				<tr>
					<th>項目</th>
					<th>変更前</th>
					<th>変更後</th>
				</tr>
				@foreach($diff as $field => $change)
					<tr>
						<td>{{ $field }}</td>

						<td class="diff-before">
							{{ $change['before'] }}
						</td>

						<td class="diff-after">
							{{ $change['after'] }}
						</td>
					</tr>
					@if($field == 'cover_img')

						<td>
							<img src="/storage/{{ $change['before'] }}" width="120">
							<img src="{{ asset('storage/'.$change['before']) }}" width="120">
						</td>

						<td>
							<img src="/storage/{{ $change['after'] }}" width="120">
						</td>

					@endif
					@if($field == 'movie')

						<video src="/storage/{{ $change['after'] }}" width="300" controls></video>

					@endif
				@endforeach
			</table>

			@if(isset($diff['cover_img']))
				<div class="panel panel-default">
				    <h3>画像変更</h3>

				    @php
				        $beforeImgs = json_decode($diff['cover_img']['before'] ?? null, true);
				        $afterImgs  = json_decode($diff['cover_img']['after'] ?? null, true);
				    @endphp

				    <div style="display:flex; gap:20px;">

				        <div>
				            <p>変更前</p>
				            @if(is_array($beforeImgs))
				                @foreach($beforeImgs as $img)
				                    <img src="{{ asset('storage/'.$img) }}" width="120">
				                @endforeach
				            @else
				                なし
				            @endif
				        </div>

				        <div>
				            <p>変更後</p>
				            @if(is_array($afterImgs))
				                @foreach($afterImgs as $img)
				                    <img src="{{ asset('storage/'.$img) }}" width="120">
				                @endforeach
				            @else
				                なし
				            @endif
				        </div>

				    </div>
				</div>
			@endif

			@if(isset($diff['movie']))
				<div class="panel panel-default">
				    <h3>動画変更</h3>

				    @php
				        $beforeMovies = json_decode($diff['movie']['before'] ?? null, true);
				        $afterMovies  = json_decode($diff['movie']['after'] ?? null, true);
				    @endphp

				    <div style="display:flex; gap:20px;">

				        <div>
				            <p>変更前</p>
				            @if(is_array($beforeMovies))
				                @foreach($beforeMovies as $movie)
				                    <video width="200" controls>
				                        <source src="{{ asset('storage/'.$movie['download_link']) }}">
				                    </video>
				                @endforeach
				            @else
				                なし
				            @endif
				        </div>

				        <div>
				            <p>変更後</p>
				            @if(is_array($afterMovies))
				                @foreach($afterMovies as $movie)
				                    <video width="200" controls>
				                        <source src="{{ asset('storage/'.$movie['download_link']) }}">
				                    </video>
				                @endforeach
				            @else
				                なし
				            @endif
				        </div>

				    </div>
				</div>
			@endif

		@else
		   変更履歴はありません。新規登録商品です	
		@endif
	</div>
	{{-- 審査履歴 --}}
	<div class="panel panel-default">
	    <h3>審査履歴</h3>

	    @foreach($logs as $log)
	        <div class="review-log">
	            <strong>{{ $log->created_at }}</strong><br>
	            {{ $log->action }}<br>
	            <small class="text-muted">{{ $log->comment }}</small>
	        </div>
	    @endforeach

		@if(!empty($diff))

		<table class="table table-bordered">
		    <tr>
		        <th>項目</th>
		        <th>変更前</th>
		        <th>変更後</th>
		    </tr>

		    @foreach($diff as $field => $change)
		    <tr>
		        <td>{{ $field }}</td>

		        {{-- 変更前 --}}
		        <td>
		            @php $before = $change['before'] ?? $product->getOriginal($field); @endphp
		            @if($before)
		                {{-- 画像 --}}
		                @if(Str::endsWith($before, ['.jpg','.png','.jpeg']))
		                    <img src="{{ asset('storage/versions/'.$before) }}" width="120">

		                {{-- 動画 --}}
		                @elseif(Str::endsWith($before, ['.mp4']))
		                    <video width="200" controls>
		                        <source src="{{ asset('storage/versions/'.$before) }}">
		                    </video>

		                {{-- 文字 --}}
		                @else
		                    {{ $before }}
		                @endif
		            @else
		                -
		            @endif
		        </td>


		        {{-- 変更後 --}}
		        <td>
		            @php $after = $change['after'] ?? null; @endphp

		            @if($after)
		                {{-- 画像 --}}
		                @if(Str::endsWith($after, ['.jpg','.png','.jpeg']))
		                    <img src="{{ asset('storage/'.$after) }}" width="120">

		                {{-- 動画 --}}
		                @elseif(Str::endsWith($after, ['.mp4']))
		                    <video width="200" controls>
		                        <source src="{{ asset('storage/'.$after) }}">
		                    </video>

		                {{-- 文字 --}}
		                @else
		                    {{ $after }}
		                @endif
		            @else
		                -
		            @endif
		        </td>

		    </tr>
		    @endforeach

		</table>

		@else
		<p>変更なし</p>
		@endif
	</div>	

	{{-- 承認 却下 次の商品 --}}
	<div class="panel panel-default">
		<div class="text-center" style="margin-top:30px">

			<form method="POST" action="{{ route('product.approve',$product->id) }}" style="display:inline">
			@csrf
				<button id="approveBtn" class="btn btn-success btn-lg">
				承認 (A)
				</button>
			</form>

			<form method="POST" action="{{ route('product.reject',$product->id) }}" style="display:inline">
			@csrf
				<button id="rejectBtn" class="btn btn-danger btn-lg">
					却下 (R)
				</button>
			</form>

			<a href="{{ route('admin.product-review.next') }}"　id="nextBtn" class="btn btn-primary btn-lg">
				次の商品 (N) →
			</a>

		</div>	
	</div>
	{{-- 情報 マニュアル --}}
	<div class="row">
		
	    {{-- 商品情報 --}}
		<div class="col-6 col-md-4">

			<div class="panel panel-bordered seller-panel">

				<div class="panel-heading">
					商品情報
					
				</div>
			
				<div class="panel-body">

					<h3>{{ $product->name }}</h3>

					<p class="text-muted">
						価格 : <strong>{{ number_format($product->price) }}円</strong>
					</p>

					<p style="white-space: pre-line">
						{{ $product->description }}
					</p>

				</div>
			</div>	
		</div>

	    {{-- 出品者情報 --}}
	    <div class="col-6 col-md-4">

	        <div class="panel panel-bordered seller-panel">

	            <div class="panel-heading">
	                出品者情報
	            </div>

	            <div class="panel-body">

	                <div class="seller-avatar">

	                    @if($seller->avatar)
	                        <img src="{{ asset('storage/'.$seller->avatar) }}">
	                    @else
	                        <img src="/images/default-avatar.png">
	                    @endif

	                </div>

	                <h4>{{ $seller->name }}</h4>

	                <p>
	                    SHOP承認可否 :
	                    @if($seller->is_active)
	                        <span class="label label-success">SHOP承認</span>
	                    @else
	                        <span class="label label-danger">SHOP未承認のため審査可でも非表示</span>
	                    @endif
	                </p>

	                <p>
	                    登録日 :
	                    {{ $seller->created_at->format('Y/m/d') }}
	                </p>

	                <p>
	                    販売数 :
	                    <strong>{{ $salesCount }}</strong>
	                </p>

					<p>
					評価 :
						<span style="color:#f5b301;font-size:18px">
							★ {{ $rating }}
						</span>
					</p>

	                <a
	                href="/admin/users/{{ $seller->user_id }}"
	                class="btn btn-primary btn-sm">

	                出品者詳細

	                </a>

	            </div>

	        </div>

	    </div>
	    {{-- Shop情報 --}}
		<div class="col-6 col-md-4">

			<div class="panel panel-bordered seller-panel">

				<div class="panel-heading">
					Shop情報
				</div>

				<div class="panel-body">

					@if($shop)

					<h4>{{ $shop->name }}</h4>

					<p>
						ショップID : {{ $shop->id }}
					</p>

					<p>
						開設日 :
						{{ $shop->created_at->format('Y/m/d') }}
					</p>

					<p>
						商品数 :
						<strong>{{ $shop->products()->count() }}</strong>
					</p>

					<p>
						ショップ評価 :
						⭐ {{ number_format($shop->rating ?? 0,1) }}
					</p>

					<a href="/admin/shops/{{ $shop->id }}" class="btn btn-info btn-sm">

						Shop詳細

					</a>

					@else

						<p class="text-muted">Shopなし</p>

					@endif
				</div>	
		</div>

		{{-- マニュアル集(規約、詳細な事例) --}}
		<div class="col-6 col-md-4">
			<div class="panel panel-bordered seller-panel">
				<div class="panel-heading">
					リンクは準備中
				</div>
				<div class="panel-body">
					 <p>マニュアル集(規約、詳細な事例) ないとわからない	</p>
					 <p>-</p>
					 <p>-</p>
					 <p>-</p>
					 <p>-</p>
				</div>	
			</div>
		</div>	
	</div>		
</div>

<!-- 画像モーダル -->

<div id="imageModal" class="image-modal">

	<span class="close-btn">&times;</span>

	<img class="modal-content" id="modalImage">

	<div class="nav prev">&#10094;</div>
	<div class="nav next">&#10095;</div>

</div>

<script>

	let images = [];
	let currentIndex = 0;

	document.querySelectorAll('.review-image').forEach(function(img){

	images.push(img.src);

	img.addEventListener('click',function(){

	currentIndex = parseInt(this.dataset.index);

	openModal();

	});

	});

	function openModal(){

	document.getElementById('imageModal').style.display='block';

	showImage();

	}

	function showImage(){

	document.getElementById('modalImage').src = images[currentIndex];

	}

	function nextImage(){

	currentIndex++;

	if(currentIndex >= images.length){
	currentIndex = 0;
	}

	showImage();

	}

	function prevImage(){

	currentIndex--;

	if(currentIndex < 0){
	currentIndex = images.length-1;
	}

	showImage();

	}

	document.querySelector('.next').onclick = nextImage;
	document.querySelector('.prev').onclick = prevImage;

	document.querySelector('.close-btn').onclick = function(){
	document.getElementById('imageModal').style.display='none';
	};

	document.addEventListener('keydown',function(e){

	if(e.key === "ArrowRight"){
	nextImage();
	}

	if(e.key === "ArrowLeft"){
	prevImage();
	}

	if(e.key === "Escape"){
	document.getElementById('imageModal').style.display='none';
	}

	});

</script>
<script>

	document.addEventListener('keydown', function(e){

		// 入力フォーム中は無効
		if(document.activeElement.tagName === "INPUT" ||
		   document.activeElement.tagName === "TEXTAREA"){
		   return;
		}

		switch(e.key.toLowerCase()){

		    // 承認
		    case 'a':
		        document.getElementById('approveBtn').click();
		    break;

		    // 却下
		    case 'r':
		        document.getElementById('rejectBtn').click();
		    break;

		    // 次の商品
		    case 'n':
		        document.getElementById('nextBtn').click();
		    break;

		}

	});

</script>
@stop