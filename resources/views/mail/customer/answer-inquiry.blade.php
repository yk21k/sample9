@component('mail::message')
# サイト管理者から回答

サイト管理者から回答を受け取りました。

このメールは、回答を受け取ったことをお知らせするものです。このメールには返信しませんので、下のリンクからウェブサイトにアクセスして返信を確認してください。

@component('mail::button', ['url' => route('inquiries.answers')])
サイトにアクセス
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
