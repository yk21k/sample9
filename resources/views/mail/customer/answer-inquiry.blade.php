@component('mail::message')
# 回答済み

サイト管理者から回答を受け取りました。

このメールは、回答を受け取ったことをお知らせするものです。このメールには返信しませんので、下のリンクからウェブサイトにアクセスして返信を確認してください。

@component('mail::button', ['url' => route('account.answers')])
Visit the site to check
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
