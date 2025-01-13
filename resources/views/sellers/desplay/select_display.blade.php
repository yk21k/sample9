@extends('layouts.seller')

@section('content')

<h2>Desplay Select Form</h2>
@if($check_desplay_count == 0)

    <form method="POST" action="{{ route('seller.select_desplay') }}">@csrf
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
            <input type="hidden" name="desplay_id" value=1>
            <label>
                Shop Name:
            </label>
            <input type="text" name="shop_name">
            <label>
                Phone:
            </label>
            <input type="" name="desplay_phone">
            <label>
                URL:
            </label>
            <input type="" name="url">
            <label>
                Real Shop Name1:
            </label>
            <input type="text" name="desplay_real_store1">
            <label>
                Real Shop Address1:
            </label>
            <input type="text" name="desplay_real_address1">
            <label>
                Real Shop Name2:
            </label>
            <input type="text" name="desplay_real_store2">
            <label>
                Real Shop Address2:
            </label>
            <input type="text" name="desplay_real_address2">
            <label>
                Commercial Area:
            </label>
            <input type="text" name="desplay_area">

            <button type="submit" class="btn btn-primary mb-2 mr-2">Submit</button>
    </form>

    <form method="POST" action="{{ route('seller.select_desplay') }}">@csrf
        <ul>
            <li>SHOP NAME: hhh</li>
            <li>Email: fff</li>
            <li>REGISTER: ddd</li>
        </ul>
        <input type="hidden" name="desplay_id" value="2">
        <button type="submit" class="btn btn-primary mb-2 mr-2">Submit</button>
    </form>
    
@else
    Thank you for Choosing!!
    <form method="POST" action="{{ route('seller.delete_desplay') }}">@csrf
        <button type="submit" class="btn btn-primary mb-2 mr-2">Delete Page</button>
            
    </form>
@endif
@endsection