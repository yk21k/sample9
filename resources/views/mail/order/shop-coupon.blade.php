@component('mail::message')
# Coupon Present


<strong>こんにちは!</strong><br>
この度はご利用いただきありがとうございます。<br><br>
ご愛顧の感謝の気持ちを込めて、次回のお買い物で使えるクーポンをプレゼントします！<br><br>
クーポンコード: {{ $formail_coupons->name }} 

<br>
※ご利用期限は {{ $formail_coupons->expiry_date }} までです。<br><br>
今後ともよろしくお願いいたします。<br>

<em>（Shop 名）: {{ $formail_shops->name }}</em>


@component('mail::button', ['url' => route('home')])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
