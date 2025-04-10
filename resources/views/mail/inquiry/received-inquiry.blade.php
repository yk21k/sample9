@component('mail::message')
# アカウントページからお問い合わせがありました

User ID : {{$inquiryAnswers->user_id}}
Subject : {{$inquiryAnswers->inq_subject}}
Subject : {{$inquiryAnswers->inquiry_details}}

@component('mail::button', ['url' => url('/admin')])
Please visit the site and answer
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent