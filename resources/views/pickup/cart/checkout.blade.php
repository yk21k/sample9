@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h4 mb-4">カート確認（店舗受け取り）</h1>

    @if($cart->isEmpty())
        <div class="alert alert-info">カートに商品がありません。</div>
    @else
        <form id="payment-form" action="{{ route('store-pickup.payment.create') }}" method="POST">
            @csrf

            {{-- 店舗ごとにグループ化 --}}
            @foreach($cart->groupBy('shop_id') as $shopId => $shopItems)
                @php
                    // 店舗情報取得
                    $firstItem = $shopItems->first();

                    // product, shop は Eloquent モデルであるため、オブジェクトとして扱う
                    $shop = $firstItem['product']->shop ?? null;

                    // 店舗に紐づく受け取り場所を取得
                    $locations = $shop ? $shop->pickupLocations : collect();

                    // 店舗名を取得
                    $shopName = $shop?->name ?? '店舗情報なし';

                    // すでに選択済みのpickup_location_id（変更不可対応）
                    $selectedLocationId = $firstItem['pickup_location_id'] ?? null;
                @endphp
                <div class="card mb-4 p-3 border-primary">
                    <h5 class="mb-3">{{ $shopName }}</h5>

                    {{-- 商品一覧 --}}
                    <ul class="list-group mb-3">
                        @foreach($shopItems as $item)
                            @php
                                $product = $item['product'];
                                $isTaxable = !empty($shop['invoice_number']);
                                $taxRate = \App\Models\TaxRate::current()?->rate ?? 0;
                                $unitPrice = $isTaxable
                                    ? round($product['price'] * (1 + $taxRate))
                                    : $product['price'];
                            @endphp

                            <li class="list-group-item d-flex justify-content-between align-items-center cart-item" data-cart-id="{{ $item['id'] }}" data-product-id="{{ $item['product']['id'] }}">
                                <div>
                                    {{ $product['name'] }}
                                    @if($isTaxable)
                                        <span class="badge bg-success ms-2">課税</span>
                                    @else
                                        <span class="badge bg-warning text-dark ms-2">非課税</span>
                                    @endif
                                </div>
                                <span>¥{{ number_format($unitPrice) }}</span>
                            
                                {{-- hidden 商品データ --}}
                                <input type="hidden" name="items[{{ $shopId }}][{{ $loop->index }}][product_id]" value="{{ $product['id'] }}">
                                <input type="hidden" name="items[{{ $shopId }}][{{ $loop->index }}][shop_id]" value="{{ $shopId }}">
                                <input type="hidden" name="items[{{ $shopId }}][{{ $loop->index }}][price]" value="{{ $unitPrice }}">
                                <input type="hidden" name="items[{{ $shopId }}][{{ $loop->index }}][quantity]" value="{{ $item['quantity'] }}">

                            
                                {{-- hidden input を cartId ベースに --}}
                                <input type="hidden" id="pickup_date_{{ $item['id'] }}" 
                                       value="{{ $item['pickup_date'] ?? old('pickup_date_' . $item['id'], now()->format('Y-m-d')) }}">
                                       
                                <input type="hidden" id="pickup_time_{{ $item['id'] }}" 
                                       value="{{ $item['pickup_time'] ?? old('pickup_time_' . $item['id'], '12:00') }}">

                                <input type="hidden" id="pickup_location_id_{{ $item['id'] }}" 
                                       value="{{ $item['pickup_location_id'] ?? '' }}">

                                <input type="hidden" id="pickup_slot_id_{{ $item['id'] }}" 
                                       value="{{ $item['pickup_slot_id'] ?? '' }}">       
                            </li>

                        @endforeach
                    </ul>

                    {{-- 受け取り場所（選択済み・変更不可） --}}
                    @if(!empty($locations))
                        @foreach($locations as $location)
                            @if($selectedLocationId == $location['id'])
                                <div class="card mb-2 p-2 border-secondary">
                                    <p class="fw-bold mb-1">{{ $location['name'] }}</p>
                                    <p class="mb-1">{{ $location['address'] }}</p>
                                    @if($location['phone'])
                                        <p class="mb-1">TEL: {{ $location['phone'] }}</p>
                                    @endif

                                    {{-- YouTube埋め込み --}}
                                    @php
                                        $youtubeId = null;
                                        if (!empty($location['youtube_url'])) {
                                            if (preg_match('/youtu\.be\/([\w\-]{11})/', $location['youtube_url'], $matches)) {
                                                $youtubeId = $matches[1];
                                            } elseif (preg_match('/v=([\w\-]{11})/', $location['youtube_url'], $matches)) {
                                                $youtubeId = $matches[1];
                                            }
                                        }
                                    @endphp

                                    @if($youtubeId)
                                        <div class="ratio ratio-16x9 mt-2">
                                            <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" allowfullscreen></iframe>
                                        </div>
                                    @endif

                                    {{-- hidden 選択済み受け取り場所 --}}
                                    <input type="hidden" name="pickup_location[{{ $shopId }}]" value="{{ $location['id'] }}">
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            @endforeach

            {{-- Stripe Elements カード情報 --}}
            <div class="card mb-4 p-3 border-secondary">
                <h5 class="mb-3">カード情報を入力してください</h5>
                <div id="card-element" class="form-control"></div>
                <div id="card-errors" role="alert" class="text-danger mt-2"></div>
            </div>

            {{-- Hidden --}}
            <input type="hidden" name="type" value="3"> {{-- 店舗受取 --}}
            <input type="hidden" name="amount" value="{{ $total }}">
            <input type="hidden" name="cart" value="{{ encrypt(json_encode($cart)) }}">


            {{-- 決済ボタン --}}
            <div class="text-end">
                <button id="submit" class="btn btn-primary btn-lg">Stripeで支払う</button>
            </div>   

        </form>
    @endif

