@extends('layouts.app')

@section('content')
<h2>決済がキャンセルされました</h2>
<p>再度カートをご確認ください。</p>
<a href="{{ route('pickup.cart.index') }}" class="btn btn-secondary">カートへ戻る</a>
@endsection
