@component('mail::message')
# {{ $formail_campaigns->name }}　開催

<strong>お知らせ</strong><br>
現在、（Shop 名）: {{ $formail_shops->name }}ではキャンペーンを
開催中です！<br><br>
【キャンペーン内容】<br>
- {{ $formail_campaigns->description }}<br>
期間: {{ ($formail_campaigns->start_date)->format('Y年n月j日') }}〜{{ ($formail_campaigns->end_date)->format('Y年n月j日')  }}<br>
詳細については、当社のウェブサイトをご覧ください。<br><br>
ぜひご参加ください！<br>
<em>（Shop 名）: {{ $formail_shops->name }}</em>


@component('mail::button', ['url' => route('home')])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