</div>

{{-- Stripe --}}


{{-- Stripe --}}
<script src="https://js.stripe.com/v3/"></script>



<script>
    const stripe = Stripe(@json(config('services.stripe.key')));
    const elements = stripe.elements();
    const card = elements.create("card", { hidePostalCode: true });
    card.mount("#card-element");

    card.on('change', (event) => {
        document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
    });

    document.getElementById('payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            // --- ① pickup_info 作成 ---
            const pickupInfoArray = Array.from(document.querySelectorAll('.cart-item')).map(el => {
                const cartId = el.dataset.cartId;
                return {
                    cart_id: cartId,
                    pickup_date: document.getElementById(`pickup_date_${cartId}`).value,
                    pickup_time: document.getElementById(`pickup_time_${cartId}`).value,
                    pickup_location_id: document.getElementById(`pickup_location_id_${cartId}`)?.value || null,
                    pickup_slot_id: document.getElementById(`pickup_slot_id_${cartId}`)?.value || null
                };
            });

            // ✅ cart_idをキーにした連想配列へ変換
            const pickupInfo = Object.fromEntries(
                pickupInfoArray.map(info => [info.cart_id, info])
            );

            // --- ✅ ② 在庫チェック（新規追加部分） ---
            const checkResponse = await fetch("{{ route('store-pickup.check-stock') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ cart: @json($cart), })
            });

            const checkData = await checkResponse.json();

            if (!checkResponse.ok || !checkData.success) {
                alert(checkData.message || '在庫確認でエラーが発生しました。');
                return; // ❌ 在庫NGなら決済ストップ
            }

            // --- ③ PaymentIntent 作成 ---
            const res = await fetch("{{ route('store-pickup.payment.create') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    cart: @json($cart),
                    type: 3,
                    pickup_info: pickupInfo
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'PaymentIntent 作成エラー');

            // --- ④ Stripe 決済 ---
            const result = await stripe.confirmCardPayment(data.clientSecret, {
                payment_method: { card: card }
            });

            if (result.error) {
                document.getElementById('card-errors').textContent = result.error.message;
                console.error('Stripeエラー', result.error.message);
                return;
            }

            if (result.paymentIntent.status === 'succeeded') {

                // cartId ベースで pickup_date / pickup_time を取得
                const updatedCart = Array.from(document.querySelectorAll('.cart-item')).map($item => {
                    const cartId = $item.dataset.cartId;
                    return {
                        id: cartId,
                        product_id: $item.dataset.productId,
                        pickup_date: document.getElementById(`pickup_date_${cartId}`).value,
                        pickup_time: document.getElementById(`pickup_time_${cartId}`).value,
                    };
                });

                // 注文作成 API へ送信
                const orderResponse = await fetch("{{ route('store-pickup.order.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify
                    ({
                        payment_intent_id: result.paymentIntent.id,
                        pickup_info: updatedCart
                    })
                });
                console.log('🧾 決 pickup_info:', updatedCart);
                console.log(sessionStorage);
                console.log(JSON.stringify(sessionStorage));
                // 決済成功後リダイレクト
                window.location.href = "{{ route('store-pickup.payment.success') }}";
            }

        } catch (err) {
            console.error('決済フローエラー', err);
            document.getElementById('card-errors').textContent = err.message;
        }
    });
</script>

@endsection
