@extends('layouts.app')

@section('content')

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
  <div class="alert alert-info">{{ session('info') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="container py-5">
  <h2 class="mb-4">商品受取確認フォーム</h2>
  <p>ご来店ありがとうございました。以下の内容をご確認ください。</p>

  @foreach ($orders as $order)
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-gray">
        <strong>注文番号:</strong> {{ $order->id }}
        <span class="text-muted float-end">{{ $order->created_at->format('Y/m/d H:i') }}</span>
      </div>

      <div class="card-body">
        <h5 class="mb-3">商品一覧</h5>
        <ul class="list-group mb-4">

          @foreach ($order->items as $item)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ $item->product->name ?? '商品名不明' }}</strong><br>
                <small class="text-muted">ステータス: {{ $item->status }}</small>
              </div>

              @if($item->status === 'pending' || $item->status === 'picked_up')
                <form method="POST" action="{{ route('pickup.item.receive', $item->id) }}">
                  @csrf
                  <button type="submit" class="btn btn-success btn-sm">
                    受取確認
                  </button>
                </form>
              @elseif($item->status === 'pending_confirmation')
                <span class="badge bg-secondary">
                  受取確認のお待ち時間経過しました。受取済に変更されました（{{ $order->otp->expires_at->format('Y/m/d H:i') }})

                </span>
              @else
                <span class="badge bg-secondary">
                  受取済（{{ $order->otp->expires_at->format('Y/m/d H:i') }})

                </span>
              @endif
            </li>
          @endforeach

        </ul>

        <div class="border-top pt-3">
          <h6 class="fw-bold">OTP情報</h6>

          @if($order->otp && $order->otp->expires_at->isFuture())
            {{-- ✅ 有効なOTPが存在する場合 --}}
            <p class="mb-1">OTPコード：<strong>{{ $order->otp->code }}</strong></p>
            <p class="text-muted mb-2">有効期限：{{ $order->otp->expires_at->format('Y/m/d H:i') }}</p>
          @else
            {{-- ❌ OTPが存在しない or 有効期限切れ --}}
            <form method="POST" action="{{ route('pickup.otp.generate') }}">
              @csrf
              <input type="hidden" name="order_id" value="{{ $order->id }}">
              <button type="submit" class="btn btn-primary btn-sm">
                OTPを発行する
              </button>
            </form>
          @endif

        </div>
        <form method="GET" action="{{ route('pickup.otp.login.form') }}">
          @csrf
          <button type="submit" class="btn btn-success btn-sm">
            ワンタイムパスでログインする
          </button>
        </form>
      </div>
    </div>
  @endforeach
</div>
@endsection
