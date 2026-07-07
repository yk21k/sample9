@extends('layouts.seller')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            商品申請詳細
        </h2>

    </div>

    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif

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
        action="{{ route('seller.product_drafts.update', $draft) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">

            <div class="card-body">

                {{-- status --}}
                <div class="mb-4">

                    @if($draft->status === 'owner_pending')

                        <span class="badge bg-warning fs-6">
                            owner承認待ち
                        </span>


                    @elseif($draft->status === 'approved')

                        <span class="badge bg-success fs-6">
                            運営審査中
                        </span>

                    @elseif($draft->status === 'rejected')

                        <span class="badge bg-danger fs-6">
                            差し戻し
                        </span>

                    @else

                        <span class="badge bg-secondary fs-6">
                            {{ $draft->status }}
                        </span>

                    @endif

                </div>

                <div class="row">

                    {{-- 左側 --}}
                    <div class="col-md-8">

                        {{-- 商品名 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                商品名

                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $draft->name) }}"
                                class="form-control"
                                required
                            >

                        </div>

                        {{-- 商品説明 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                商品説明

                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="8"
                            >{{ old('description', $draft->description) }}</textarea>

                        </div>

                        {{-- 差し戻しコメント --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold text-danger">

                                差し戻しコメント

                            </label>

                            <textarea
                                name="owner_comment"
                                class="form-control"
                                rows="5"
                                placeholder="修正内容を入力"
                            >{{ old('owner_comment', $draft->owner_comment) }}</textarea>

                        </div>

                    </div>

                    {{-- 右側 --}}
                    <div class="col-md-4">

                        {{-- 画像 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                商品画像

                            </label>

                            @if($draft->cover_img)

                                <div class="mb-3">

                                    
                                    <img
                                        src="{{ Storage::disk('s3')->url($draft->cover_img) }}"
                                        style="width:120px;border-radius:12px;"
                                    >

                                </div>

                            @endif

                            <input
                                type="file"
                                name="cover_img"
                                class="form-control"
                            >

                        </div>

                        {{-- 画像2 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                商品画像2

                            </label>

                            @if($draft->cover_img2)

                                <div class="mb-2">

                                    
                                    <img
                                        src="{{ Storage::disk('s3')->url($draft->cover_img2) }}"
                                        style="width:120px;border-radius:12px;"
                                    >

                                </div>

                            @endif

                            <input
                                type="file"
                                name="cover_img2"
                                class="form-control"
                            >

                        </div>

                        {{-- 画像3 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                商品画像3

                            </label>

                            @if($draft->cover_img3)

                                <div class="mb-2">

                                    
                                    <img
                                        src="{{ Storage::disk('s3')->url($draft->cover_img3) }}"
                                        style="width:120px;border-radius:12px;"
                                    >

                                </div>

                            @endif

                            <input
                                type="file"
                                name="cover_img3"
                                class="form-control"
                            >

                        </div>

                        @php
                            $product = $draft->product_id
                                ? \App\Models\Product::find($draft->product_id)
                                : null;
                        @endphp



                        {{-- Movie --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                Movie URL

                            </label>

                            <input
                                type="text"
                                name="movie"
                                value="{{ old('movie', $draft->movie) }}"
                                class="form-control"
                                placeholder="https://youtube.com/..."
                            >

                        </div>

                        {{-- Movie Upload --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                Movie Upload

                            </label>
                            @if($product && $product->movie_file)

                                <div class="mb-3">

                                    <label class="form-label">
                                        現在公開中の動画
                                    </label>

                                    <video
                                        controls
                                        style="
                                            width:100%;
                                            border-radius:12px;
                                        "
                                    >
                                        <source
                                            src="{{ asset('storage/'.$product->movie_file) }}">
                                    </video>

                                </div>

                            @endif

                            @if($draft->movie_file)

                                <div class="mb-3">

                                    <label class="form-label">
                                        申請中の動画
                                    </label>

                                    <video
                                        controls
                                        style="
                                            width:100%;
                                            border-radius:12px;
                                        "
                                    >
                                        <source
                                            src="{{ asset('storage/'.$draft->movie_file) }}">
                                    </video>

                                </div>

                            @endif

                            <input
                                type="file"
                                name="movie_file"
                                class="form-control"
                                accept="video/*"
                            >

                        </div>

                        {{-- 価格 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                価格

                            </label>

                            <input
                                type="number"
                                name="price"
                                value="{{ old('price', $draft->price) }}"
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
                                value="{{ old('shipping_fee', $draft->shipping_fee) }}"
                                class="form-control"
                            >

                        </div>

                        {{-- 在庫 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                在庫

                            </label>

                            <input
                                type="number"
                                name="stock"
                                value="{{ old('stock', $draft->stock) }}"
                                class="form-control"
                                required
                            >

                        </div>

                        

                        {{-- Product Attributes --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                Product Attributes (JSON)

                            </label>

                            <textarea
                                name="product_attributes"
                                class="form-control"
                                rows="8"
                            >{{ old(
                                'product_attributes',
                                json_encode(
                                    $draft->product_attributes,
                                    JSON_PRETTY_PRINT |
                                    JSON_UNESCAPED_UNICODE
                                )
                            ) }}</textarea>

                        </div>

                        {{-- 作成者 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                作成者

                            </label>

                            <div class="form-control bg-light">

                                {{ $draft->creator?->name }}


                            </div>

                        </div>

                        {{-- 作成日 --}}
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                作成日

                            </label>

                            <div class="form-control bg-light">

                                {{ $draft->created_at }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>  
        {{-- action buttons --}}

        @if(
            in_array(
                optional(auth()->user()->shopMember)->role,
                ['manager', 'staff']
            ) && 
            $draft->product_id &&
            !in_array($draft->status, ['admin_pending', 'owner_pending'])
        )
            <div class="mt-4 d-flex gap-2">

                {{-- 更新 --}}
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    更新
                </button>
            </div> 
        @endif
    </form>        

    @php
        $member = auth()->user()->shopMember;
    @endphp

    @if(
        $member &&
        $member->role === 'owner' &&
        $draft->status !== 'approved'
    )
        {{-- owner承認 --}}
        <form
            method="POST"
            action="{{ route('seller.product_drafts.approve', $draft) }}"
            class="d-inline"
        >
            @csrf

            <button
                type="submit"
                class="btn btn-success"
                onclick="return confirm('運営審査へ送信しますか？')"
            >
                owner承認
            </button>

        </form>

    @endif

    @if(
    $member &&
    $member->role === 'owner'
    )

        <form
            method="POST"
            action="{{ route('seller.product_drafts.reject', $draft) }}"
            class="d-inline"
        >
            @csrf

            <button
                type="submit"
                class="btn btn-danger"
                onclick="return confirm('差し戻しますか？')"
            >
                差し戻し
            </button>

        </form>

    @endif



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

            const modalElement =
                document.getElementById('priceModal');

            const priceModal =
                bootstrap.Modal.getOrCreateInstance(
                    modalElement
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 即時モーダル
        |--------------------------------------------------------------------------
        */
        priceInput?.addEventListener(
            'input',
            showModal
        );

        shippingInput?.addEventListener(
            'input',
            showModal
        );
    }
);

</script>

@endsection