@extends('voyager::master')

@section('content')
<style>
	button:disabled {
	    opacity: 0.5;
	    cursor: not-allowed;
	}
</style>
@if(session('error'))

	<div class="alert alert-danger">

		{{ session('error') }}

	</div>

@endif

<div class="page-content container-fluid">

	<h2>商品審査キュー</h2>

	@foreach($queues as $queue)
		@if($queue->status === 'reviewing')
		    <div style="color:#f39c12; font-size:12px;">
		        👀 審査中：
		        {{ $queue->reviewer->name ?? '不明' }}
		    </div>
		@endif

		<div class="panel panel-bordered review-card" id="queue-{{ $queue->id }}">
			@php
		        $product = $queue->product;
		        $isLocked = $queue->status === 'reviewing' && $queue->reviewer_id !== auth()->id();
		    @endphp
			<div class="panel-body">

				<h3>{{ $queue->product->name }}</h3>
				<h4>{{ $queue->product->shop->name }}</h4>

				<p>価格 : {{ number_format($queue->product->price) }}円</p>

				@foreach(['cover_img','cover_img2','cover_img3'] as $field)
				    <img 
				        src="{{ $queue->product->$field ? mediaUrl($queue->product->$field) : asset('images/no_image.jpg') }}"
				        width="200"
				    >
				@endforeach

				<a href="{{ route('product.review.show',$queue->product_id)
				}}" class="btn btn-primary">
					審査する
				</a>

				<button 
				    type="button"
				    class="btn btn-success btn-sm quick-approve"
				    data-queue="{{ $queue->id }}"
				    data-url="{{ route('product.approve',$product->id) }}"
				    {{ $isLocked ? 'disabled' : '' }}
				>
				    ✔ 承認
				</button>

				<button 
				    type="button"
				    class="btn btn-danger btn-sm quick-reject"
				    data-queue="{{ $queue->id }}"
				    data-url="{{ route('product.reject',$product->id) }}"
				    {{ $isLocked ? 'disabled' : '' }}
				>
				    ✖ 却下
				</button>

			</div>

		</div>

	@endforeach

</div>

@stop

<script>
	function sendReview(btn, url, queueId, color){

	    // ローディング
	    btn.dataset.original = btn.innerHTML;
	    btn.disabled = true;
	    btn.innerHTML = '⏳ 処理中...';

	    fetch(url, {
	        method: 'POST',
	        headers: {
	            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
	            'Accept': 'application/json'
	        }
	    })
	    .then(res => {
	        if(!res.ok) throw new Error();
	        return res.json();
	    })
	    .then(() => {

	        const card = document.getElementById('queue-'+queueId);

	        if(card){

	            // フラッシュ
	            card.style.background = color;
	            card.style.transition = '0.3s';

	            setTimeout(()=> card.style.opacity = 0, 200);
	            setTimeout(()=> {
	            	card.remove();
	            	// 🔥 次を補充
    				loadNextPage();
	            }, 500);
	        }

	    })
	    .catch(() => {

	        btn.disabled = false;
	        btn.innerHTML = btn.dataset.original;

	        alert('エラーが発生しました');

	    });
	}

	document.addEventListener('DOMContentLoaded', function(){

	    document.querySelectorAll('.quick-approve').forEach(btn => {
	        btn.addEventListener('click', function(){

	            if(!confirm('承認しますか？')) return;

	            sendReview(this, this.dataset.url, this.dataset.queue, '#d4edda');
	        });
	    });

	    document.querySelectorAll('.quick-reject').forEach(btn => {
	        btn.addEventListener('click', function(){

	            if(!confirm('却下しますか？')) return;

	            sendReview(this, this.dataset.url, this.dataset.queue, '#f8d7da');
	        });
	    });

	});

	function loadNextPage(){

	    if(window.loadingNext) return;

	    window.loadingNext = true;

	    fetch('?page=' + window.nextPage)
	    .then(res => res.text())
	    .then(html => {

	        const parser = new DOMParser();
	        const doc = parser.parseFromString(html,'text/html');

	        const newItems = doc.querySelectorAll('.review-card');

	        if(newItems.length === 0){
	            return;
	        }

	        newItems.forEach(el => {
	            document.querySelector('.container-fluid').appendChild(el);
	        });

	        window.nextPage++;
	        window.loadingNext = false;

	    });
	}

	// 初期値
	window.nextPage = 2;
	window.loadingNext = false;
</script>