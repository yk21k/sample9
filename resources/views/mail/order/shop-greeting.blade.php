@component('mail::message')
# Greetings


This is a greeting email. Thank you.


この度はオーダーNO：<strong>{{ $mail_orders->order_number }}</strong>の内容でご購入いただきありがとうございます！<br><br>
商品をご使用いただいた後、ぜひレビューを投稿してください。お客様の貴重な意見が、他のお客様の参考になります。
当サイトでは、評価に対してクーポンや金銭などの対価は一切提供しておりません。
公平で信頼性のある評価を維持するため、
報酬・特典などの提供を受けた上でのレビューやスコア投稿はご遠慮くださいますようお願い申し上げます。
<br><br>
レビューのご投稿はご購入の商品の詳細ページ内の活性化された　Launch Favorite からお願いします<br>
※この評価は、他のお客様にも参考にしていただく目的でランキング等に使用されます。 

ご協力よろしくお願いします！<br>
<em>{{ $formail_shops->name }}</em>


@component('mail::button', ['url' => route('home')])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
