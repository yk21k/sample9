@component('mail::message')

# 本日開催中のキャンペーン一覧

<strong>お知らせ</strong><br>
現在、{{ $formail_shops->name }}ではキャンペーンを開催中です！<br><br>
@forelse($formail_campaigns as $campaign)
---
【キャンペーン内容】
## 🎁 {{ $campaign->name }}

{{ $campaign->description }}

📅 期間：{{ $campaign->start_date->format('Y年m月d日') }} ～ {{ $campaign->end_date->format('Y年m月d日') }}

@empty
現在開催中のキャンペーンはありません。
@endforelse<br>

- {{ $formail_shops->name ?? 'ショップ名未設定' }}のオークション出品以外の商品がオフ、オークション出品と同じ商品でも通常商品ならオフ<br><br>

詳細については、ウェブサイトをご覧ください。<br><br>
ぜひご確認ください！<br>
<em>{{ $formail_shops->name }}</em>


@component('mail::button', ['url' => route('home')])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
