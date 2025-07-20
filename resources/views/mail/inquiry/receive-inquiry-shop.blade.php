@component('mail::message')
# お問い合わせリクエスト

当サイトのあなたのShop宛のお問い合わせをいただきました。お問い合わせ内容の詳細を記載いたします。当サイトにアクセスしご回答お願いします。

@php
    $subjects = [
        1 => 'このお問い合わせの回答や問題解決と購入とは別のものです。',
        2 => 'このお問い合わせの回答や問題解決が解決されなくても購入します。',
        3 => 'このお問い合わせの回答や問題解決が解決したら購入します。',
        4 => 'この問題が解決されれば、購入を積極的に検討します。',
        5 => 'ご購入・お受け取りいただいた商品についてです。',
        6 => 'キャンセルしたい',
    ];

    $subjectLabel = $subjects[$inquiryAnswers->inq_subject] ?? '未分類';
@endphp

Subject : {{ $subjectLabel }}

Email : {{ $inquiryAnswers->inqUser->email }}
Subject : {{ $inquiryAnswers->inquiry_details }}

@component('mail::button', ['url' => url('/admin')])
Manage Inquiries
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
