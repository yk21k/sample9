@component('mail::message')
# ショップテンプレート有効化のご依頼

以下のショップに対し、テンプレート有効化のご依頼が届いております。内容をご確認のうえ、ご対応をお願いいたします。

**ショップ名：** {{ $desplay->shop_name }}  
**ショップオーナー：** {{ $desplay->shop->name }}  
**ショップID：** {{ $desplay->shop_id }}

@component('mail::button', ['url' => url('/admin/shops')])
ショップ管理画面を開く
@endcomponent

何卒よろしくお願いいたします。  
{{ config('app.name') }}
@endcomponent
