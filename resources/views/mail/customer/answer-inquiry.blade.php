@component('mail::message')
# Answered

You received an answer from the site administrator.

This email will let you know that we have received your answer. We do not reply to this email, so please access the website from the link below and check for replies.

@component('mail::button', ['url' => route('account.answers')])
Visit the site to check
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
