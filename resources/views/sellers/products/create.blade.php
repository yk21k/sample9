@extends('layouts.seller')

@section('content')

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                商品追加seller/products
            </h2>

            <p class="text-muted mb-0">
                新しい商品を登録します
            </p>

        </div>

        <a
            href="{{ route('seller.products.index') }}"
            class="btn btn-outline-secondary"
        >
            一覧へ戻る
        </a>

    </div>

    {{-- エラー表示 --}}
    @if ($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form
        method="POST"
        action="{{ route('seller.products.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <div class="row">

                    {{-- 商品名 --}}
                    <div class="col-md-12 mb-4">

                        <label class="form-label fw-bold">
                            商品名
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            required
                        >

                    </div>

                    {{-- 価格 --}}
                    <div class="col-md-6 mb-4">

                        <label class="form-label fw-bold">
                            価格
                        </label>

                        <input
                            type="number"
                            name="price"
                            class="form-control"
                            value="{{ old('price') }}"
                            min="0"
                            required
                        >

                    </div>

                    {{-- 配送料 --}}
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            配送料
                        </label>

                        <input
                            type="number"
                            name="shipping_fee"
                            class="form-control"
                        >

                    </div>

                    {{-- 在庫 --}}
                    <div class="col-md-6 mb-4">

                        <label class="form-label fw-bold">
                            在庫数
                        </label>
                        <!-- 無在庫販売は禁止でどこかで止める -->
                        <input
                            type="number"
                            name="stock"
                            class="form-control"
                            value="{{ old('stock', 0) }}"
                            min="1"
                            required
                        >

                    </div>

                    {{-- 商品説明 --}}
                    <div class="col-md-12 mb-4">

                        <label class="form-label fw-bold">
                            商品説明
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="6"
                        >{{ old('description') }}</textarea>

                    </div>

                    {{-- 商品画像 --}}
                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-bold">
                            商品画像
                        </label>

                        <input
                            type="file"
                            name="cover_img"
                            class="form-control"
                            accept="image/*"
                        >

                        <small class="text-muted">
                            JPG / PNG 推奨
                        </small>
                    </div>
                    
                    {{-- 画像2 --}}
                    <div class="col-md-6 mb-4">

                        <label class="form-label fw-bold">
                            商品画像2
                        </label>

                        <input
                            type="file"
                            name="cover_img2"
                            class="form-control"
                            accept="image/*"
                        >

                    </div>

                    {{-- 画像3 --}}
                    <div class="col-md-6 mb-4">

                        <label class="form-label fw-bold">
                            商品画像3
                        </label>

                        <input
                            type="file"
                            name="cover_img3"
                            class="form-control"
                            accept="image/*"
                        >

                    </div>

                    {{-- Movie URL --}}
                    <div class="col-md-12 mb-4">

                        <label class="form-label fw-bold">
                            Movie URL
                        </label>

                        <input
                            type="text"
                            name="movie"
                            class="form-control"
                            value="{{ old('movie') }}"
                            placeholder="https://youtube.com/..."
                        >

                        <small class="text-muted">
                            YouTube URL 等
                        </small>

                    </div>

                    {{-- Movie Upload --}}
                    <div class="col-md-12 mb-4">

                        <label class="form-label fw-bold">
                            Movie Upload
                        </label>

                        <input
                            type="file"
                            name="movie_file"
                            class="form-control"
                            accept="video/*"
                        >

                        <small class="text-muted">
                            mp4 / mov 推奨
                        </small>

                    </div>


                    

                </div>

            </div>

        </div>

        <div class="mt-4 d-flex gap-2">

            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                保存
            </button>

            <a
                href="{{ route('seller.products.index') }}"
                class="btn btn-light border"
            >
                キャンセル
            </a>

        </div>

    </form>

</div>

<!-- Price Modal -->
<div
    id="priceModal"
    style="
        display:none;
        position:fixed;
        z-index:99999;
        left:0;
        top:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,0.5);
    "
>

    <div
        style="
            background:#fff;
            width:500px;
            max-width:90%;
            margin:100px auto;
            padding:30px;
            border-radius:12px;
        "
    >

        <h4 class="mb-3">
            商品価格確認
        </h4>

        <div id="modalMessage"></div>

        <div class="mt-4 text-end">

            <button
                type="button"
                class="btn btn-primary"
                onclick="closePriceModal()"
            >
                OK
            </button>

        </div>

    </div>

</div>

@php

    $shop = auth()->user()->currentShop();

    $isTaxable =
        !empty($shop?->invoice_number);

    $shopName =
        $shop?->name;

@endphp

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const priceInput =
            document.querySelector(
                'input[name="price"]'
            );

        const shippingInput =
            document.querySelector(
                'input[name="shipping_fee"]'
            );

        const taxRate =
            {{ App\Models\TaxRate::current()?->rate ?? 0 }};

        const isTaxable =
            @json($isTaxable);

        const shopName =
            @json($shopName);

        let lastPrice = null;
        let lastShipping = null;    

        function showModal()
        {
            const price =
                parseFloat(priceInput?.value || 0);

            const shipping =
                parseFloat(shippingInput?.value || 0);

            if (!price) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 配送料25%
            |--------------------------------------------------------------------------
            */
            const maxShipping =
                Math.floor(price * 0.25);

            if (shipping > maxShipping) {

                alert(
                    `配送料は価格の25%（最大 ${maxShipping} 円）までです`
                );

                shippingInput.value = '';

                shippingInput.focus();

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 表示価格計算
            |--------------------------------------------------------------------------
            */
            let displayPrice = price;

            let displayShipping = shipping;

            if (isTaxable) {

                displayPrice =
                    Math.floor(
                        price + (price * taxRate)
                    );

                displayShipping =
                    Math.floor(
                        shipping + (shipping * taxRate)
                    );
            }

            const total =
                displayPrice + displayShipping;

            /*
            |--------------------------------------------------------------------------
            | 同値再表示防止
            |--------------------------------------------------------------------------
            */
            if (
                lastPrice === price &&
                lastShipping === shipping
            ) {
                return;
            }

            lastPrice = price;
            lastShipping = shipping;


            /*
            |--------------------------------------------------------------------------
            | メッセージ
            |--------------------------------------------------------------------------
            */
            let message = '';

            if (isTaxable) {

                message += `
                    <div style="color:tomato;">
                        ${shopName} は課税事業者です。<br><br>

                        商品価格：
                        ${displayPrice} 円<br>

                        配送料：
                        ${displayShipping} 円<br><br>

                        合計表示価格：
                        ${total} 円
                    </div>
                `;

            } else {

                message += `
                    <div style="color:green;">
                        ${shopName} は免税事業者です。<br><br>

                        商品価格：
                        ${displayPrice} 円<br>

                        配送料：
                        ${displayShipping} 円<br><br>

                        合計表示価格：
                        ${total} 円
                    </div>
                `;
            }

            document.getElementById(
                'modalMessage'
            ).innerHTML = message;

            document.getElementById(
                'priceModal'
            ).style.display = 'block';

            modal.show();
        }

        /*
        |--------------------------------------------------------------------------
        | 即時モーダル
        |--------------------------------------------------------------------------
        */
        priceInput?.addEventListener(
            'change',
            showModal
        );

        shippingInput?.addEventListener(
            'change',
            showModal
        );
    }
);

function closePriceModal()
{
    document.getElementById(
        'priceModal'
    ).style.display = 'none';
}

</script>

@endsection