@extends('layouts.app')

@section('content')

@if(isset($query))
    <h2>Products Found :: {{ $products->total() }}</h2>
    <div>

        {{ $products->appends(['query'=>request('query')])->render() }} 

    </div>
@endif

<h2>{{ $categoryName ?? null }}  Products</h2>  

@if(empty($products))
    <p>Nothing</p>
@endif

<div class="row" style="margin-right: 20%;"> 

@php
    $tax_rate = App\Models\TaxRate::current()?->rate;
@endphp

@forelse($products as $product) 

<div class="col-4" >
    
    <div class="card">
        @if(isset($product->cover_img) && !empty($product->cover_img))
              
            <img class="card-img-top" src="{{ asset( 'storage/'.$product->cover_img ) }}" alt="Card image cap">

        @else

            <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">

        @endif
        <div class="card-body">
            <h4 class="card-title">{{$product->name}}</h4>
            <p class="card-text">{{$product->description}}</p>
            <h3>$ {{ floor(($product->price+$product->shipping_fee)*($tax_rate+1)) }}</h3>
            <h3> {{ $product->shop->name ?? 'Sample9' }}</h3>
            @if($product->shop->invoice_number)
                <span class="badge bg-danger ms-2">課税事業者</span>
            @else
                <span class="badge bg-success ms-2">免税事業者</span>
            @endif 
        </div>
        
        <div class="card-body">
            <a href="{{ route('cart.add', $product->id) }}" class="card-link">Add to cart</a>
        </div>
    </div>
    
</div>
@empty
        <p>Nothing</p>
@endforelse


</div>
@endsection
