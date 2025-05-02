@component('mail::message')
# おめでとうございます

ショップがオープンしました

@component('mail::button', ['url' => route('shops.show', $shop->id)])
当サイトにアクセス
@endcomponent

@component('mail::button', ['url' => url('/admin/shops')])
当サイトのあなたのShopの管理画面ににアクセス
@endcomponent

Shop開設の申込ありがとうございました。<br>
{{ config('app.name') }}
@endcomponent