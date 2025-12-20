@extends('layouts.app')

@section('content')
<div class="container py-5">
  <h2>店舗スタッフダッシュボード</h2>
  <p>{{ Auth::guard('shop_staff')->user()->name }} さん、ようこそ。</p>

  <p class="mt-3">購入者が提示する6桁のワンタイムパスワード（OTP）を入力してください。</p>

  {{-- OTP入力フォーム --}}
  <form method="POST" action="{{ route('shop.verifyOtp') }}" class="mt-4">
    @csrf
    <div class="mb-3" style="max-width: 250px;">
      <input type="text" 
             name="otp_code" 
             class="form-control text-center" 
             maxlength="6" 
             required 
             placeholder="例: ABC123">
      @error('otp_code')
        <div class="text-danger small mt-2">{{ $message }}</div>
      @enderror
    </div>
    <button type="submit" class="btn btn-primary">確認する</button>
  </form>

  {{-- ✅ 本人確認後に注文情報を表示 --}}
  @if(isset($verified) && $verified)
    <div class="mt-5 border p-4 rounded bg-gray">
      <h4>✅ 本人確認完了</h4>
      <p><strong>注文番号:</strong> {{ $order->id }}</p>

      {{-- 商品ごとの情報 --}}
      @foreach ($order->items as $item)
        {{-- ログイン中スタッフの店舗の商品だけ表示 --}}
        @if(Auth::guard('shop_staff')->user()->shop_id === ($item->product->shop->id ?? null))
          <div class="border rounded p-3 my-3 bg-gray shadow-sm">
            <p>
              <strong>商品名:</strong> {{ $item->product->name ?? '不明な商品' }}<br>
              <strong>店舗名:</strong> {{ $item->product->shop->name ?? '不明な店舗' }}
            </p>

            @php
              $reservation = $order->reservations->firstWhere('pickup_order_item_id', $item->id);
              $slotFromReservation = $reservation?->slot;

              // 受取日の Carbon インスタンス
              $pickupDate = $slotFromReservation?->date ? \Carbon\Carbon::parse($slotFromReservation->date) : null;

              // 受取日の 1週間後
              $afterOneWeek = $pickupDate ? $pickupDate->copy()->addWeek() : null;
            @endphp
            @if($slotFromReservation)

              <p>
                <strong>予定受取スロット:</strong>
                {{ optional($slotFromReservation->date)->format('Y/m/d') ?? '未設定' }}
                {{ $slotFromReservation->start_time }}〜{{ $slotFromReservation->end_time }}
              </p>

              {{-- ⚠ 当日でない場合の注意喚起 --}}
              @if($slotFromReservation->date && \Carbon\Carbon::parse($slotFromReservation->date)->isSameDay(\Carbon\Carbon::today()) === false)
                <div class="alert alert-warning mt-2">
                  ⚠ この受け取りスロットは <strong>本日（{{ now()->format('Y/m/d') }}）</strong> ではありません。
                </div>
              @endif

              @if($pickupDate && now()->greaterThan($afterOneWeek))
                  <div class="alert alert-danger mt-2">
                      ⚠ この受取スロットは <strong>1週間以上経過</strong> しています。<br>
                      受け渡しは<strong>店舗様の判断</strong>でお願いいたします。
                  </div>
              @endif
              
              {{-- ✅ 受け渡しボタン --}}
              @if($item->status === 'pending')
                <form action="{{ route('staff.order.person_in_charge', ['item' => $item->id]) }}" method="POST" class="mt-3">
                  @csrf
                  <button type="submit" class="btn btn-success">
                    私、（{{ auth()->user()->name }}）が受け渡しました
                  </button>
                </form>
              @elseif($item->status === 'pending_confirmation')
                <span class="badge bg-secondary mt-3">
                  受取確認済です、猶予期間後、送金依頼して下さい。　送金依頼は、別途、管理者で行います。
                </span> 
              @elseif($item->status === 'picked_up')
                <span class="badge bg-secondary mt-3">
                  店頭で{{ $item->person_in_charge }}さんが受渡ました。
                </span>
              @else
                <span class="badge bg-secondary mt-3">
                  購入者がまだ受取確認をしていません
                </span>
              @endif        

            @endif
          </div>
        @endif
      @endforeach

      <p><strong>受取人:</strong> {{ $order->user->name ?? '不明なユーザー' }}</p>
      <p><strong>受取確認日時:</strong> {{ now()->format('Y/m/d H:i') }}</p>
    </div>
  @endif

  {{-- ログアウト --}}
  <div class="mt-4">
    <form id="logout-form" action="{{ route('shop_staff.logout') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-danger">ログアウト</button>
    </form>
  </div>
</div>
@endsection
