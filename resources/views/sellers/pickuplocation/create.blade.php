@extends('layouts.seller')
    

@section('content')
<style>
    .youtube-preview-box {
        background-color: #f8f9fa; /* 薄いグレーで囲い */
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 8px;
        margin-top: 10px;
    }

    .preview-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: #333;
        margin-bottom: 6px;
    }

    .youtube-error-inline {
        color: #b00020;           /* 濃い赤 */
        font-weight: bold;
        margin-left: 4px;
        font-size: 0.9rem;
    }

</style>

{{-- ✅ フラッシュメッセージの表示 --}}
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container">
    <h2 class="mb-4">受取店舗の登録</h2>

    <form action="{{ route('seller.pickup.locations.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">店舗名</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">住所</label>
            <input type="text" class="form-control" id="address" name="address" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">電話番号</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>

        <div class="mb-3">
            <label for="recorded_at" class="form-label">撮影日</label>
            <input type="date" class="form-control" id="recorded_at" name="recorded_at" required>
        </div>

        <div class="mb-3">
            <label for="youtube_url" class="form-label">YouTube URL</label>
            <input type="text" 
                   class="form-control @error('youtube_url') is-invalid @enderror" 
                   id="youtube_url" 
                   name="youtube_url" 
                   placeholder="https://www.youtube.com/watch?v=xxxx"
                   value="{{ old('youtube_url', $pickupLocation->youtube_url ?? '') }}" required>
            
            {{-- 埋め込みプレビュー（入力があれば常に表示） --}}
            <div id="youtube-preview" class="youtube-preview-box" 
                 style="display: {{ old('youtube_url', $pickupLocation->youtube_url ?? '') ? 'block' : 'none' }};">
                <p class="preview-title">
                    プレビュー：
                    @error('youtube_url')
                        <span class="youtube-error-inline">{{ $message }}</span>
                    @enderror
                </p>

                <div class="ratio ratio-16x9">
                    <iframe id="youtube-iframe" src="{{ old('youtube_url', $pickupLocation->youtube_url ?? '') }}" allowfullscreen></iframe>
                </div>
            </div>

            {{-- バリデーションメッセージ --}}
            @error('youtube_url')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>


        <button type="submit" class="btn btn-primary">登録する</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlInput = document.getElementById('youtube_url');
        const preview = document.getElementById('youtube-preview');
        const iframe = document.getElementById('youtube-iframe');

        urlInput.addEventListener('input', function() {
            const url = urlInput.value.trim();

            // 正規表現で YouTube ID 抽出
            const match = url.match(/(?:v=|be\/)([a-zA-Z0-9_-]{11})/);
            if (match) {
                const videoId = match[1];
                iframe.src = `https://www.youtube.com/embed/${videoId}`;
                preview.style.display = 'block';
            } else {
                iframe.src = '';
                preview.style.display = 'none';
            }
        });
    });
</script>


@endsection


