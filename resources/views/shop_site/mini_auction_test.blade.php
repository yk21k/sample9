<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>商品表示タイプ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
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
    <h1>{{ $desplay->shop_name }}</h1>
    <div class="row">
      <div class="col-4">
        <div class="list-group" id="list-tab" role="tablist">
          <a class="list-group-item list-group-item-action active" id="list-home-list" data-bs-toggle="list" href="#list-home" role="tab" aria-controls="list-home">{{ $desplay->name1 }}</a>
          <a class="list-group-item list-group-item-action" id="list-profile-list" data-bs-toggle="list" href="#list-profile" role="tab" aria-controls="list-profile">{{ $desplay->name2 }}</a>
          <a class="list-group-item list-group-item-action" id="list-messages-list" data-bs-toggle="list" href="#list-messages" role="tab" aria-controls="list-messages">{{ $desplay->name3 }}</a>
          <a class="list-group-item list-group-item-action" id="list-settings-list" data-bs-toggle="list" href="#list-settings" role="tab" aria-controls="list-settings">PR</a>
        </div>
      </div>
      <div class="col-8">
        <div class="tab-content" id="nav-tabContent">
          <div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
                <!-- 商品情報 -->
                <div class="auction-item">
                  <img src="{{ asset('storage/img/'.$desplay->shop_name.'/'.$desplay->photo1) }}" alt="商品画像" class="item-image">
                  <div class="item-details">
                    <h2 class="item-title">{{ $desplay->name1 }}</h2>
                    <p class="item-description">{{ $desplay->description1 }}</p>
                    
                    <!-- 商品詳細 -->
                    <p><strong>商品名:</strong> {{ $desplay->name1 }}</p>
                    <p><strong>ショップ名:</strong> {{ $desplay->shop_name }}</p>
                    <p><strong>URL:</strong> <a href="https://example.com" target="_blank">{{ $desplay->url }}</a></p>
                    <p><strong>住所:</strong> 東京都新宿区1-2-3</p>
                    <p><strong>メールアドレス:</strong> {{ $desplay->desplay_mail }}</p>
                    <p><strong>電話番号:</strong> {{ $desplay->desplay_phone }}</p>
                    
                  </div>
                </div>

          </div>

          <div class="tab-pane fade" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
                <!-- 商品情報 -->
                <div class="auction-item">
                  <img src="{{ asset('storage/img/'.$desplay->shop_name.'/'.$desplay->photo2) }}" alt="商品画像" class="item-image">
                  <div class="item-details">
                    <h2 class="item-title">{{ $desplay->name2 }}</h2>
                    <p class="item-description">{{ $desplay->description2 }}</p>
                    
                    <!-- 商品詳細 -->
                    <p><strong>商品名:</strong> {{ $desplay->name2 }}</p>
                    <p><strong>ショップ名:</strong> {{ $desplay->shop_name }}</p>
                    <p><strong>URL:</strong> <a href="https://example.com" target="_blank">{{ $desplay->url }}</a></p>
                    <p><strong>住所:</strong> 東京都新宿区2-2-3</p>
                    <p><strong>メールアドレス:</strong> {{ $desplay->desplay_mail }}</p>
                    <p><strong>電話番号:</strong> {{ $desplay->desplay_phone }}</p>
                    
                    

                  </div>
                </div>

                 
          </div>

          <div class="tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-messages-list">
                <!-- 商品情報 -->
                <div class="auction-item">
                  <img src="{{ asset('storage/img/'.$desplay->shop_name.'/'.$desplay->photo3) }}" alt="商品画像" class="item-image">
                  <div class="item-details">
                    <h2 class="item-title">{{ $desplay->name3 }}</h2>
                    <p class="item-description">{{ $desplay->description3 }}</p>
                    
                    <!-- 商品詳細 -->
                    <p><strong>商品名:</strong> {{ $desplay->name1 }}</p>
                    <p><strong>ショップ名:</strong> {{ $desplay->shop_name }}</p>
                    <p><strong>URL:</strong> <a href="https://example.com" target="_blank">{{ $desplay->url }}</a></p>
                    <p><strong>住所:</strong> 東京都新宿区1-2-3</p>
                    <p><strong>メールアドレス:</strong> {{ $desplay->desplay_mail }}</p>
                    <p><strong>電話番号:</strong> {{ $desplay->desplay_phone }}</p>
                    
                  </div>
                </div>

                
          </div>
          <div class="tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-settings-list">その他のご案内　PR
              <p><strong>PR:</strong> {{ $desplay->desplay_pr }}</p>
              <!-- 実店舗情報 -->
              <p><strong>実店舗名:</strong> {{ $desplay->desplay_real_store1 }}</p>
              <p><strong>実店舗住所:</strong> {{ $desplay->desplay_real_address1 }}</p>
              <!-- 実店舗情報 -->
              <p><strong>実店舗名:</strong> {{ $desplay->desplay_real_store2 }}</p>
              <p><strong>実店舗住所:</strong> {{ $desplay->desplay_real_address2 }}</p>
              <!-- 実店舗情報 -->
              <p><strong>実店舗名:</strong> {{ $desplay->desplay_real_store3 }}</p>
              <p><strong>実店舗住所:</strong> {{ $desplay->desplay_real_address3 }}</p>
          </div>

        </div>
      </div>
    </div>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>
