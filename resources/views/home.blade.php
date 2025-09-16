@extends('layouts.app')

@section('content')

<!-- Side navigation -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://vjs.zencdn.net/7.21.1/video-js.css" rel="stylesheet" />
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

</style>

<div class="sidenav shadow-sm">
    <div style="color: black;"><a><h3>Category Menu</h3></a></div>
        <ul class="multilevel-dropdown-menu">
            @foreach($categories as $category)

            <li class="parent"><a href="{{ route('products.index', ['category_id' => $category->id]) }}">{{ $category->name }}</a>
                @php 

                    $children = TCG\Voyager\Models\Category::where('parent_id', $category->id)->get();

                @endphp
                @if($children->isNotEmpty())
                    @foreach($children as $child)
                    <ul class="child">
                        <li class="parent"><a href="{{ route('products.index', ['category_id' => $child->id]) }}">{{ $child->name }} <span class="expand">»</span></a>
                            @php 
                                $grandChild = TCG\Voyager\Models\Category::where('parent_id', $child->id)->get();
                            @endphp
                            @if($grandChild->isNotEmpty())
                                @foreach($grandChild as $c)
                                <ul class="child">
                                    <li class="parent"><a href="{{ route('products.index', ['category_id' => $c->id]) }}">{{ $c->name }}</a>
                                        @php 
                                            $greatGrandchild = TCG\Voyager\Models\Category::where('parent_id', $c->id)->get();
                                        @endphp
                                        @if($greatGrandchild->isNotEmpty())

                                            @foreach($greatGrandchild as $ch)
                                            <ul class="child">
                                                <li class="parent"><a href="{{ route('products.index', ['category_id' => $ch->id]) }}">{{ $ch->name }}</a></li>
                                            </ul>
                                            @endforeach
                                        @endif    
                                    </li>    
                                </ul>
                                @endforeach
                            @endif
                        </li>
                    </ul>
                    @endforeach
                @endif
            </li>

            @endforeach
        </ul>
</div>

<div class="container">        

    <div class="row">

        <h2>Products test</h2>
        <br><br><br>
        <div class="text-animation1">  
            @foreach($holidays as $holiday)
                    <p>Holiday Store: {{ $holiday['shop_name'] }}</p>
                &nbsp;&nbsp;&nbsp;           
            @endforeach
        </div>
        @foreach($extra_holidays as $ex_holiday)
            <div class="text-animation2"> 
                @if($ex_holiday['date_flag'] === 2) 
                
                    <p>Stores Temporarily Closed :: {{ $ex_holiday['shop_name'] }}</p>
                
                @endif
                </div> 
                <div class="text-animation3">     
                @if($ex_holiday['date_flag'] === 1)
                    
                    <p>📣📣📣　Temporary Store :: {{ $ex_holiday['shop_name'] }}　📣📣📣</p><br><br><br>
                    
                @endif 
            </div>          
        @endforeach
        <br><br><br><br><br><br>   
            

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

            @if($index === 0)
            <!-- 🏆 1位（特別表示） -->
            <div class="guaranteed-product" style="border: 2px solid gold; padding: 20px; margin-bottom: 30px; background: darkslategray;">
                <h2>🏆 保証品（1位）</h2>
                <p style="font-size: 1.2em;">Name: {{ $attr->name }}</p>
                <p style="font-size: 1.2em;">Price: {{ floor(($attr->price+$attr->shipping_fee)*($tax_rate+1)) }}</p>
                <p style="font-size: 1.2em;">Score: {{ $score }}</p>

                @foreach ($movies as $movie)
                    <div class="video-wrapper" data-product-id="{{ $attr->id }}" style="position: relative; display: inline-block; width: 100%; max-width: 800px; margin: 20px auto; /* 中央寄せ＋上下余白 */">

                        <video class="my-video video-js" controls preload="auto" width="850" height="" data-setup="{}" muted style="max-height: 450px;">
                            <source src="{{ asset('storage/'.$movie['download_link']) }}" type="video/mp4">
                        </video>

                        <div class="overlay-btn video-overlay-button" style="
                            position: absolute;
                            top: 20px;
                            right: 20px;
                            z-index: 10;
                            background: rgba(255,255,255,0.85);
                            padding: 10px 12px;
                            border-radius: 6px;
                            font-weight: bold;
                            cursor: pointer;
                            display: none;
                            font-size: 1em;
                            color:black;">
                            🔥 商品を見る　
                        </div>
                    </div>
                @endforeach
            </div>

            @else
                @if($index % 2 === 1)
                    <div class="product-row" style="display: flex; gap: 20px; margin-bottom: 30px;">
                @endif
                <div class="product" style="flex: 1; border: 1px solid #ccc; padding: 10px;">
                    <p>Name: {{ $attr->name }}</p>
                    <p>Price: {{ floor(($attr->price+$attr->shipping_fee)*($tax_rate+1)) }}</p>
                    <p>Score: {{ $score }}</p>

                    @foreach ($movies as $movie)
                        <div class="video-wrapper" data-product-id="{{ $attr->id }}" style="position: relative; display: inline-block;">
                            <video class="my-video video-js" controls preload="auto" width="640" height="360"data-setup="{}" muted>
                                <source src="{{ asset('storage/'.$movie['download_link']) }}" type="video/mp4">
                            </video>

                            <div class="overlay-btn video-overlay-button" style="
                                position: absolute;
                                top: 20px;
                                right: 20px;
                                z-index: 10;
                                background: rgba(255,255,255,0.85);
                                padding: 8px 12px;
                                border-radius: 6px;
                                font-weight: bold;
                                cursor: pointer;
                                display: none;
                                color: black;">
                                🔥 商品を見る
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($index % 2 === 0 || $loop->last)
                    </div>
                @endif
            @endif
        @endforeach



        <br><br><br>        


        
    </div><br>

    {{-- 課税事業者の商品 --}}
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

    <br><br><br> 

    {{-- 免税事業者の商品 --}}
    <h3>免税事業者の商品一覧</h3>
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

    

</div><br>
    
<script src="https://vjs.zencdn.net/7.21.1/video.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const videoElements = document.querySelectorAll('.my-video');

    videoElements.forEach((videoEl) => {
        const player = videojs(videoEl);
        const wrapper = videoEl.closest('.video-wrapper');
        const overlayBtn = wrapper.querySelector('.overlay-btn');
        const productId = wrapper.dataset.productId;

        player.ready(function () {
            player.on('play', function () {
                setTimeout(() => {
                    overlayBtn.style.display = 'block';
                }, 1000);
            });
        });

        overlayBtn.addEventListener('click', function () {
            if (productId) {
                window.location.href = `/product/${productId}`;
            } else {
                alert("商品IDが見つかりませんでした。");
            }
        });
    });
});

</script>
<script>
document.querySelectorAll('.price-box').forEach(function(box) {
  box.addEventListener('mouseenter', function() {
    const original = box.querySelector('.original-price');
    const discount = box.querySelector('.discount-price');

    original.style.textDecoration = 'line-through';
    discount.style.display = 'inline';
  });

  box.addEventListener('mouseleave', function() {
    const original = box.querySelector('.original-price');
    const discount = box.querySelector('.discount-price');

    original.style.textDecoration = 'none';
    discount.style.display = 'none';
  });
});
</script>




@endsection