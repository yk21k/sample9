@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('front/css/custom102.css') }}">

<div class="shop-profile container my-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <h1 class="shop-title text-center mb-4">
                🏬 当サイトでのShop情報です
            </h1>

            <div class="shop-info">

                <div class="info-row">
                    <h3 class="info-label">ショップ名</h3>
                    <p class="info-value">{{ $parts->name }}</p>
                </div>

                <div class="info-row">
                    <h3 class="info-label">ショップ説明</h3>
                    <p class="info-value">{{ $parts->description }}</p>
                </div>

                <div class="info-row">
                    <h3 class="info-label">お問い合わせ方法</h3>
                    <p class="info-value">サイト内のお問い合わせフォームよりご連絡ください</p>
                </div>

                <div class="info-row text-center mt-4">
                    <a href="{{ route('customer.inquiry', ['shopId'=>$parts->id]) }}">
                        <h5>Shop へお問い合わせする</h5>
                    </a>
                    
                </div>

                <div class="info-row mt-3">
                    <h3 class="info-label">ショップ担当者</h3>
                    <p class="info-value">{{ $parts->manager }}</p>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

