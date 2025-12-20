@extends('layouts.app')

@section('content')
<div class="container py-5">
  <h2>商品受取確認フォーム</h2>
  <p>以下の商品について、受取確認を行ってください。</p>

  <div class="border p-3 mb-4">
    <p><strong>商品名:</strong> {{ $item->product->name ?? '商品情報なし' }}</p>
    <p><strong>注文番号:</strong> {{ $item->order->id ?? '不明' }}</p>
  </div>
  <form method="POST" action="{{ route('pickup.confirm.submit.item', $item->id) }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">
        <input type="checkbox" name="confirmed" value="1" required>
        上記の商品を受け取りました。
      </label>
    </div>

    <button type="submit" class="btn btn-primary">送信</button>
  </form>
</div>
@endsection
