@component('mail::message')
# Inquiry request

We have received an inquiry. Here are inquiry details.

Email : {{ $inquiryAnswers->inqUser->email }}
Subject : {{ $inquiryAnswers->inq_subject }}
Subject : {{ $inquiryAnswers->inquiry_details }}

@component('mail::button', ['url' => url('/admin/customer-inquiries')])
Manage Inquiries
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
