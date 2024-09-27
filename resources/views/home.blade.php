@extends('layouts.app')

@section('content')

<!-- Side navigation -->
<div class="wrapper">
    @include('layouts.sidebar')
</div>  

<div class="container">

    <h2>Products AAA</h2>


    <div class="row">
        @foreach($allProducts as $product)
            
            <div class="col-4">
                <div class="card">
                    <a class="" href="{{ route('products.detail', ['id'=>$product->id]) }}">

                    @if(isset($product->cover_img) && !empty($product->cover_img))
                        <img class="card-img-top" src="{{ asset( 'storage/'.$product->cover_img ) }}" alt="Card image cap">               
                    @else
                        <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">
                    @endif
                    </a>

                    <div class="card-body">

                        <h4 class="card-title">{{ $product->name }}</h4>
                        {{ $product->description }}
                        <h4 class="card-title"> ${{ $product->price }} </h4>
                        <h4 class="card-title"> Stock : {{ $product->stock }} </h4>
                        @foreach($product_attributes as $attr)
                            @foreach ($attr->values as $val)
                                @if((!empty(json_decode($product->product_attributes,true)[$attr->name]) && json_decode($product->product_attributes,true)[$attr->name] == $val->value))
                            
                                <h4>{{$attr->name}} : {{$val->value}}</h4>
                                @endif
                            @endforeach
                        @endforeach
                            
                        <h4 class="card-title"> {{ $product->shop->name }} </h4>

                        <a class="" href="{{ route('inquiries.create', ['id'=>$product->shop->id]) }}"><h4>Contact Shop Manager</h4></a>


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
