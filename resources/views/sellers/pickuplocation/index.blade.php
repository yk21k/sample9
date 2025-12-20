@extends('layouts.seller')

@section('content')
<div class="container">
    <h2 class="mb-4">登録済み受取店舗</h2>

    @forelse($locations as $loc)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    {{ $loc->name }}
                    @if($loc->shop)
                        <small class="text-muted">（{{ $loc->shop->name }}）</small>
                    @endif
                </h5>
                <p class="mb-1"><strong>住所:</strong> {{ $loc->address }}</p>
                <p class="mb-1"><strong>電話:</strong> {{ $loc->phone ?? '未登録' }}</p>
                <p class="mb-1"><strong>撮影日:</strong> {{ $loc->recorded_at ?? '未入力' }}</p>
                <p class="mb-1"><strong>ステータス:</strong>
                    @if($loc->status == 1)
                        <span class="badge bg-success">承認済</span>
                    @elseif($loc->status == 2)
                        <span class="badge bg-danger">却下</span>
                    @else
                        <span class="badge bg-secondary">承認待ち</span>
                    @endif
                </p>

                @if($loc->youtube_url)
                    <div class="ratio ratio-16x9 mt-3">
                        <iframe src="https://www.youtube.com/embed/{{ \Illuminate\Support\Str::after($loc->youtube_url, 'v=') }}" 
                                title="YouTube video"
                                allowfullscreen></iframe>
                    </div>
                @else
                    <p class="text-muted">動画なし</p>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info">登録された受取店舗はありません。</div>
    @endforelse
</div>
@endsection
