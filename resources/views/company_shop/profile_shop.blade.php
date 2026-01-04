@extends('layouts.app')
@section('content')
<style>
    /* ベーススタイル */
/*    body {
        font-family: "Hiragino Sans", "Noto Sans JP", sans-serif;
        line-height: 1.6;
        margin: 0;
        padding: 0;
        background: #f9f9f9;
        color: #333;
    }*/

    main {
        padding: 40px 20px;
    }

    /* コンテナ */
    .container {
        /*background: #fff;*/
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 30px;
        margin-bottom: 40px;
    }

    /* セクション */
    section {
        margin-bottom: 40px;
    }

    section h2 {
        font-size: 1.8rem;
        margin-bottom: 15px;
        border-bottom: 2px solid #27627d;
        display: inline-block;
        padding-bottom: 5px;
        color: #27627d;
    }

    section h3, 
    section h4 {
        margin-top: 20px;
        color: #444;
    }

    /* 実店舗情報 */
    #about p {
        margin: 5px 0;
    }

    /* 商品一覧 */
    #products .product-list {
        margin-bottom: 25px;
    }

    #products h4 {
        font-size: 1.2rem;
        margin-bottom: 10px;
        color: #222;
    }

    /* 商品アイテム */
    .product-item {
        width: 180px;
        height: 180px;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .product-item:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }

    /* お問い合わせ */
    #contact a {
        display: inline-block;
        background: #27627d;
        color: #fff;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    #contact a:hover {
        background: #1a4657;
    }

    /* レスポンシブ */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }

        .product-item {
            width: 100%;
            height: auto;
        }
    }

    .video-container {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 */
        height: 0;
        overflow: hidden;
        max-width: 100%;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        margin-bottom: 20px;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

</style>
<body>
    <main>
        <!-- About Section -->
        @foreach($shop_dataes as $shop_data)
        <div class="container">        
            <section id="about">
                <h2>会社情報</h2>
                <h3>{{ $shop_data->shop->name }}</h3>
                <p>消費税と配送料について<br><small>(出品者からの回答です)</small>{{ $shop_data->pr1 }}</p>
                <p>直営店舗ありますか<br><small>(出品者からの回答です)</small>{{ $shop_data->pr2 }}</p>
                <p>商品や会社PRをお願いします<br><small>(出品者からの回答です)</small>{{ $shop_data->pr3 }}</p>
                <!-- 店舗紹介動画 -->
                <div class="video-wrapper">
                    <h4>店舗/取扱会社/出品者紹介動画</h4>
                    当サイトに掲載される動画は、出品者が提示した外部サービス（YouTube等）上のURLに基づき表示されるものです。
                    当サイトは、当該動画の正当な管理者、作成者、または公式性について、確認・保証を行うものではありません。
                    動画の内容や権利関係についてのご質問・お問い合わせは、当該動画の掲載元または出品者へ直接お願いいたします。
                    なお、権利侵害やなりすましの疑いがある場合には、当サイト所定のお問い合わせでご連絡ください。当サイトは、適切な範囲で確認・対応を行います。
                    <div class="video-container">
                        <iframe width="560" height="315" 
                            src="{{ $shop_data->url }}" 
                            title="店舗紹介動画"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>                   
                    </div>
                </div>

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

                            <!-- 商品紹介動画（あれば表示） -->
                            @if(!empty($shop_product->youtube_url))
                                <div class="video-container">

                                    <iframe width="560" height="315" 
                                        src="https://www.youtube.com/embed/{{ $shop_product->youtube_url }}" 
                                        title="{{ $shop_product->name }} 紹介動画"
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            @endif

                            @if(isset($shop_product->cover_img) && !empty($shop_product->cover_img))
                                <div class="product-item">
                                    <a href="{{ route('products.detail', ['id'=>$shop_product->id]) }}">
                                        <img src="{{ asset( 'storage/'.$shop_product->cover_img ) }}" alt="{{ $shop_product->name }}">
                                    </a>
                                </div>     
                            @else
                                <div class="product-item">
                                    <a href="{{ route('products.detail', ['id'=>$shop_product->id]) }}">
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
                <a href="{{ route('inquiries.create', ['id'=>$shop_data->shop->id]) }}">
                   お問い合わせする 
                </a>             
            </section>
        </div>
    
        @endforeach
    </main>     
</body>
@endsection
