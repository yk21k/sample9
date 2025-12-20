@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">受取店舗情報</h2>

    @foreach ($cart as $item)
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h4 class="card-title">{{ $item['product_name'] }}</h4>

                <p class="mb-1"><strong>店舗名：</strong>{{ $item['shop']['name'] }}</p>
                <p>住所：{{ $item['pick']->address ?? '未登録' }}</p>

                {{-- 動画（存在する場合のみ表示） --}}
				@if(!empty($item['pick']->embed_youtube_url))
				<div class="ratio ratio-16x9 mb-3">
				    <iframe src="{{ $item['pick']->embed_youtube_url }}"
				            title="店舗紹介動画"
				            frameborder="0"
				            allowfullscreen>
				    </iframe>
				</div>
				@else
                    <p class="text-muted">動画は登録されていません</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
