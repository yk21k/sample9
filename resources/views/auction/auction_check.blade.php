Auction Check
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>商品購入ページ</h3>
    <p>「テスト商品」を購入します（1000円）</p>
    
    <form method="POST" action="">
        @csrf
        <button type="submit" class="btn btn-primary">購入する</button>
    </form>
</div>
@endsection