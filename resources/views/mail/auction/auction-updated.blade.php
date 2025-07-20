@component('mail::message')
# ご注文の状況が更新されました

{{ $auction->name }} の配送状況が更新されました。

現在の状況:  
**{{ $auction->delivery_status_label }}**

@component('mail::button', ['url' => route('home.auction')])
詳細を見る
@endcomponent

ありがとうございます。  
{{ config('app.name') }}
@endcomponent
