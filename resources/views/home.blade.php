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
                <p style="font-size: 1.2em;">Price: {{ floor(($attr->price+$attr->shipping_fee)*1.1) }}</p>
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
                    <p>Price: {{ floor(($attr->price+$attr->shipping_fee)*1.1) }}</p>
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

        @foreach ($discountedProducts as $product)


            <div class="col-4">
                @if($product->status==0)
                    
                    <div class="card card-skin change-border01 container10">
                        <div class="card-body change-border01__inner">
                            @if(isset($product->cover_img) && !empty($product->cover_img))
                                <img class="card-img-top" src="{{ asset( 'storage/'.$product->cover_img ) }}" alt="Card image cap">               
                            @else
                                <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">
                            @endif
                            <h4 class="card-title overlay10">Inactive</h4>
                            <span class="change-border01__inner"><h4 class="card-title">Coming Soon !!</h4></span>
                            
                            
                            <h4 class="card-title">Coming Soon !!</h4>
                            <h4 class="card-title">Coming Soon !!</h4>
                            <h4 class="card-title">Coming Soon !!</h4>
                            <h4 class="card-title">Coming Soon !!</h4>
                        </div>
                    </div>
                    <br>   
                @else
                <div class="card card-skin change-border01">
                    <div class="card-body change-border01__inner">
                        <a class="" href="{{ route('products.detail', ['id'=>$product->id]) }}">

                            @if(isset($product->cover_img) && !empty($product->cover_img))
                                <img class="card-img-top" src="{{ asset( 'storage/'.$product->cover_img ) }}" alt="Card image cap">               
                            @else
                                <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">
                            @endif
                        </a>

                        <div class="card-body change-border01__inner">

                            <h4 class="card-title">{{ $product->name }}</h4>

                            {{ $product->description }}

                            <h4 class="card-title">
                                @if ($product->campaign)
                                    <div class="price-box">
                                        <span class="original-price"> 
                                            ¥{{ number_format(floor(($product->price+$product->shipping_fee)*1.1)) }}
                                             </span>
                                        <span class="discount-price" style="display: none;">キャンペーン価格: ¥{{ number_format(floor(($product->discounted_price)*1.1)) }} 
                                            </span>
                                        <div class="ribbon1"> Campaign !! </div>
                                        <div class="ribbon2">
                                             <small>Up to: {{Carbon\Carbon::parse($product->campaign->end_date)->format('Y/m/d')}}</small>
                                        </div>
                                    </div>
                                @else
                                    ¥{{ floor(($product->price+$product->shipping_fee)*1.1) }}   

                                @endif

                            </h4>    
                            <h4 class="card-title" id="stockQty">
                             @if($product->stock<=0) 
                                <div class="ribbon">Sold out!!</div>
                             @else  
                                Stock : {{ $product->stock }}
                             @endif   
                            </h4>
                            @foreach($product_attributes as $attr)
                                @foreach ($attr->values as $val)
                                    @if((!empty(json_decode($product->product_attributes,true)[$attr->name]) && json_decode($product->product_attributes,true)[$attr->name] == $val->value))
                                
                                    <h4>{{$attr->name}} : {{$val->value}}</h4>
                                    @endif
                                @endforeach
                            @endforeach
                            <h4 class="card-title">{{ $product->shop->name }}</h4>

                            <a class="" href="{{ route('customer.inquiry', ['shopId'=>$product->shop->id]) }}"><h5>Contact Shop Manager</h5></a>
                            <div class="card-body change-border01__inner" id="addCart1">
                                <a href="{{ route('cart.add', $product->id) }}" class="card-link">Add to Cart</a>
                            </div>
                            
                               
                        </div>                      
                        
                    </div>
                </div>     
                @endif
            </div>
            <br>
        @endforeach
        
    </div><br>
    

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