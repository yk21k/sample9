@component('mail::message')
# ショップ無効化のご依頼

以下のショップについて、会員終了に伴う無効化の対応をお願いいたします。詳細は以下のとおりです。

**ユーザー名：** {{ $deleteShop->deleteShopp->name }}

@component('mail::button', ['url' => url('/admin/shops')])
ショップの有効／無効を管理する
@endcomponent

何卒よろしくお願いいたします。  
{{ config('app.name') }}
@endcomponent
