@extends('layouts.app')

@section('content')


@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="max-w-5xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">店舗受け取り 可能な商品一覧</h1>

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="商品名で検索"
               class="border rounded p-2 w-full">
        <select name="sort" class="border rounded p-2">
            <option value="">並び替え</option>
            <option value="price_asc" @selected(request('sort')==='price_asc')>価格の安い順</option>
            <option value="price_desc" @selected(request('sort')==='price_desc')>価格の高い順</option>
        </select>
        <button class="bg-blue-500 text-white px-4 py-2 rounded">検索</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($products as $product)
            @php
                $tax_rate = App\Models\TaxRate::current()?->rate;
                $shop = App\Models\Shop::where('id', $product['shop_id'])->first();
                $slots = App\Models\PickupSlot::where('pickup_product_id', $product['id'])->first();

            @endphp
            @if($shop->invoice_number)
                <div class="border rounded shadow hover:shadow-md transition p-2">
                    @if($product->stock > 0)
                        <a href="{{ route('pickup.catalog.show', $product) }}">
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img1 ? asset('storage/'.$product->cover_img1) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img2 ? asset('storage/'.$product->cover_img2) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img3 ? asset('storage/'.$product->cover_img3) : asset('images/no_image.jpg') }}">&nbsp;
                            <h2 class="font-bold text-lg">{{ $product->name }}</h2>  
                        </a>
                        <p class="text-gray-700">{{ $product->description }}</p>
                        <p class="text-gray-700">¥{{ number_format($product->price*(1+$tax_rate)) }}</p>
                        <span class="badge bg-danger ms-2">課税事業者</span>    
                    @elseif($product->stock === 0)
                        <a>
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img1 ? asset('storage/'.$product->cover_img1) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img2 ? asset('storage/'.$product->cover_img2) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img3 ? asset('storage/'.$product->cover_img3) : asset('images/no_image.jpg') }}">&nbsp;
                            <h2 class="font-bold text-lg">{{ $product->name }}</h2>
                            在庫がありません。  
                        </a>
                        <p class="text-gray-700">{{ $product->description }}</p>
                        <p class="text-gray-700">¥{{ number_format($product->price*(1+$tax_rate)) }}</p>
                        <span class="badge bg-danger ms-2">課税事業者</span>    
                    @endif    
                </div>    
            @else
                <div class="border rounded shadow hover:shadow-md transition p-2">
                    @if($product->stock > 0)
                        <a href="{{ route('pickup.catalog.show', $product) }}">
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img1 ? asset('storage/'.$product->cover_img1) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img2 ? asset('storage/'.$product->cover_img2) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img3 ? asset('storage/'.$product->cover_img3) : asset('images/no_image.jpg') }}">&nbsp;
                            <h2 class="font-bold text-lg">{{ $product->name }}</h2>  
                        </a>
                        <p class="text-gray-700">{{ $product->description }}</p>
                        <p class="text-gray-700">¥{{ number_format($product->price) }}</p>
                        <span class="badge bg-success ms-2">免税事業者</span>
                    @elseif($product->stock === 0)
                        <a>
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img1 ? asset('storage/'.$product->cover_img1) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img2 ? asset('storage/'.$product->cover_img2) : asset('images/no_image.jpg') }}">&nbsp;
                            <img class="w-full" width="300" height="300" src="{{ $product->cover_img3 ? asset('storage/'.$product->cover_img3) : asset('images/no_image.jpg') }}">&nbsp;
                            <h2 class="font-bold text-lg">{{ $product->name }}</h2>
                            在庫がありません。  
                        </a>
                        <p class="text-gray-700">{{ $product->description }}</p>
                        <p class="text-gray-700">¥{{ number_format($product->price) }}</p>
                        <span class="badge bg-success ms-2">免税事業者</span>  
                    @endif
                </div>
            @endif    
        @endforeach
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>

@endsection