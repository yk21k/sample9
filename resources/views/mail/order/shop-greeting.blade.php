@component('mail::message')
# Greetings


This is a greeting email. Thank you.


<strong>こんにちは!</strong><br>
            この度は（商品名）をご購入いただきありがとうございます！<br><br>
            商品をご使用いただいた後、ぜひレビューを投稿してください。お客様の貴重な意見が、他のお客様の参考になります。<br><br>
            レビューのご投稿はこちらのリンクからお願いします：<br>
            <a href="レビュー投稿ページのURL" target="_blank">レビュー投稿ページ</a><br><br>
            ご協力いただきありがとうございます！<br>
            <em>（会社名）</em>

@component('mail::button', ['url' => route('home')])
Button Text
@endcomponent

Shop Name : -----

Thanks,<br>
{{ config('app.name') }}
@endcomponent
