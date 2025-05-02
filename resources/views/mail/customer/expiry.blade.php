@component('mail::message')
# 【重要な通知】運転免許証/パスポートの有効期限について

ショップの代表者の運転免許証/パスポートの有効期限が、迫っています。
有効期限を更新した運転免許証/パスポートをアップロードして下さい。

@component('mail::button', ['url' => route('shops.show', $shop->id)])
当サイトにアクセス
@endcomponent

@component('mail::button', ['url' => url('/admin/shops')])
当サイトのあなたのShopの管理画面ににアクセス
@endcomponent

ご対応よろしくお願いします。<br>
{{ config('app.name') }}
@endcomponent