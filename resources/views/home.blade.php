@extends('layouts.app')

@section('content')

<!-- Side navigation -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://vjs.zencdn.net/7.21.1/video-js.css" rel="stylesheet" />

<link rel="stylesheet" href="{{ asset('front/css/custom91.css') }}">
<link rel="stylesheet" href="{{ asset('front/css/custom96.css') }}">

<script src="https://vjs.zencdn.net/7.21.1/video.min.js"></script>
<style>

    @media screen and (max-width: 384px) {
        .sidenav {
            /*width: 50%;
            padding: 10px;
            background-color: #f8f9fa;*/
            display: none;
        }
    }

    .original-price {
        text-decoration: line-through; /* 元の価格に取り消し線を引く */
        color: grey; /* 元の価格の色を変更 */
    }

    .discounted-price {
        font-weight: bold; /* 太文字 */
        color: red; /* 赤色 */
    }

    .video-overlay-button {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.8);
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
        cursor: pointer;
        display: none; /* 最初は非表示 */
    }

    .video-wrapper {
        position: relative;
        display: inline-block;
    }

    .original-price {
      text-decoration: none;
      color: #d3d3d3;
      transition: all 0.3s ease;
    }

    .discount-price {
      color: #e53935;
      font-weight: bold;
      margin-left: 8px;
    }

    svg.theme-icon-active {
        pointer-events: none;
    }


</style>

