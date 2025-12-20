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

<div class="max-w-3xl mx-auto p-4">
    <div class="flex flex-col md:flex-row gap-4">
        <div class="md:w-1/2">
            <h1 class="text-2xl font-bold mb-2">{{ $pickupProduct->name }}</h1>
            
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img1 ? asset('storage/'.$pickupProduct->cover_img1) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img2 ? asset('storage/'.$pickupProduct->cover_img2) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img3 ? asset('storage/'.$pickupProduct->cover_img3) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img4 ? asset('storage/'.$pickupProduct->cover_img4) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img5 ? asset('storage/'.$pickupProduct->cover_img5) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img6 ? asset('storage/'.$pickupProduct->cover_img6) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img7 ? asset('storage/'.$pickupProduct->cover_img7) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img8 ? asset('storage/'.$pickupProduct->cover_img8) : asset('images/no_image.jpg') }}">&nbsp;
            <img class="w-full" width="300" height="300" src="{{ $pickupProduct->cover_img9 ? asset('storage/'.$pickupProduct->cover_img9) : asset('images/no_image.jpg') }}">&nbsp;
            <h2 class="font-bold text-lg">{{ $pickupProduct->name }}</h2>  
        </div>

        <div class="md:w-1/2">
            <p class="text-gray-700 mb-4">{{ $pickupProduct->description }}</p>
            <p class="text-xl font-semibold mb-4">¥{{ number_format($pickupProduct->price) }}</p>

            
            <form action="{{ route('pickup.cart.add', ['id' => $pickupProduct->id]) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-info">
                    受け取り予約カートに追加
                </button>
            </form>



            <a href="{{ route('pickup.catalog.index') }}" class="text-blue-500 underline block mt-4">
                ← 商品一覧に戻る
            </a>
        </div>
    </div>
</div>

@endsection
