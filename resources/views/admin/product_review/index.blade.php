@extends('voyager::master')

@section('content')

<div class="container">

<h2>商品審査</h2>

<table class="table">

<thead>
<tr>
<th>ID</th>
<th>商品名</th>
<th>価格</th>
<th>操作</th>
</tr>
</thead>

<tbody>

@foreach($products as $product)

<tr>

<td>{{ $product->id }}</td>
<td>{{ $product->name }}</td>
<td>{{ $product->price }}</td>

<td>

<form method="POST"
action="{{ route('product.approve',$product) }}">
@csrf
<button class="btn btn-success">
承認
</button>
</form>

<form method="POST"
action="{{ route('product.reject',$product) }}">
@csrf

<input type="text"
name="review_comment"
placeholder="否認理由">

<button class="btn btn-danger">
否認
</button>

</form>

</td>

</tr>

@endforeach

</tbody>
</table>

</div>

@endsection