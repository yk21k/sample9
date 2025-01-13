        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>店舗情報 - {{ $desplay->shop_name }}</title>
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

                .display-shop{
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

        <header class="display-shop" style="background-image: url({{ asset('image/_46870c8f-f444-4ba0-a61f-058d489cb703.jpeg') }});">
            <h1>{{ $desplay->shop_name }}</h1>
            <p>ご来店いただきありがとうございます！</p>
        </header>

        <div class="display-shop1" style="background-image: url({{ asset('image/_46870c8f-f444-4ba0-a61f-058d489cb703.jpeg') }});">
            <div class="display-shop-text1">
                <h2>店舗情報</h2>
                <ul class="info">
                    <li><strong>商圏:</strong> {{ $desplay->area }}</li>
                    <li><strong>店名:</strong> {{ $desplay->desplay_real_store1 }}</li>
                    <li><strong>住所:</strong> {{ $desplay->desplay_real_address1  }}</li>
                    <li><strong>URL:</strong> <a href="{{ $desplay->url }}" target="_blank">{{ $desplay->url }}</a></li>
                    <li><strong>電話番号:</strong> <span class="contact">{{ $desplay->desplay_phone }}</span></li>
                    
                </ul>
            </div>    
        </div>

        </body>