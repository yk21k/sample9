@component('mail::message')
# Greetings


This is a greeting email. Thank you.


<strong>こんにちは!</strong><br>
この度は、ご購入いただきありがとうございます！<br><br>
商品をご使用いただいた後、ぜひレビューを投稿してください。お客様の貴重な意見が、他のお客様の参考になります。<br><br>
レビューのご投稿はご購入の商品の詳細ページ内の活性化された　Launch Favorite からお願いします<br>
ご協力よろしくお願いいたします！<br>

<em>（Shop 名：{{ $formail_shops->name }}）</em>

@component('mail::button', ['url' => route('home')])
Button Text
@endcomponent

Shop Name : -----

Thanks,<br>
{{ config('app.name') }}
@endcomponent
