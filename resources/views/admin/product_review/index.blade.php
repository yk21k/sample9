@extends('voyager::master')

@section('content')

@if(session('error'))

<div class="alert alert-danger">

	{{ session('error') }}

</div>

@endif


<div class="page-content container-fluid">

<h2>商品審査キュー</h2>

@foreach($queues as $queue)

<div class="panel panel-bordered">

<div class="panel-body">

<h3>{{ $queue->product->name }}</h3>

<p>価格 : {{ number_format($queue->product->price) }}円</p>

<img src="{{ $queue->product->cover_img ? asset('storage/'.$queue->product->cover_img) : asset('images/no_image.jpg') }}" width="200">

<img src="{{ $queue->product->cover_img2 ? asset('storage/'.$queue->product->cover_img2) : asset('images/no_image.jpg') }}" width="200">

<img src="{{ $queue->product->cover_img3 ? asset('storage/'.$queue->product->cover_img3) : asset('images/no_image.jpg') }}" width="200">

<br><br>

<a href="{{ route('product.review.show',$queue->product_id)
}}"
class="btn btn-primary">
審査する
</a>

</div>

</div>

@endforeach

</div>

@stop