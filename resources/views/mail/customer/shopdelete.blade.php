@component('mail::message')
# ご利用いただきありがとうございます。

当サイト上であなたのショップは現在、稼働していません。

@component('mail::button', ['url' => route('home')])
詳細については、こちらをご覧ください
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
