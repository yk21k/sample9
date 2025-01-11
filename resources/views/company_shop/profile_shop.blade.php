@extends('layouts.app')
@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('front/css/custom92.css') }}">

</head>
<body>
    <main>
        <!-- About Section -->
        @foreach($shop_dataes as $shop_data)
        <div class="container">        
            <section id="about">
                <h2>会社情報</h2>
                <h3>{{ $shop_data->shop->name }}</h3>
                <p>{{ $shop_data->pr1 }}</p>

                <!-- Store Section -->
                <h4>実店舗情報</h4>
                <p>〒　{{ $shop_data->zip_code1 }}</p>
                <p>{{ $shop_data->address1 }}</p>
                @if($shop_data->free_delivery == "no")
                    <p>当サイトでは、無料の配送を行っていません。詳細をご確認希望時は、下記お問い合わせよりお問い合わせください</p>
                @elseif($shop_data->free_delivery == "yes")
                    <p>当サイトでは、無料の配送を行っております。詳細をご確認希望時は、下記お問い合わせよりお問い合わせください</p>
                @else 
                    <p>ーー</p>   
                @endif
            </section>
            <!-- Products Section -->
            <section id="products">
                <h2>商品一覧</h2>
                @foreach($shop_products as $shop_product)
                    @if($shop_data->shop_id == $shop_product->shop_id)
                        <div class="product-list">
                            <h4>{{ $shop_product->name }}</h4>
                            @if(isset($shop_product->cover_img) && !empty($shop_product->cover_img))
                                <div class="product-item">
                                    <a class="" href="{{ route('products.detail', ['id'=>$shop_product->id]) }}">
                                    <img src="{{ asset( 'storage/'.$shop_product->cover_img ) }}" alt="{{ $shop_product->name }}">
                                    
                                    </a>
                                </div>      
                            @else
                                <div class="product-item">
                                    <a class="" href="{{ route('products.detail', ['id'=>$shop_product->id]) }}">
                                    <img src="{{ asset('images/no_image.jpg') }}" alt="{{ $shop_product->name }}">
                                    </a>
                                </div> 
                            @endif 
                        </div>
                    @endif
                    
                @endforeach
            </section>
            <!-- Contact Section -->
            <section id="contact">
                <h2>お問い合わせ(実店舗ではなくショップマネージャーへ)</h2>
                <a class="" href="{{ route('inquiries.create', ['id'=>$shop_data->shop->id]) }}">
                   お問い合わせする 
                </a>             
            </section>
            <br><br><br><br>
        </div>    
        </main>


            
        @endforeach

</body>

@endsection
