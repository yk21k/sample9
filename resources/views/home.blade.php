@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Products</h2>

    <div class="row">
        @foreach($allProducts as $product)
            <div class="col-4">
                <div class="card">
                    <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">
                    <div class="card-body">
                        <h4 class="card-title">{{ $product->name }}</h4>
                        <p class="card-text">{{ $product->description }}</p>
                        <h4 class="card-title"> ${{ $product->price }} </h4>

                    </div>
                    <div class="card-body">
                        <a href="{{ route('cart.add', $product->id) }}" class="card-link">Add to Cart</a>
                    </div>
                </div>
            </div>   
        @endforeach
    </div>

</div>
@endsection
