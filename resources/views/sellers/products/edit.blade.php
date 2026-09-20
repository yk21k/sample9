@extends('layouts.seller')

@section('content')

<div class="container">

    <h2 class="mb-4">
        商品編集seller/products　// staff manager用？　draft編集に一本化の方がいいかも今後未定
    </h2>

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form
        method="POST"
        action="{{ route('seller.products.update', $product) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="card">

            <div class="card-body">

                {{-- 商品名 --}}
                <div class="mb-3">

                    <label class="form-label">
                        商品名
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $product->name) }}"
                        class="form-control"
                        required
                    >

                </div>

                {{-- 価格 --}}
                <div class="mb-3">

                    <label class="form-label">
                        価格
                    </label>

                    <input
                        type="number"
                        name="price"
                        value="{{ old('price', $product->price) }}"
                        class="form-control"
                        required
                    >

                </div>

                {{-- 配送料 --}}
                <div class="mb-3">

                    <label class="form-label">
                        配送料
                    </label>

                    <input
                        type="number"
                        name="shipping_fee"
                        value="{{ old('shipping_fee', $product->shipping_fee) }}"
                        class="form-control"
                    >

                </div>

                {{-- 説明 --}}
                <div class="mb-3">

                    <label class="form-label">
                        商品説明
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="6"
                    >{{ old('description', $product->description) }}</textarea>

                </div>
                <div class="mb-3">
                    <label class="form-label">
                        在庫
                    </label>
                    <input
                        type="number"
                        name="stock"
                        value="{{ old('stock', $product->stock) }}"
                        class="form-control"
                        required
                    >
                </div>

                {{-- 現在画像 --}}
                @if($product->cover_img)

                    <div class="mb-3">

                        <img
                            src="{{ asset('storage/'.$product->cover_img) }}"
                            style="width:120px;border-radius:12px;"
                        >

                    </div>

                @endif

                {{-- 新画像 --}}
                <div class="mb-3">

                    <label class="form-label">
                        商品画像変更
                    </label>

                    <input
                        type="file"
                        name="cover_img"
                        class="form-control"
                    >

                </div>

                {{-- 現在画像 --}}
                @if($product->cover_img2)

                    <div class="mb-3">

                        <img
                            src="{{ asset('storage/'.$product->cover_img2) }}"
                            style="width:120px;border-radius:12px;"
                        >

                    </div>

                @endif

                {{-- 新画像 --}}
                <div class="mb-3">

                    <label class="form-label">
                        商品画像変更2
                    </label>

                    <input
                        type="file"
                        name="cover_img2"
                        class="form-control"
                    >

                </div>

                {{-- 現在画像 --}}
                @if($product->cover_img3)

                    <div class="mb-3">

                        <img
                            src="{{ asset('storage/'.$product->cover_img3) }}"
                            style="width:120px;border-radius:12px;"
                        >

                    </div>

                @endif

                {{-- 新画像 --}}
                <div class="mb-3">

                    <label class="form-label">
                        商品画像変更3
                    </label>

                    <input
                        type="file"
                        name="cover_img3"
                        class="form-control"
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

        <div class="mt-3 d-flex gap-2">

            <button
                type="submit"
                class="btn btn-primary"
            >
                更新
            </button>

            <a
                href="{{ route('seller.products.index') }}"
                class="btn btn-secondary"
            >
                戻る
            </a>

        </div>

    </form>

    {{-- 商品YouTube動画 --}}
    <div class="mt-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <h5 class="fw-bold mb-2">
                    商品YouTube動画
                </h5>

                <p class="text-muted mb-3">
                    AI審査・動画加工・Preview・管理者審査を経て
                    YouTubeへアップロードする動画を登録します。
                </p>

                <a
                    href="{{ route(
                        'seller.products.youtube-video.create',
                        ['product' => $product->id]
                    ) }}"
                    class="btn btn-outline-primary"
                >
                    YouTube動画を登録
                </a>

            </div>

        </div>

    </div>

</div>

<!-- Price Modal -->
<div
    class="modal fade"
    id="priceModal"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    商品価格確認
                </h5>

            </div>

            <div class="modal-body">

                <div id="modalMessage"></div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-dismiss="modal"
                >
                    OK
                </button>

            </div>

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

                const modal =
                    new bootstrap.Modal(
                        document.getElementById(
                            'priceModal'
                        )
                    );

                modal.show();
            }

            /*
            |--------------------------------------------------------------------------
            | 即時モーダル
            |--------------------------------------------------------------------------
            */
            priceInput?.addEventListener(
                'blur',
                showModal
            );

            shippingInput?.addEventListener(
                'blur',
                showModal
            );
        }
    );

</script>

@endsection