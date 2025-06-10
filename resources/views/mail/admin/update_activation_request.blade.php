@component('mail::message')
# ショップ更新申請のお知らせ

管理者様

以下のショップより、更新申請が届いています。

---

**ショップ名**: {{ $shop->name }}  
**代表者**: {{ $shop->representative }}  
**所在地**: {{ $shop->location_1 }}  
**電話番号**: {{ $shop->telephone }}  
**Email**: {{ $shop->email }}

@component('mail::button', ['url' => url('/admin/shops/' . $shop->id)])
管理画面で確認する
@endcomponent

確認お願いいたします。  
{{ config('app.name') }}
@endcomponent
