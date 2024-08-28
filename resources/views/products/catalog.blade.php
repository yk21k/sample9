@extends('layouts.app')

@section('content')
aaaaa
	<div class="py-4 container">
	<h2>Products Found :: {{ $products->total() }}</h2>
    <div>
        {{ $products->appends(['query'=>request('query')])->render() }} 
        <!-- {{ $products->links('pagination::bootstrap-4') }}     -->
    </div>	

	@foreach($products as $product)

        @include('products._single_product')


	@endforeach
	</div>
	

@endsection