{{-- ===== Side Navigation ===== --}}
<aside class="sidenav shadow-sm">
    <h3 class="sidenav-title">Category Menu</h3>

    <ul class="multilevel-dropdown-menu">
        @foreach($categories as $category)
            <li class="parent">

                <a href="{{ route('products.index', ['category_id' => $category->id]) }}">
                    {{ $category->name }}
                </a>

                @php
                    $children = TCG\Voyager\Models\Category::where('parent_id', $category->id)->get();
                @endphp

                @if($children->isNotEmpty())
                    <ul class="child">
                        @foreach($children as $child)
                            <li class="parent">
                                <a href="{{ route('products.index', ['category_id' => $child->id]) }}">
                                    {{ $child->name }} <span class="expand">»</span>
                                </a>

                                @php
                                    $grandChild = TCG\Voyager\Models\Category::where('parent_id', $child->id)->get();
                                @endphp

                                @if($grandChild->isNotEmpty())
                                    <ul class="child">
                                        @foreach($grandChild as $c)
                                            <li class="parent">
                                                <a href="{{ route('products.index', ['category_id' => $c->id]) }}">
                                                    {{ $c->name }}
                                                </a>

                                                @php
                                                    $greatGrandchild = TCG\Voyager\Models\Category::where('parent_id', $c->id)->get();
                                                @endphp

                                                @if($greatGrandchild->isNotEmpty())
                                                    <ul class="child">
                                                        @foreach($greatGrandchild as $ch)
                                                            <li>
                                                                <a href="{{ route('products.index', ['category_id' => $ch->id]) }}">
                                                                    {{ $ch->name }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach

        <li class="pickup-link">
            <a href="{{ route('pickup.catalog.index') }}">
                Products available for in-store pickup（店舗受取可能な商品）
            </a>
        </li>
    </ul>

    {{-- ===== Holiday Information ===== --}}
    @if($holidays->isNotEmpty() || $extra_holidays->isNotEmpty())
    <div class="holiday-bar">

        <div class="holiday-track">
            @foreach($holidays as $holiday)
                <span class="holiday-item holiday-normal">
                    Holiday Store: {{ $holiday['shop_name'] }}
                </span>
            @endforeach

            @foreach($extra_holidays as $ex_holiday)
                @if($ex_holiday['date_flag'] === 2)
                    <span class="holiday-item holiday-close">
                        Stores Temporarily Closed :: {{ $ex_holiday['shop_name'] }}
                    </span>
                @endif

                @if($ex_holiday['date_flag'] === 1)
                    <span class="holiday-item holiday-temp">
                        📣 Temporary Store :: {{ $ex_holiday['shop_name'] }} 📣
                    </span>
                @endif
            @endforeach
        </div>
        <br>
    </div>
    @endif

</aside>

{{-- ===== Main Content ===== --}}
<main class="container main-content">
    <h2 style="color: red;" class="page-title">Products test</h2>

    <a href="{{ route('entrance') }}"><h4>案内板へ戻る</h4></a>

    {{-- ===== Product Ranking ===== --}}
    @php
        $products = collect();
        foreach($norm_products_pres as $attr) {
            foreach($attr->fovo_dises as $n) {
                $products->push([
                    'attr' => $attr,
                    'score' => $n->norm_total,
                    'movie' => json_decode($attr->movie, true)
                ]);
            }
        }
        $sorted = $products->sortByDesc('score')->values();
        $tax_rate = App\Models\TaxRate::current()?->rate;
    @endphp

    @foreach($sorted as $index => $item)
        @php
            $attr = $item['attr'];
            $score = $item['score'];
            $movies = $item['movie'];
        @endphp

        {{-- ===== No.1 Product ===== --}}
        @if($index === 0)
            <section class="featured-product">
                <header class="featured-header">
                    <h2>🏆 お薦め度（1位）</h2>
                    <p class="product-name">Name: {{ $attr->name }}</p>
                    <p class="product-price">
                        Price: {{ floor(($attr->price+$attr->shipping_fee)*($tax_rate+1)) }}
                    </p>
                    <p class="product-score">Score: {{ $score }}</p>
                </header>

                <div class="featured-videos">
                    @foreach($movies as $movie)
                        <div class="video-container">
                            <div class="video-wrapper" data-product-id="{{ $attr->id }}">
                                <video id="video-{{ $loop->index }}"
                                       class="video-js my-video"
                                       controls
                                       muted
                                       playsinline
                                       preload="metadata">
                                    <source src="{{ asset('storage/'.$movie['download_link']) }}" type="video/mp4">
                                </video>

                                <div class="overlay-btn video-overlay-button">
                                    🔥 商品を見る
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>





        {{-- ===== Other Products ===== --}}
        @else
            @if($index % 2 === 1)
                <div class="product-row">
            @endif

            <article class="product-card">
                <p>Name: {{ $attr->name }}</p>
                <p>Price: {{ floor(($attr->price+$attr->shipping_fee)*($tax_rate+1)) }}</p>
                <p class="product-score">Score: {{ $score }}</p>

                @foreach($movies as $movie)
                    <div class="video-wrapper" data-product-id="{{ $attr->id }}">
                        <video class="video-js my-video" controls muted playsinline preload="metadata">
                            <source src="{{ asset('storage/'.$movie['download_link']) }}" type="video/mp4">
                        </video>

                        <div class="overlay-btn video-overlay-button">
                            🔥 商品を見る
                        </div>
                    </div>
                @endforeach
            </article>

            @if($index % 2 === 0 || $loop->last)
                </div>
            @endif
        @endif
    @endforeach

    {{-- ===== Tax Sections ===== --}}
    <section class="tax-products">
        <div class="tax-inner">
            <h3>課税事業者の商品一覧</h3>
            <div class="row">
                @foreach ($discountedProducts->whereNotNull('shop.invoice_number') as $product)
                    <div class="col-4">
                        @include('partials.product_card', [
                            'product' => $product,
                            'tax_rate' => $tax_rate,
                            'product_attributes' => $product_attributes
                        ])
                    </div>
                @endforeach
            </div>

            <h3>非課税事業者の商品一覧</h3>
            <div class="row">
                @foreach ($discountedProducts->whereNull('shop.invoice_number') as $product)
                    <div class="col-4">
                        @include('partials.product_card', [
                            'product' => $product,
                            'tax_rate' => 0,
                            'product_attributes' => $product_attributes
                        ])
                    </div>
                @endforeach
            </div>
        </div>    
    </section>
</main>
    

<script src="https://vjs.zencdn.net/7.21.1/video.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.my-video').forEach((videoEl) => {
        const wrapper = videoEl.closest('.video-wrapper');
        if (!wrapper) return;

        const overlayBtn = wrapper.querySelector('.overlay-btn');
        const productId = wrapper.dataset.productId;

        /* ===== Video.js 初期化（1回だけ）===== */
        const player = videojs(videoEl, {
            fluid: false,          // ← CSSで高さ管理するので false
            responsive: false,
            controls: true,
            preload: 'metadata',
        });

        player.ready(() => {
            player.on('play', () => {
                setTimeout(() => {
                    if (overlayBtn) {
                        overlayBtn.style.display = 'block';
                    }
                }, 1000);
            });
        });

        if (overlayBtn) {
            overlayBtn.addEventListener('click', () => {
                if (productId) {
                    window.location.href = `/product/${productId}`;
                } else {
                    alert("商品IDが見つかりませんでした。");
                }
            });
        }
    });
});
</script>

@endsection