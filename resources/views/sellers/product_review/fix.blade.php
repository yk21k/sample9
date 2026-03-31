@extends('layouts.seller')

@section('content')

<style>
    .fix-target {
        border:2px solid #e74c3c;
        background:#fdecea;
        padding:10px;
        border-radius:6px;
        margin-bottom:10px;
    }

    .fixed-done {
        border:2px solid #2ecc71;
        background:#eafaf1;
    }

    .fix-comment-box{
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border-left: 6px solid #e67e22;
        padding: 16px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .fix-comment-title{
        font-size: 16px;
        font-weight: 700;
        color: #d35400;
        margin-bottom: 8px;
    }

    .fix-comment-body{
        font-size: 18px;       /* ← ここで目立たせる */
        font-weight: 600;      /* ← 太字 */
        color: #2c3e50;
        line-height: 1.6;
    }
</style>

<div class="container">

<h2>✏ 修正依頼対応</h2>

{{-- 修正理由 --}}
@php
    $targetFields = ['cover_img', 'cover_img2', 'cover_img3', 'movie'];
    $isFix = count(array_intersect($targetFields, $fixFields)) > 0;
@endphp
<div>
     <label>修正依頼</label>
     <h2>{{$product->review_comment}}</h2>
</div>

<form method="POST" action="{{ route('voyager.products.update', $product->id) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

{{-- タイトル --}}
@php
    $isFix = in_array('name', $fixFields);
@endphp

<div class="{{ $isFix ? 'fix-target' : '' }}">
    <label>商品名</label>
    <input type="text" name="name"
        value="{{ old('name', $product->name) }}"
        class="form-control">
</div>
<input type="hidden" name="shipping_fee" value="{{ $product->shipping_fee }}">
<input type="hidden" name="stock" value="{{ $product->stock }}">
<input type="hidden" name="shop_id" value="{{ $product->shop_id }}">

{{-- 価格 --}}
@php
    $isFix = in_array('price', $fixFields);
@endphp

<div class="{{ $isFix ? 'fix-target' : '' }}">
    <label>価格</label>
    <input type="text" name="price"
        value="{{ old('price', $product->price) }}"
        class="form-control">
</div>

{{-- 説明 --}}
@php
    $isFix = in_array('description', $fixFields);
@endphp

<div class="{{ $isFix ? 'fix-target' : '' }}">
    <label>説明</label>
    <textarea name="description"
        class="form-control">{{ old('description', $product->description) }}</textarea>
</div>

<br>

{{-- 画像1 --}}

@php
    $isFix = in_array('cover_img', $fixFields);
@endphp

<div class="{{ $isFix ? 'fix-target' : '' }}">

    <label>商品画像</label>

    {{-- 現在画像 --}}
    <div style="margin-bottom:10px;">
        <img src="{{ asset('storage/'.$product->cover_img) }}"
             style="width:120px; border-radius:8px;">
    </div>

    {{-- 差し替え --}}
    <input type="file" name="cover_img" class="form-control">

    <small style="color:#e74c3c;">
        ※ 新しい画像をアップロードしてください
    </small>

</div>

{{-- 画像２ --}}

@php
    $isFix = in_array('cover_img2', $fixFields);
@endphp

<div class="{{ $isFix ? 'fix-target' : '' }}">

    <label>商品画像</label>

    {{-- 現在画像 --}}
    <div style="margin-bottom:10px;">
        <img src="{{ asset('storage/'.$product->cover_img2) }}"
             style="width:120px; border-radius:8px;">
    </div>

    {{-- 差し替え --}}
    <input type="file" name="cover_img2" class="form-control">

    <small style="color:#e74c3c;">
        ※ 新しい画像をアップロードしてください
    </small>

</div>

{{-- 画像３ --}}

@php
    $isFix = in_array('cover_img3', $fixFields);
@endphp

<div class="{{ $isFix ? 'fix-target' : '' }}">

    <label>商品画像</label>

    {{-- 現在画像 --}}
    <div style="margin-bottom:10px;">
        <img src="{{ asset('storage/'.$product->cover_img3) }}"
             style="width:120px; border-radius:8px;">
    </div>

    {{-- 差し替え --}}
    <input type="file" name="cover_img3" class="form-control">

    <small style="color:#e74c3c;">
        ※ 新しい画像をアップロードしてください
    </small>

</div>

{{-- 動画 --}}
@php
    $isFix = in_array('movie', $fixFields);
@endphp

<div class="{{ $isFix ? 'fix-target' : '' }}">

    <label>動画</label>

    {{-- 現在動画 --}}
    @if($product->movie)
        <video controls style="width:200px;">
            <source src="{{ asset('storage/'.$product->movie) }}">
        </video>
    @endif

    {{-- 差し替え --}}
    <input type="file" name="movie" class="form-control">

</div>

{{-- 修正依頼判定 --}}
@php
    // チェック対象の項目
    $targetFields = ['cover_img', 'cover_img2', 'cover_img3', 'movie'];

    // $fixFields は Product モデルの reviewQueue から取得される配列
    // 共通要素があれば true
    $isFix = count(array_intersect($targetFields, $fixFields)) > 0;
@endphp


<div>
    <label>修正依頼コメント</label>
    <div>{{ $product->review_comment ?? 'なし' }}</div>
</div>

{{-- 修正依頼ボタン --}}
<button id="saveBtn" class="btn btn-success" @if($isFix) disabled @endif>
    修正して保存
</button>



</form>

</div>

{{-- JavaScript でリアルタイム判定（任意、ユーザーがフォーム操作時に有効化） --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const saveBtn = document.getElementById('saveBtn');

        // フォーム内のチェック対象フィールド
        const fixFieldsInputs = ['cover_img', 'cover_img2', 'cover_img3', 'movie'].map(name => document.querySelector(`[name="${name}"]`));

        if(fixFieldsInputs.some(f => f)) { // フィールドが存在する場合
            fixFieldsInputs.forEach(input => {
                input.addEventListener('change', () => {
                    // どれかに値が入っていればボタン有効
                    let anyChanged = fixFieldsInputs.some(f => f && f.value);
                    saveBtn.disabled = !anyChanged;
                });
            });
        }
    });
</script>

@endsection