@extends('layouts.app')

@section('content')
<style>
    .card-taxable {
        border: 3px solid #f5c2c7;
    }

    .card-nontaxable {
        border: 3px solid #badbcc;
    }

    .price-taxable {
        color: #b35c00;
    }

    .price-nontaxable {
        color: #0f5132;
    }

    .badge-taxable {
        background-color: #ffe5b4;
        color: #7a3e00;
    }

    .badge-nontaxable {
        background-color: #c7eed8;
        color: #1d5635;
    }
</style>
@if (session('success'))
    <div class="alert alert-info">
        {{ session('success') }}
    </div>
@endif
<div class="container">
    <h1 class="h4 mb-4">カート</h1>

    @if(count($cartItems) === 0)
        <div class="alert alert-info">カートに商品がありません</div>
    @else
        @foreach($cartItems->groupBy('shop_id') as $shopId => $shopItems)
            @php
                $isTaxableShop = $shopItems->first()->product->shop->invoice_number ? true : false;
                $bgClassShop = $isTaxableShop ? 'card-taxable' : 'card-nontaxable';
                $today = \Carbon\Carbon::today();
                $maxDay = \Carbon\Carbon::today()->addDays(14); // 14日先まで
            @endphp

            <div class="card mb-4 {{ $bgClassShop }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $shopItems->first()->shop_name }}</strong>

                    {{-- 一括スロット適用フォーム --}}
                    <form method="POST" action="{{ route('pickup.cart.updateAllSlots') }}" class="d-flex align-items-center">
                        @csrf
                        <input type="hidden" name="shop_id" value="{{ $shopId }}">

                        <input type="date"
                               name="date"
                               class="form-control form-control-sm me-2 shop-date"
                               min="{{ \Carbon\Carbon::today()->toDateString() }}"
                               max="{{ now()->addDays(14)->toDateString() }}"
                               required>

                        <select name="pickup_slot_id" class="form-select form-select-sm me-2 shop-slot" required>
                            <option value="">スロットを選択</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            この店舗の商品すべてに適用
                        </button>
                    </form>
                </div>

                <div class="card-body">
                    @foreach($shopItems as $item)
                        @php
                            $isTaxable = $item->product->shop->invoice_number ? true : false;
                            $displayPrice = $isTaxable
                                ? number_format($item->product->price * (App\Models\TaxRate::current()?->rate + 1))
                                : number_format($item->product->price);
                        @endphp

                        <div class="d-flex mb-3 p-2 {{ $isTaxable ? 'card-taxable' : 'card-nontaxable' }} cart-item"
                             data-product-id="{{ $item->product_id }}"
                             data-cart-id="{{ $item->id }}">

                            <img src="{{ asset('storage/' . $item->product->cover_img1) }}"
                                 class="img-thumbnail me-3"
                                 style="width:80px;height:80px;">

                            <div class="flex-grow-1">
                                <p class="mb-0">{{ $item->product->name }}</p>
                                <p class="mb-0">
                                    ¥{{ $displayPrice }}
                                    <span class="badge {{ $isTaxable ? 'badge-taxable' : 'badge-nontaxable' }}">
                                       {{ $isTaxable ? '課税' : '非課税' }}
                                    </span>
                                </p>
                                @if($item->product->stock < 3)
                                    <button 
                                        type="button" 
                                        class="btn btn-primary" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="在庫が残りわずかです！お早めにご注文ください。"
                                    >
                                        残りわずか <span class="badge bg-light text-dark">{{ $item->product->stock }}</span>
                                    </button>
                                @endif

                                {{-- 個別スロット --}}
                                <input type="date"
                                       class="form-control form-control-sm slot-date mt-1"
                                       value=""
                                       min="{{ \Carbon\Carbon::today()->toDateString() }}"
                                       max="{{ now()->addDays(14)->toDateString() }}">

                                <select class="form-select form-select-sm available-slots mt-1">
                                    <option value="">日付を選択してください</option>
                                </select>

                                <p class="no-slots-msg text-danger mt-1" style="display:none;">
                                    担当不在かお休みか選択できない日時です
                                </p>
                            </div>

                            {{-- 個別削除 --}}
                            <form action="{{ route('pickup.cart.remove', $item->id) }}" method="POST" class="ms-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">削除</button>
                            </form>
                        </div>
                        <hr>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- カート全削除 --}}
        <form action="{{ route('pickup.cart.clear') }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">カートを空にする</button>
        </form>

        <div class="text-end">
            <a href="{{ route('pickup.cart.checkout') }}" id="goCheckout" class="btn btn-primary btn-lg">
                受取場所の確認とカード情報入力に進む
            </a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl)
    })
})
</script>

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(function() {
    // 個別スロット取得
    $(document).on('change', '.slot-date', function() {
        var $container = $(this).closest('.cart-item');
        var productId = $container.data('product-id');
        var date = $(this).val();
        var $select = $container.find('.available-slots');
        var $msg = $container.find('.no-slots-msg');

        if (date === "") {
            $select.html('<option value="">日付を選択してください</option>');
            $msg.hide();
            return;
        }

        $.ajax({
            url: "{{ route('pickup.cart.getAvailableSlots') }}",
            data: { product_id: productId, date: date },
            success: function(res) {
                $select.empty();
                if (res.slots.length > 0) {
                    $msg.hide();
                    $select.append('<option value="">日時を選択</option>');
                    $.each(res.slots, function(i, slot) {
                        $select.append('<option value="' + slot.id + '">ID:' + slot.id + '｜' + slot.start_time + '〜' + slot.end_time + '</option>');
                    });
                } else {
                    $msg.show();
                    $select.append('<option value="">受取できない日時です</option>');
                }
            }
        });
    });

    // 日付 or スロット選択時に session 更新
    $(document).on('change', '.slot-date, .available-slots', function() {
        const $container = $(this).closest('.cart-item');
        const productId = $container.data('product-id');
        const cartId = $container.data('cart-id');
        const pickupDate = $container.find('.slot-date').val();
        const pickupTime = $container.find('.available-slots').val();
        const pickupLocationId = $container.find('input[name^="pickup_location"]').val();
        const $selectedSlot = $container.find('.available-slots option:selected');//追加2025/11/03
        const pickupSlotId = $selectedSlot.val();//追加2025/11/03

        $.ajax({
            url: "{{ route('pickup.cart.updatePickupInfo') }}",
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            data: JSON.stringify({
                pickup_info: {
                    [cartId]: {
                        product_id: productId,
                        pickup_date: pickupDate,
                        pickup_time: pickupTime,
                        pickup_slot_id: pickupTime, // DB登録用
                        pickup_location_id: pickupLocationId
                    }
                }
            }),
            success: function(res) {
                console.log('Session updated:', res);
            },
            error: function(xhr) {
                console.error('更新エラー:', xhr.responseText);
            }
        });
    });

    // ✅ チェックアウトへ進むボタンのクリック時に 0.3秒遅延を入れる
    $('#goCheckout').on('click', function(e) {
        e.preventDefault();
        const checkoutUrl = $(this).attr('href');
        setTimeout(() => {
            window.location.href = checkoutUrl;
        }, 300);
    });

    // 一括スロット取得
    $(document).on('change', '.shop-date', function() {
        var $form = $(this).closest('form');
        var shopId = $form.find('input[name="shop_id"]').val();
        var date = $(this).val();
        var $select = $form.find('.shop-slot');

        if (date === "") {
            $select.html('<option value="">スロットを選択</option>');
            return;
        }

        $.ajax({
            url: "{{ route('pickup.cart.getCommonSlots') }}",
            data: { shop_id: shopId, date: date },
            success: function(res) {
                $select.empty();
                if (res.commonSlots.length > 0) {
                    $select.append('<option value="">スロットを選択</option>');
                    $.each(res.commonSlots, function(i, slot) {
                        $select.append('<option value="' + slot.id + '">' + slot.start_time + '〜' + slot.end_time + '</option>');
                    });
                } else {
                    $select.append('<option value="">スロットがありません</option>');
                }
            }
        });
    });
});
</script>
@endsection
