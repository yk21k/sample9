@component('mail::message')
# おめでとうございます

ショップのサイトが公開されました。

@component('mail::button', ['url' => route('shops.show', $desplay->shop_id)])
あなたのショップサイトにアクセス
@endcomponent

申込ありがとうございました。<br>
{{ config('app.name') }}
@endcomponent
