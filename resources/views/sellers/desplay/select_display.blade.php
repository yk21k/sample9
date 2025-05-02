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
                                
                                <!-- 商品詳細 -->
                                <p><strong>商品名:</strong> 素晴らしい商品</p>
                                <p><strong>商品説明:</strong> .............商品説明文　............</p>
                                <p><strong>ショップ名:</strong> ショップ</p>
                                <p><strong>住所:</strong> 商品とある店舗と合致させる東京都仮想区1-2-3</p>
                                
                              </div>
                            </div>

                      </div>

                      <div class="tab-pane fade" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
                            <!-- 商品情報 -->
                            <div class="auction-item">
                              <img src="{{ asset('image/3dtest7.jpeg') }}" alt="商品画像" class="item-image">
                              <div class="item-details">
                                <h2 class="item-title">素晴らしい商品</h2>
                                
                                <!-- 商品詳細 -->
                                <p><strong>商品名:</strong> 素晴らしい商品</p>
                                <p><strong>商品説明:</strong> .............商品説明文　............</p>
                                <p><strong>ショップ名:</strong> ショップ</p>
                                <p><strong>住所:</strong> 商品とある店舗と合致させる東京都仮想区1-2-3</p>
                                
                              </div>
                            </div>

                             
                      </div>

                      <div class="tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-messages-list">
                            <!-- 商品情報 -->
                            <div class="auction-item">
                              <img src="{{ asset('image/earting1.jpg') }}" alt="商品画像" class="item-image">
                              <div class="item-details">
                                <h2 class="item-title">素晴らしい商品</h2>
                                
                                <!-- 商品詳細 -->
                                <p><strong>商品名:</strong> 素晴らしい商品</p>
                                <p><strong>商品説明:</strong> .............商品説明文　............</p>
                                <p><strong>ショップ名:</strong> ショップ</p>
                                <p><strong>住所:</strong> 商品とある店舗と合致させる東京都仮想区1-2-3</p>
                                
                              </div>
                            </div>

                            
                      </div>
                      <div class="tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-settings-list">その他のご案内　PR
                            <p><strong>PR:</strong> PR..............</p>
                            <!-- 会社情報 -->
                            <p><strong>会社名（屋号）:</strong> ⚪︎⚪︎商店　⚪︎⚪︎会社</p>
                            <p><strong>会社住所:</strong>
                             <a href="https://www.google.com/maps/search/?q=" target="_blank" rel="noopener noreferrer">
                                東京都東京区⚪︎-⚪︎-⚪︎
                            </a>
                            </p>
                            <p><strong>道順や電話番号/TEL/email、営業日は、Googleマップで検索ください</strong>
                            <!-- 実店舗情報 -->
                            <p><strong>実店舗名:</strong> 実店舗A</p>
                            <p><strong>実店舗住所:</strong> 
                                <a href="https://www.google.com/maps/search/?q=" target="_blank" rel="noopener noreferrer">
                                    東京都新東京区⚪︎-⚪︎-⚪︎
                                </a>
                            </p>
                            <!-- 実店舗情報 -->
                            <p><strong>実店舗名:</strong> 実店舗B</p>
                                <p><strong>実店舗住所:</strong> 
                                <a href="https://www.google.com/maps/search/?q=" target="_blank" rel="noopener noreferrer">
                                    東京都ニュー東京区⚪︎-⚪︎-⚪︎
                                </a>
                            </p>
                            <!-- 実店舗情報 -->
                            <p><strong>実店舗名:</strong> 実店舗C</p>
                                <p><strong>実店舗住所:</strong> 
                                <a href="https://www.google.com/maps/search/?q=" target="_blank" rel="noopener noreferrer">
                                    東京都第二東京区⚪︎-⚪︎-⚪︎
                                </a>
                            </p>
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

                  <label for="shop_name">アルファベット表記の会社名（必須）:</label>
                  <input type="text" name="shop_name" id="shop_name" placeholder="アルファベット表記の会社名（必須）を入力">

                  <label for="shop_name">会社名（屋号）:</label>
                  <input type="text" name="display_shop_name" id="display_shop_name" placeholder="会社名（屋号）を入力">

                  <label for="shop_name">会社住所:</label>
                  <input type="text" name="shop_address" id="shop_address" placeholder="会社住所を入力">
                  
                  <label for="desplay_real_store1">実店舗名1:</label>
                  <input type="text" name="desplay_real_store1" id="desplay_real_store1" placeholder="実店舗名1を入力">
                  
                  <label for="desplay_real_address1">実店舗の住所1:</label>
                  <input type="text" name="desplay_real_address1" id="desplay_real_address1" placeholder="住所を入力">
                  
                  <label for="desplay_real_store2">実店舗名2</label>
                  <input type="text" name="desplay_real_store2" id="desplay_real_store2" placeholder="実店舗名2を入力">
                  
                  <label for="desplay_real_address2">実店舗の住所2:</label>
                  <input type="text" name="desplay_real_address2" id="desplay_real_address2" placeholder="住所を入力">

                   <label for="desplay_real_store3">実店舗名3</label>
                  <input type="text" name="desplay_real_store3" id="desplay_real_store3" placeholder="実店舗名3を入力">
                  
                  <label for="desplay_real_address3">実店舗の住所3:</label>
                  <input type="text" name="desplay_real_address3" id="desplay_real_address3" placeholder="住所を入力">
                  
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
                    <label for="name1">商品名1:取り扱い店舗は、自動的に実店舗名1になります。</label>
                    <input type="text" name="name1" id="name1" placeholder="商品名1を入力">
                    
                    <label for="photo1">商品の写真1:</label>
                    <input type="file" name="photo1" id="photo1" multiple>
                    
                    <label for="description">商品の説明1:</label>
                    <input type="text" name="description1" id="description1" placeholder="商品1の説明を入力">
                    
                  </div>
                  
                  <!-- 商品2 -->
                  <div class="product-info">
                    <h4>商品2</h4>
                    <label for="name2">商品名2:取り扱い店舗は、自動的に実店舗名2になります。</label>
                    <input type="text" name="name2" id="name2" placeholder="商品名2を入力">
                    
                    <label for="photo2">商品の写真2:</label>
                    <input type="file" name="photo2" id="photo2" multiple>
                    
                    <label for="description2">商品の説明2:</label>
                    <input type="text" name="description2" id="description2" placeholder="商品2の説明を入力">
                    
                  </div>
                  
                  <!-- 商品3 -->
                  <div class="product-info">
                    <h4>商品3</h4>
                    <label for="name3">商品名3:取り扱い店舗は、自動的に実店舗名3になります。</label>
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
----------------------------------------------------------------
        <!DOCTYPE html>
        <html lang="ja">
        <head>
          <meta charset="UTF-8">
          <title>ドラッグ可能なフォーム</title>
          <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
          <style>
            body {
              margin: 0;
              height: 100vh;
              background-color: #f0f0f0;
            }
            .draggable {
              position: fixed;
              width: 300px;
              background: white;
              border: 1px solid #ccc;
              box-shadow: 0 2px 6px rgba(0,0,0,0.1);
              padding: 10px;
              z-index: 1000;
              cursor: default;
            }
            .drag-header {
              background: #007acc;
              color: white;
              padding: 8px;
              cursor: move;
              font-weight: bold;
            }
          </style>
        </head>
        <body>

        <div x-data="draggable()" x-init="init"
             @mousedown.window="dragStart($event)"
             @mouseup.window="dragEnd"
             @mousemove.window="dragMove"
             :style="`top: ${y}px; left: ${x}px`"
             class="draggable">

          <div class="drag-header" @mousedown="headerClicked = true">
            フォームをドラッグ
          </div>

          <!-- フォームを囲む div にスクロール設定 -->
          <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">

            <form method="POST" action="{{ route('seller.select_desplay') }}" enctype="multipart/form-data">@csrf

              <label>入力ホーム</label>
              <input type="text" class="w-full mb-2" style="width:100%; margin-bottom: 10px;"><br>

              <label>内容</label>
              <textarea style="width:100%; height: 80px;"></textarea><br><br>

              <!-- ダミーの追加項目（スクロールの動作確認用） -->
              <label>項目1</label><input type="text" style="width:100%;"><br>
              <label>項目2</label><input type="text" style="width:100%;"><br>
              <label>項目3</label><input type="text" style="width:100%;"><br>
              <label>項目4</label><input type="text" style="width:100%;"><br>
              <label>項目5</label><input type="text" style="width:100%;"><br>

              <button type="submit">保存</button>
                
                <!-- 取り扱い者情報 -->
                <div class="section">
                  <h3>取り扱い者情報 Template1と共通</h3>
                  <input type="hidden" name="seller_mail" value="{{ auth()->user()->email }}">

                  <label for="shop_name">アルファベット表記の会社名（必須）:</label>
                  <input type="text" name="shop_name" id="shop_name" placeholder="アルファベット表記の会社名（必須）を入力">

                  <label for="shop_name">会社名（屋号）:</label>
                  <input type="text" name="display_shop_name" id="display_shop_name" placeholder="会社名（屋号）を入力">

                  <label for="shop_name">会社住所:</label>
                  <input type="text" name="shop_address" id="shop_address" placeholder="会社住所を入力">
                  
                  <label for="desplay_real_store1">実店舗名1:</label>
                  <input type="text" name="desplay_real_store1" id="desplay_real_store1" placeholder="実店舗名1を入力">
                  
                  <label for="desplay_real_address1">実店舗の住所1:</label>
                  <input type="text" name="desplay_real_address1" id="desplay_real_address1" placeholder="住所を入力">
                  
                  <label for="desplay_real_store2">実店舗名2</label>
                  <input type="text" name="desplay_real_store2" id="desplay_real_store2" placeholder="実店舗名2を入力">
                  
                  <label for="desplay_real_address2">実店舗の住所2:</label>
                  <input type="text" name="desplay_real_address2" id="desplay_real_address2" placeholder="住所を入力">

                   <label for="desplay_real_store3">実店舗名3</label>
                  <input type="text" name="desplay_real_store3" id="desplay_real_store3" placeholder="実店舗名3を入力">
                  
                  <label for="desplay_real_address3">実店舗の住所3:</label>
                  <input type="text" name="desplay_real_address3" id="desplay_real_address3" placeholder="住所を入力">
                  
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
                    <label for="name1">商品名1:取り扱い店舗は、自動的に実店舗名1になります。</label>
                    <input type="text" name="name1" id="name1" placeholder="商品名1を入力">
                    
                    <label for="photo1">商品の写真1:</label>
                    <input type="file" name="photo1" id="photo1" multiple>
                    
                    <label for="description">商品の説明1:</label>
                    <input type="text" name="description1" id="description1" placeholder="商品1の説明を入力">
                  </div>
                  
                  <!-- 商品2 -->
                  <div class="product-info">
                    <h4>商品2</h4>
                    <label for="name2">商品名2:取り扱い店舗は、自動的に実店舗名2になります。</label>
                    <input type="text" name="name2" id="name2" placeholder="商品名2を入力">
                    
                    <label for="photo2">商品の写真2:</label>
                    <input type="file" name="photo2" id="photo2" multiple>
                    
                    <label for="description2">商品の説明2:</label>
                    <input type="text" name="description2" id="description2" placeholder="商品2の説明を入力">
                  </div>
                  
                  <!-- 商品3 -->
                  <div class="product-info">
                    <h4>商品3</h4>
                    <label for="name3">商品名3:取り扱い店舗は、自動的に実店舗名3になります。</label>
                    <input type="text" name="name3" id="name3" placeholder="商品名3を入力">
                    
                    <label for="photo3">商品の写真3:</label>
                    <input type="file" name="photo3" id="photo3" multiple>
                    
                    <label for="description3">商品の説明3:</label>
                    <input type="text" name="description3" id="description3" placeholder="商品3の説明を入力">
                  </div>
                </div>

            </form>

          </div>


        </div>

        <script>
        function draggable() {
          return {
            x: 100,
            y: 100,
            offsetX: 0,
            offsetY: 0,
            dragging: false,
            headerClicked: false,

            init() {
              // 初期化
            },

            dragStart(e) {
              if (!this.headerClicked) return;
              this.dragging = true;
              this.offsetX = e.clientX - this.x;
              this.offsetY = e.clientY - this.y;
              this.headerClicked = false;
            },

            dragMove(e) {
              if (!this.dragging) return;
              this.x = e.clientX - this.offsetX;
              this.y = e.clientY - this.offsetY;
            },

            dragEnd() {
              this.dragging = false;
            }
          }
        }
        </script>

        </body>
        </html>
        ーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーー

        <!-- プレビュー -->
        <div class="w-full md:w-3/4 p-4 overflow-auto h-screen">
          <section class="py-3" style="background-image: url('images/background-pattern.jpg');background-repeat: no-repeat;background-size: cover;">
            <div class="container-fluid">
              <div class="row">
                <div class="col-md-12">

                  <div class="banner-blocks">
                  
                    <div class="banner-ad large bg-info block-1">

                      <div class="swiper main-swiper">
                        <div class="swiper-wrapper">
                          
                          <div class="swiper-slide">
                            <div class="row banner-content p-5">
                              <div class="content-wrapper col-md-7">
                                <div class="categories my-3">1.キレイなカバン</div>
                                <h3 class="display-4">2.ステキな商品で本日を楽しく</h3>
                                <p>3.当店の選りすぐりの商品であなたのステキさを再生産</p>
                                <a href="#" class="btn btn-outline-dark btn-lg text-uppercase fs-6 rounded-1 px-4 py-3 mt-3">4.詳細表示</a>
                              </div>
                              <div class="img-wrapper col-md-5">
                                5.<img src="{{ asset('images/shop_site_image1.jpeg') }}" class="img-fluid">
                              </div>
                            </div>
                          </div>
                          
                          <div class="swiper-slide">
                            <div class="row banner-content p-5">
                              <div class="content-wrapper col-md-7">
                                <div class="categories mb-3 pb-3">6.キレイな服</div>
                                <h3 class="banner-title">7.ステキな商品で明日を楽しく</h3>
                                <p>8.当店の選りすぐりの商品であなたのステキさを生まれ変わらせよう</p>
                                <a href="#" class="btn btn-outline-dark btn-lg text-uppercase fs-6 rounded-1">9.詳細表示</a>
                              </div>
                              <div class="img-wrapper col-md-5">
                                10.<img src="{{ asset('images/shop_site_image6.jpeg') }}" class="img-fluid">
                              </div>
                            </div>
                          </div>
                          
                          <div class="swiper-slide">
                            <div class="row banner-content p-5">
                              <div class="content-wrapper col-md-7">
                                <div class="categories mb-3 pb-3">11.キレイな靴</div>
                                <h3 class="banner-title">12.ステキな商品で毎日を楽しく</h3>
                                <p>13.厳選された商品を手に入れ、季節を楽しもう</p>
                                <a href="#" class="btn btn-outline-dark btn-lg text-uppercase fs-6 rounded-1">14.詳細表示</a>
                              </div>
                              <div class="img-wrapper col-md-5">
                                15.<img src="{{ asset('images/shop_site_image7.jpeg') }}" class="img-fluid">
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <div class="swiper-pagination"></div>

                      </div>
                    </div>

                  </div>
                  <!-- / Banner Blocks -->
                    
                </div>
              </div>
            </div>
          </section>
          <section class="py-5 bg-light">
            <div class="container-fluid">
              <h2 class="text-center mb-4" style="color:cadetblue;">16.商品</h2>
              <div class="row justify-content-center">

                <!-- 商品カード -->
                <div class="col-6 col-md-4 col-lg-2 mb-4">
                  <div class="card h-100 text-center shadow-sm">
                    17.写真<img src="{{ asset('images/shop_site_image8.jpeg') }}" class="card-img-top" alt="商品1">
                    <div class="card-body">
                      <h5 class="card-title">18.かっこいい商品</h5>
                      <p class="card-text small">19.着心地良いかっこいい商品</p>
                      <a href="#" class="btn btn-sm btn-outline-primary">20.詳細表示</a>
                    </div>
                  </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2 mb-4">
                  <div class="card h-100 text-center shadow-sm">
                    21.写真<img src="{{ asset('images/shop_site_image1.jpeg') }}" class="card-img-top" alt="商品2">
                    <div class="card-body">
                      <h5 class="card-title">22.かわいい商品</h5>
                      <p class="card-text small">23.カワイイ安全な商品</p>
                      <a href="#" class="btn btn-sm btn-outline-primary">24.詳細表示</a>
                    </div>
                  </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2 mb-4">
                  <div class="card h-100 text-center shadow-sm">
                    25.写真<img src="{{ asset('images/shop_site_image2.jpeg') }}" class="card-img-top" alt="商品3">
                    <div class="card-body">
                      <h5 class="card-title">26.クールな商品</h5>
                      <p class="card-text small">27.着心地のいいクールな商品</p>
                      <a href="#" class="btn btn-sm btn-outline-primary">28.詳細表示</a>
                    </div>
                  </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2 mb-4">
                  <div class="card h-100 text-center shadow-sm">
                    29.写真<img src="{{ asset('images/shop_site_image3.jpeg') }}" class="card-img-top" alt="商品4">
                    <div class="card-body">
                      <h5 class="card-title">30.キュートな商品</h5>
                      <p class="card-text small">31.安全なキュートな商品</p>
                      <a href="#" class="btn btn-sm btn-outline-primary">32.詳細表示</a>
                    </div>
                  </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2 mb-4">
                  <div class="card h-100 text-center shadow-sm">
                    33.写真<img src="{{ asset('images/shop_site_image4.jpeg') }}" class="card-img-top" alt="商品5">
                    <div class="card-body">
                      <h5 class="card-title">34.高価な商品</h5>
                      <p class="card-text small">35.高価で洗練されている商品</p>
                      <a href="#" class="btn btn-sm btn-outline-primary">36.詳細表示</a>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </section>
          <footer class="py-5">
            <div class="container-fluid">
              <div class="row">
                  <div class="container">
                    <div class="row">
                      <div class="col-md-6">
                        <h5 class="mb-3">37.〇〇ショップ（店名）</h5>
                        <ul class="list-unstyled">
                          <li><strong>代表者：</strong> 38.山田 太郎</li>
                          <li><strong>所在地：</strong> 39.東京都渋谷区道玄坂1-2-3</li>
                          <li><strong>電話番号：</strong> 40.03-1234-5678</li>
                          <li><strong>Email：</strong> 41.<a href="mailto:info@example.com" class="text-white">info@example.com</a></li>
                        </ul>
                      </div>
                      <div class="col-md-6 text-md-end mt-4 mt-md-0">
                        42.<p class="mb-0">&copy; 2025 〇〇ショップ. All rights reserved.</p>
                      </div>
                    </div>
                  </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                  <div class="footer-menu">
                    <div class="social-links mt-5">
                      <ul class="d-flex list-unstyled gap-2">
                        <li>
                          <a href="#" class="btn btn-outline-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M15.12 5.32H17V2.14A26.11 26.11 0 0 0 14.26 2c-2.72 0-4.58 1.66-4.58 4.7v2.62H6.61v3.56h3.07V22h3.68v-9.12h3.06l.46-3.56h-3.52V7.05c0-1.05.28-1.73 1.76-1.73Z"/></svg>
                          </a>
                        </li>
                        <li>
                          <a href="#" class="btn btn-outline-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M22.991 3.95a1 1 0 0 0-1.51-.86a7.48 7.48 0 0 1-1.874.794a5.152 5.152 0 0 0-3.374-1.242a5.232 5.232 0 0 0-5.223 5.063a11.032 11.032 0 0 1-6.814-3.924a1.012 1.012 0 0 0-.857-.365a.999.999 0 0 0-.785.5a5.276 5.276 0 0 0-.242 4.769l-.002.001a1.041 1.041 0 0 0-.496.89a3.042 3.042 0 0 0 .027.439a5.185 5.185 0 0 0 1.568 3.312a.998.998 0 0 0-.066.77a5.204 5.204 0 0 0 2.362 2.922a7.465 7.465 0 0 1-3.59.448A1 1 0 0 0 1.45 19.3a12.942 12.942 0 0 0 7.01 2.061a12.788 12.788 0 0 0 12.465-9.363a12.822 12.822 0 0 0 .535-3.646l-.001-.2a5.77 5.77 0 0 0 1.532-4.202Zm-3.306 3.212a.995.995 0 0 0-.234.702c.01.165.009.331.009.488a10.824 10.824 0 0 1-.454 3.08a10.685 10.685 0 0 1-10.546 7.93a10.938 10.938 0 0 1-2.55-.301a9.48 9.48 0 0 0 2.942-1.564a1 1 0 0 0-.602-1.786a3.208 3.208 0 0 1-2.214-.935q.224-.042.445-.105a1 1 0 0 0-.08-1.943a3.198 3.198 0 0 1-2.25-1.726a5.3 5.3 0 0 0 .545.046a1.02 1.02 0 0 0 .984-.696a1 1 0 0 0-.4-1.137a3.196 3.196 0 0 1-1.425-2.673c0-.066.002-.133.006-.198a13.014 13.014 0 0 0 8.21 3.48a1.02 1.02 0 0 0 .817-.36a1 1 0 0 0 .206-.867a3.157 3.157 0 0 1-.087-.729a3.23 3.23 0 0 1 3.226-3.226a3.184 3.184 0 0 1 2.345 1.02a.993.993 0 0 0 .921.298a9.27 9.27 0 0 0 1.212-.322a6.681 6.681 0 0 1-1.026 1.524Z"/></svg>
                          </a>
                        </li>
                        <li>
                          <a href="#" class="btn btn-outline-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M23 9.71a8.5 8.5 0 0 0-.91-4.13a2.92 2.92 0 0 0-1.72-1A78.36 78.36 0 0 0 12 4.27a78.45 78.45 0 0 0-8.34.3a2.87 2.87 0 0 0-1.46.74c-.9.83-1 2.25-1.1 3.45a48.29 48.29 0 0 0 0 6.48a9.55 9.55 0 0 0 .3 2a3.14 3.14 0 0 0 .71 1.36a2.86 2.86 0 0 0 1.49.78a45.18 45.18 0 0 0 6.5.33c3.5.05 6.57 0 10.2-.28a2.88 2.88 0 0 0 1.53-.78a2.49 2.49 0 0 0 .61-1a10.58 10.58 0 0 0 .52-3.4c.04-.56.04-3.94.04-4.54ZM9.74 14.85V8.66l5.92 3.11c-1.66.92-3.85 1.96-5.92 3.08Z"/></svg>
                          </a>
                        </li>
                        <li>
                          <a href="#" class="btn btn-outline-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M17.34 5.46a1.2 1.2 0 1 0 1.2 1.2a1.2 1.2 0 0 0-1.2-1.2Zm4.6 2.42a7.59 7.59 0 0 0-.46-2.43a4.94 4.94 0 0 0-1.16-1.77a4.7 4.7 0 0 0-1.77-1.15a7.3 7.3 0 0 0-2.43-.47C15.06 2 14.72 2 12 2s-3.06 0-4.12.06a7.3 7.3 0 0 0-2.43.47a4.78 4.78 0 0 0-1.77 1.15a4.7 4.7 0 0 0-1.15 1.77a7.3 7.3 0 0 0-.47 2.43C2 8.94 2 9.28 2 12s0 3.06.06 4.12a7.3 7.3 0 0 0 .47 2.43a4.7 4.7 0 0 0 1.15 1.77a4.78 4.78 0 0 0 1.77 1.15a7.3 7.3 0 0 0 2.43.47C8.94 22 9.28 22 12 22s3.06 0 4.12-.06a7.3 7.3 0 0 0 2.43-.47a4.7 4.7 0 0 0 1.77-1.15a4.85 4.85 0 0 0 1.16-1.77a7.59 7.59 0 0 0 .46-2.43c0-1.06.06-1.4.06-4.12s0-3.06-.06-4.12ZM20.14 16a5.61 5.61 0 0 1-.34 1.86a3.06 3.06 0 0 1-.75 1.15a3.19 3.19 0 0 1-1.15.75a5.61 5.61 0 0 1-1.86.34c-1 .05-1.37.06-4 .06s-3 0-4-.06a5.73 5.73 0 0 1-1.94-.3a3.27 3.27 0 0 1-1.1-.75a3 3 0 0 1-.74-1.15a5.54 5.54 0 0 1-.4-1.9c0-1-.06-1.37-.06-4s0-3 .06-4a5.54 5.54 0 0 1 .35-1.9A3 3 0 0 1 5 5a3.14 3.14 0 0 1 1.1-.8A5.73 5.73 0 0 1 8 3.86c1 0 1.37-.06 4-.06s3 0 4 .06a5.61 5.61 0 0 1 1.86.34a3.06 3.06 0 0 1 1.19.8a3.06 3.06 0 0 1 .75 1.1a5.61 5.61 0 0 1 .34 1.9c.05 1 .06 1.37.06 4s-.01 3-.06 4ZM12 6.87A5.13 5.13 0 1 0 17.14 12A5.12 5.12 0 0 0 12 6.87Zm0 8.46A3.33 3.33 0 1 1 15.33 12A3.33 3.33 0 0 1 12 15.33Z"/></svg>
                          </a>
                        </li>
                        <li>
                          <a href="#" class="btn btn-outline-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M1.04 17.52q.1-.16.32-.02a21.308 21.308 0 0 0 10.88 2.9a21.524 21.524 0 0 0 7.74-1.46q.1-.04.29-.12t.27-.12a.356.356 0 0 1 .47.12q.17.24-.11.44q-.36.26-.92.6a14.99 14.99 0 0 1-3.84 1.58A16.175 16.175 0 0 1 12 22a16.017 16.017 0 0 1-5.9-1.09a16.246 16.246 0 0 1-4.98-3.07a.273.273 0 0 1-.12-.2a.215.215 0 0 1 .04-.12Zm6.02-5.7a4.036 4.036 0 0 1 .68-2.36A4.197 4.197 0 0 1 9.6 7.98a10.063 10.063 0 0 1 2.66-.66q.54-.06 1.76-.16v-.34a3.562 3.562 0 0 0-.28-1.72a1.5 1.5 0 0 0-1.32-.6h-.16a2.189 2.189 0 0 0-1.14.42a1.64 1.64 0 0 0-.62 1a.508.508 0 0 1-.4.46L7.8 6.1q-.34-.08-.34-.36a.587.587 0 0 1 .02-.14a3.834 3.834 0 0 1 1.67-2.64A6.268 6.268 0 0 1 12.26 2h.5a5.054 5.054 0 0 1 3.56 1.18a3.81 3.81 0 0 1 .37.43a3.875 3.875 0 0 1 .27.41a2.098 2.098 0 0 1 .18.52q.08.34.12.47a2.856 2.856 0 0 1 .06.56q.02.43.02.51v4.84a2.868 2.868 0 0 0 .15.95a2.475 2.475 0 0 0 .29.62q.14.19.46.61a.599.599 0 0 1 .12.32a.346.346 0 0 1-.16.28q-1.66 1.44-1.8 1.56a.557.557 0 0 1-.58.04q-.28-.24-.49-.46t-.3-.32a4.466 4.466 0 0 1-.29-.39q-.2-.29-.28-.39a4.91 4.91 0 0 1-2.2 1.52a6.038 6.038 0 0 1-1.68.2a3.505 3.505 0 0 1-2.53-.95a3.553 3.553 0 0 1-.99-2.69Zm3.44-.4a1.895 1.895 0 0 0 .39 1.25a1.294 1.294 0 0 0 1.05.47a1.022 1.022 0 0 0 .17-.02a1.022 1.022 0 0 1 .15-.02a2.033 2.033 0 0 0 1.3-1.08a3.13 3.13 0 0 0 .33-.83a3.8 3.8 0 0 0 .12-.73q.01-.28.01-.92v-.5a7.287 7.287 0 0 0-1.76.16a2.144 2.144 0 0 0-1.76 2.22Zm8.4 6.44a.626.626 0 0 1 .12-.16a3.14 3.14 0 0 1 .96-.46a6.52 6.52 0 0 1 1.48-.22a1.195 1.195 0 0 1 .38.02q.9.08 1.08.3a.655.655 0 0 1 .08.36v.14a4.56 4.56 0 0 1-.38 1.65a3.84 3.84 0 0 1-1.06 1.53a.302.302 0 0 1-.18.08a.177.177 0 0 1-.08-.02q-.12-.06-.06-.22a7.632 7.632 0 0 0 .74-2.42a.513.513 0 0 0-.08-.32q-.2-.24-1.12-.24q-.34 0-.8.04q-.5.06-.92.12a.232.232 0 0 1-.16-.04a.065.065 0 0 1-.02-.08a.153.153 0 0 1 .02-.06Z"/></svg>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                
              </div>
            </div>
          </footer>
        </div>


ーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーー

        <!-- 別のtemplate -->
        <form method="POST" action="{{ route('seller.select_desplay') }}">@csrf
            <ul>
                <li>SHOP NAME: hhh</li>
                <li>Email: fff</li>
                <li>REGISTER: ddd</li>
            </ul>
            <input type="hidden" name="desplay_id" value="4">
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