@if($product->status == 0)
    {{-- 非公開商品（Coming Soon） --}}
    <div class="card card-skin change-border01 container10">
        <div class="card-body change-border01__inner">
            <img class="card-img-top" 
                 src="{{ $product->cover_img ? asset('storage/'.$product->cover_img) : asset('images/no_image.jpg') }}" 
                 alt="Card image cap">               

            <h4 class="card-title overlay10">Inactive</h4>
            <span class="change-border01__inner">
                <h4 class="card-title">Coming Soon !!</h4>
                <h4 class="card-title">Coming Soon !!</h4>
                <h4 class="card-title">Coming Soon !!</h4>
                <h4 class="card-title">Coming Soon !!</h4>
            </span>
        </div>
    </div>
@else
    {{-- 公開商品 --}}
    <div class="card card-skin change-border01">
        <div class="card-body change-border01__inner">
            <a href="{{ route('products.detail', ['id'=>$product->id]) }}">
                <img class="card-img-top" 
                     src="{{ $product->cover_img ? asset('storage/'.$product->cover_img) : asset('images/no_image.jpg') }}" 
                     alt="Card image cap">
            </a>

            <div class="card-body change-border01__inner">
                <h4 class="card-title">{{ $product->name }}</h4>
                <p>{{ $product->description }}</p>

                {{-- 価格 --}}
                <h4 class="card-title">
                    @if ($product->campaign)
                        <div class="price-box">
                            <span class="original-price">
                                ¥{{ number_format(floor(($product->price+$product->shipping_fee)*($tax_rate+1))) }}
                            </span>
                            <span class="discount-price">
                                キャンペーン価格: ¥{{ number_format(floor(($product->discounted_price)*($tax_rate+1))) }}
                            </span>
                            <div class="ribbon1">Campaign !!</div>
                            <div class="ribbon2">
                                <small>Up to: {{ Carbon\Carbon::parse($product->campaign->end_date)->format('Y/m/d') }}</small>
                            </div>
                        </div>
                    @else
                        ¥{{ floor(($product->price+$product->shipping_fee)*($tax_rate+1)) }}
                    @endif
                </h4>

                {{-- 在庫 --}}
                <h4 class="card-title" id="stockQty">
                    @if($product->stock <= 0)
                        <div class="ribbon">Sold out!!</div>
                    @else
                        Stock : {{ $product->stock }}
                    @endif
                </h4>

                {{-- 商品属性 --}}
                @foreach($product_attributes as $attr)
                    @foreach ($attr->values as $val)
                        @if(!empty(json_decode($product->product_attributes, true)[$attr->name]) && 
                            json_decode($product->product_attributes, true)[$attr->name] == $val->value)
                            <h4>{{ $attr->name }} : {{ $val->value }}</h4>
                        @endif
                    @endforeach
                @endforeach

                {{-- 店舗名 + バッジ --}}
                <h4 class="card-title">
                    {{ $product->shop->name }}
                    @if($product->shop->invoice_number)
                        <span class="badge bg-danger ms-2">課税事業者</span>
                    @else
                        <span class="badge bg-success ms-2">免税事業者</span>
                    @endif
                </h4>

                {{-- 問い合わせ --}}
                <a href="{{ route('customer.inquiry', ['shopId'=>$product->shop->id]) }}">
                    <h5>Contact Shop Manager</h5>
                </a>

                {{-- カート追加 --}}
                <div class="card-body change-border01__inner" id="addCart1">
                    <a href="{{ route('cart.add', $product->id) }}" class="card-link">Add to Cart</a>
                </div>
            </div>
        </div>
    </div>
@endif

<br><br><br>
