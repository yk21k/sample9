@component('mail::message')
# Coupon Present

<strong>こんにちは!</strong><br>
この度はご利用いただきありがとうございます。<br><br>
ご愛顧の感謝の気持ちを込めて、次回のお買い物で使えるクーポンをプレゼントします！<br><br>
こちらのクーポンは、オークション出品の商品に対しては、適用されません。
オークション出品の商品と同じ商品が当サイトの通常の出品物として出品されている場合は、適用されます。
ひとつの商品に一度だけ利用できます。同じ商品を２つ以上購入時もひとつの商品に一度だけ利用できます。
クーポンコード: {{ $formail_coupons->code }}
有効期限: {{ $formail_coupons->expiry_date }}
<br><br>
今後ともよろしくお願いいたします。<br>
<em>{{ $formail_shops->name }}</em>


@component('mail::button', ['url' => route('home')])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
