@component('mail::message')
# ショップ有効化のご依頼

以下のショップが新規に登録されました。内容をご確認のうえ、有効化の対応をお願いいたします。

**ショップ名：** {{ $shop->name }}  
**ショップオーナー：** {{ $shop->owner->name }}

@component('mail::button', ['url' => url('/admin/shops')])
ショップ管理画面を開く
@endcomponent

何卒よろしくお願いいたします。  
{{ config('app.name') }}
@endcomponent
