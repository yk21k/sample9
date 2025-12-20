@extends('layouts.app')

@section('content')

<div class="container py-5">
  <h2>商品受取確認フォーム</h2>
  <p>ご来店ありがとうございました。以下の内容をご確認ください。</p>

  @foreach ($orders as $order)
	  <div class="border rounded p-3 mb-3">
	    <p><strong>注文番号:</strong> {{ $order->id }}</p>
	    <p><strong>ステータス:</strong> {{ $order->status }}</p>
	    @if($order->status !== 'received')
	      <a href="{{ route('pickup.confirm.form.item', ['id' => $order->id]) }}" class="btn btn-success btn-sm">
	        受取確認フォームへ
	      </a>
	    @else
	      <span class="badge bg-secondary">受取済</span>
	    @endif
	  </div>
  @endforeach


</div>


@endsection