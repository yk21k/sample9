@extends('layouts.seller')
@section('content')
    @foreach ($errors->all() as $error)
      <li>{{$error}}</li>
    @endforeach
    <h2>Desplay Select Form</h2>
    
    @if($check_desplay_count == 0)
    <form method="POST" action="{{ route('seller.select_desplay') }}" enctype="multipart/form-data">@csrf
          <div class="form-check">
            <input class="form-check-input" type="radio" name="desplay_id" id="template1" value="1">
            <label class="form-check-label" for="template1">
              Template 1
            </label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="radio" name="desplay_id" id="template2" value="2">
            <label class="form-check-label" for="template2">
              Template 2
            </label>
          </div>
       
    

        <h2>Template 1</h2>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>店舗情報 - 店名</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f9f9f9;
                        color: #333;
                    }
                    header {   
                        background-size: cover;
                        background-position: center;
                        color: white;
                        text-align: center;
                        padding: 20px 20px;
                    }
                    header h1 {
                        margin: 0;
                        font-size: 3em;
                    }
                    header p {
                        font-size: 1.2em;
                    }
                    section {
                        padding: 10px;

                    }

                    .display-shop1{
                        position: relative;
                        z-index: 1;
                        width: 80%;
                        height: 80%;        
                    }

                    
                    .display-shop-text{
                        position: relative;
                        z-index: 3;
                        margin: 0.5%;
                        
                    }

                    .display-shop-text1{
                        position: relative;
                        z-index: 3;
                        margin: 0.5%;
                        width: 80%;
                        height: 80%;
                    }

                    .info {
                        list-style-type: none;
                        padding: 0;
                    }
                    .info li {
                        margin: 10px 0;
                    }
                    .footer {
                        text-align: center;
                        background-color: #333;
                        color: white;
                        padding: 10px;
                        position: fixed;
                        width: 100%;
                        bottom: 0;
                    }
                    .contact {
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>

                <header class="display-shop1" style="background-image: url({{ asset('image/_46870c8f-f444-4ba0-a61f-058d489cb703.jpeg') }});">
                    <h1>店名</h1>
                    <p>ご来店いただきありがとうございます！</p>
                </header>

                <div class="display-shop1" style="background-image: url({{ asset('image/_46870c8f-f444-4ba0-a61f-058d489cb703.jpeg') }});">
                    <div class="display-shop-text1">
                        <h2>店舗情報</h2>
                        <ul class="info">
                            <li><strong>店名:</strong> 店名</li>
                            <li><strong>URL:</strong> <a href="https://www.example.com" target="_blank">www.example.com</a></li>
                            <li><strong>電話番号:</strong> <span class="contact">012-345-6789</span></li>
                            <li><strong>住所:</strong> 東京都新宿区西新宿2-8-1</li>
                            <li><strong>商圏:</strong> 新宿区、渋谷区、渋谷駅周辺</li>
                        </ul>
                    </div>    
                </div>

            </body>
        <br><br><br>

        <h2>Template 2 商品表示タイプ</h2>
        <!-- auctionのtemplate -->
            <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <title>商品表示タイプ</title>
              <style>
                    /* style.css */
                    * {
                      margin: 0;
                      padding: 0;
                      box-sizing: border-box;
                    }

                    body {
                      font-family: Arial, sans-serif;
                      background-color: #f4f4f4;
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      height: 100％;
                    }

                    .auction-container {
                      background-color: #b0c4de;  /* 背景 */
                      color: #2e2929;             /* 文字 */
                      border-radius: 8px;
                      padding: 20px;
                      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                      text-align: center;
                      width: 100%; /* 横幅を広げて、画像と詳細が並ぶように調整 */
                    }

                    h1 {
                      font-size: 24px;
                      margin-bottom: 20px;
                    }

                    .auction-item {
                      display: flex;            /* 横並びにするためにflexboxを使用 */
                      align-items: flex-start;  /* アイテムを上揃えにする */
                      justify-content: flex-start;
                      margin-bottom: 20px;
                    }

                    .item-image {
                      width: 300px;             /* 商品画像の幅を指定 */
                      border-radius: 8px;
                      margin-right: 20px;       /* 画像と詳細の間にスペースを作る */
                    }

                    .item-details {
                      flex-grow: 1;             /* 詳細部分が残りの幅を占めるようにする */
                      text-align: left;         /* 詳細を左寄せに */
                    }

                    .item-title {
                      font-size: 20px;
                      margin-bottom: 10px;
                    }

                    .item-description {
                      font-size: 14px;
                      color: #2e2929; /* 文字色 */
                      margin-bottom: 20px;
                    }

                    .item-price {
                      font-size: 18px;
                      font-weight: bold;
                      margin-bottom: 20px;
                    }

                    .item-details p {
                      margin: 5px 0;
                    }

                    .item-details a {
                      color: #00aaff; /* リンクを青に */
                      text-decoration: none;
                    }

                    .item-details a:hover {
                      text-decoration: underline;
                    }

                    input[type="number"] {
                      padding: 8px;
                      margin-right: 10px;
                      width: 80%;
                      border-radius: 4px;
                      border: 1px solid #ccc;
                    }

                    button {
                      padding: 8px 12px;
                      background-color: #4CAF50;
                      color: white;
                      border: none;
                      border-radius: 4px;
                      cursor: pointer;
                    }

                    button:hover {
                      background-color: #45a049;
                    }

                    .message {
                      margin-top: 20px;
                      font-size: 16px;
                      color: red;
                    }

                        /*  追加  */
                    .form-container {
                      max-width: 80％;
                      margin: 0 auto;
                      background-color: #5a798f;
                      padding: 20px;
                      border-radius: 8px;
                      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }

                    h2 {
                      text-align: center;
                      margin-bottom: 30px;
                      color: #333;
                    }

                    .section {
                      margin-bottom: 40px;
                    }

                    .section h3 {
                      margin-bottom: 10px;
                      color: #333;
                    }

                    label {
                      margin: 5px 0 5px;
                      color: #bfbbbb;
                    }

                    input[type="text"], input[type="tel"], input[type="email"], input[type="file"], input[type="number"] {
                      width: 100%;
                      padding: 10px;
                      margin-bottom: 20px;
                      border: 1px solid #ccc;
                      border-radius: 4px;
                    }

                    input[type="file"] {
                      padding: 5px;
                    }

                    input[type="number"] {
                      width: auto;
                    }

                    .btn {
                      padding: 12px 20px;
                      background-color: #28a745;
                      color: white;
                      border: none;
                      border-radius: 4px;
                      cursor: pointer;
                      font-size: 16px;
                      display: block;
                      margin: 0 auto;
                    }

                    .btn:hover {
                      background-color: #218838;
                    }

              </style>

            </head>
            <body>
              <div class="auction-container">
                <h1>店名</h1>
                <div class="row">
                  <div class="col-4">
                    <div class="list-group" id="list-tab" role="tablist">
                      <a class="list-group-item list-group-item-action active" id="list-home-list" data-bs-toggle="list" href="#list-home" role="tab" aria-controls="list-home">商品１</a>
                      <a class="list-group-item list-group-item-action" id="list-profile-list" data-bs-toggle="list" href="#list-profile" role="tab" aria-controls="list-profile">商品２</a>
                      <a class="list-group-item list-group-item-action" id="list-messages-list" data-bs-toggle="list" href="#list-messages" role="tab" aria-controls="list-messages">商品３</a>
                      <a class="list-group-item list-group-item-action" id="list-settings-list" data-bs-toggle="list" href="#list-settings" role="tab" aria-controls="list-settings">その他のご案内</a>
                    </div>
                  </div>
                  <div class="col-8">
                    <div class="tab-content" id="nav-tabContent">
                      <div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
                            <!-- 商品情報 -->
                            <div class="auction-item">
                              <img src="{{ asset('image/earring3.jpg') }}" alt="商品画像" class="item-image">
                              <div class="item-details">
                                <h2 class="item-title">素晴らしい商品</h2>
                                <p class="item-description">この商品は素晴らしいアイテムです。</p>
                                
                                <!-- 商品詳細 -->
                                <p><strong>商品名:</strong> 素晴らしい商品</p>
                                <p><strong>ショップ名:</strong> ショップ</p>
                                <p><strong>URL:</strong> <a href="https://example.com" target="_blank">https://example.com</a></p>
                                <p><strong>住所:</strong> 東京都新宿区1-2-3</p>
                                <p><strong>メールアドレス:</strong> example@example.com</p>
                                <p><strong>電話番号:</strong> 03-1234-5678</p>
                                
                              </div>
                            </div>

                      </div>

                      <div class="tab-pane fade" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
                            <!-- 商品情報 -->
                            <div class="auction-item">
                              <img src="{{ asset('image/3dtest7.jpeg') }}" alt="商品画像" class="item-image">
                              <div class="item-details">
                                <h2 class="item-title">素晴らしい商品</h2>
                                <p class="item-description">この商品は素晴らしいアイテムです。</p>
                                
                                <!-- 商品詳細 -->
                                <p><strong>商品名:</strong> 素晴らしい商品</p>
                                <p><strong>ショップ名:</strong> ショップ</p>
                                <p><strong>URL:</strong> <a href="https://example.com" target="_blank">https://example.com</a></p>
                                <p><strong>住所:</strong> 東京都新宿区1-2-3</p>
                                <p><strong>メールアドレス:</strong> example@example.com</p>
                                <p><strong>電話番号:</strong> 03-1234-5678</p>
                                

                              </div>
                            </div>

                             
                      </div>

                      <div class="tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-messages-list">
                            <!-- 商品情報 -->
                            <div class="auction-item">
                              <img src="{{ asset('image/earting1.jpg') }}" alt="商品画像" class="item-image">
                              <div class="item-details">
                                <h2 class="item-title">素晴らしい商品</h2>
                                <p class="item-description">この商品は素晴らしいアイテムです。</p>
                                
                                <!-- 商品詳細 -->
                                <p><strong>商品名:</strong> 素晴らしい商品</p>
                                <p><strong>ショップ名:</strong> ショップ</p>
                                <p><strong>URL:</strong> <a href="https://example.com" target="_blank">https://example.com</a></p>
                                <p><strong>住所:</strong> 東京都新宿区1-2-3</p>
                                <p><strong>メールアドレス:</strong> example@example.com</p>
                                <p><strong>電話番号:</strong> 03-1234-5678</p>
                                
                              </div>
                            </div>

                            
                      </div>
                      <div class="tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-settings-list">その他のご案内　PR
                            <p><strong>PR:</strong> PR..............</p>
                            <!-- 実店舗情報 -->
                            <p><strong>実店舗名:</strong> 実店舗A</p>
                            <p><strong>実店舗住所:</strong> 東京都渋谷区4-5-6</p>
                            <!-- 実店舗情報 -->
                            <p><strong>実店舗名:</strong> 実店舗B</p>
                            <p><strong>実店舗住所:</strong> 東京都豊島区4-5-6</p>
                            <!-- 実店舗情報 -->
                            <p><strong>実店舗名:</strong> 実店舗C</p>
                            <p><strong>実店舗住所:</strong> 東京都新宿区4-5-6</p>
                      </div>

                    </div>
                  </div>
                </div>

              </div>
            </body>

            　<div class="form-container">
                <h2>商品情報入力フォーム</h2>
                
                <!-- 取り扱い者情報 -->
                <div class="section">
                  <h3>取り扱い者情報 Template1と共通</h3>
                  <input type="hidden" name="seller_mail" value="{{ auth()->user()->email }}">

                  <label for="shop_name">店名:</label>
                  <input type="text" name="shop_name" id="shop_name" placeholder="店名を入力">
                  
                  <label for="desplay_phone">電話番号:</label>
                  <input type="tel" name="desplay_phone" id="desplay_phone" pattern="[0-9]{3}-[0-9]{4}-[0-9]{4}" placeholder="060-0000-0000">
                  
                  <label for="url">URL:</label>
                  <input type="text" name="url" id="url" placeholder="URLを入力"><br><br>
                  
                  <label for="mail">メールアドレス:</label>
                  <input type="email" name="desplay_mail" id="desplay_mail" placeholder="メールアドレスを入力">
                  
                  <label for="desplay_real_store1">実店舗名1:</label>
                  <input type="text" name="desplay_real_store1" id="desplay_real_store1" placeholder="実店舗名1を入力">
                  
                  <label for="desplay_real_address1">実店舗の住所1:</label>
                  <input type="text" name="desplay_real_address1" id="desplay_real_address1" placeholder="住所を入力">
                  
                  <label for="desplay_real_store2">実店舗名2:1</label>
                  <input type="text" name="desplay_real_store2" id="desplay_real_store2" placeholder="実店舗名2を入力">
                  
                  <label for="desplay_real_address2">実店舗の住所2:</label>
                  <input type="text" name="desplay_real_address2" id="desplay_real_address2" placeholder="住所を入力">
                  
                  <label for="desplay_area">商圏:</label>
                  <input type="text" name="desplay_area" id="desplay_area" placeholder="商圏（商圏人口、年齢層、自治体名、国名、無記入などご自由に）を入力">

                  <label for="desplay_area">PR:</label>
                  <input type="text" name="desplay_pr" id="desplay_pr" placeholder="PRを入力 500文字以内">
                  <h3 style="text-align: center;"><label>---------------Template1はここまで -----------</label></h3>
                </div>
                
                <!-- 商品情報 -->
                <div class="section">
                  <h3>商品情報</h3>
                  <br>
                  <!-- 商品1 -->
                  <div class="product-info">
                    <h4>商品1</h4>
                    <label for="name1">商品名1:</label>
                    <input type="text" name="name1" id="name1" placeholder="商品名1を入力">
                    
                    <label for="photo1">商品の写真1:</label>
                    <input type="file" name="photo1" id="photo1" multiple>
                    
                    <label for="description">商品の説明1:</label>
                    <input type="text" name="description1" id="description1" placeholder="商品1の説明を入力">
                    
                  </div>
                  
                  <!-- 商品2 -->
                  <div class="product-info">
                    <h4>商品2</h4>
                    <label for="name2">商品名2:</label>
                    <input type="text" name="name2" id="name2" placeholder="商品名2を入力">
                    
                    <label for="photo2">商品の写真2:</label>
                    <input type="file" name="photo2" id="photo2" multiple>
                    
                    <label for="description2">商品の説明2:</label>
                    <input type="text" name="description2" id="description2" placeholder="商品2の説明を入力">
                    
                  </div>
                  
                  <!-- 商品3 -->
                  <div class="product-info">
                    <h4>商品3</h4>
                    <label for="name3">商品名3:</label>
                    <input type="text" name="name3" id="name3" placeholder="商品名3を入力">
                    
                    <label for="photo3">商品の写真3:</label>
                    <input type="file" name="photo3" id="photo3" multiple>
                    
                    <label for="description3">商品の説明3:</label>
                    <input type="text" name="description3" id="description3" placeholder="商品3の説明を入力">
                    
                  </div>
                </div>

              </div>
            <button type="submit" class="btn btn-primary mb-2 mr-2">Submit</button>
        </form>

        <!-- 別のtemplate -->
        <form method="POST" action="{{ route('seller.select_desplay') }}">@csrf
            <ul>
                <li>SHOP NAME: hhh</li>
                <li>Email: fff</li>
                <li>REGISTER: ddd</li>
            </ul>
            <input type="hidden" name="desplay_id" value="3">
            <button type="submit" class="btn btn-primary mb-2 mr-2">Submit</button>
        </form>
        
    @elseif($check_desplay->is_active == 0)
        Thank you for Choosing!!
        取り消しは確認後、有効になります。表示されるかは、マイページに表示いたします。
    @elseif($check_desplay->is_active == 1)
        Thank you for Choosing!!

        @php
            $today = \Carbon\Carbon::today();  // 本日の日付を取得
        @endphp
        @if ($today->gte($twoWeeksLater))
            Thank you for Choosing!! 
            <form method="POST" action="{{ route('seller.delete_desplay') }}">@csrf
                <button type="submit" class="btn btn-primary mb-2 mr-2">Delete Page</button>
            </form>
        @else
            
            <p>サイトの削除についてご希望の場合は、{{ $twoWeeksLater->format('Y年m月d日') }}以降に再度こちらのページから下記ボタンを押して下さい</p>
            <button type="submit" class="btn btn-primary mb-2 mr-2" disabled>Delete Page</button>
        @endif
    @endif
@endsection