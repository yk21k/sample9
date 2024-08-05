@component('mail::message')
# Inquiries regarding products

We have received an inquiry. Here are inquiry details.

We received an inquiry from a customer on the product page, so please check with the store representative and respond.
We manage whether or not you receive a reply, so please access our website and reply before replying. Please check how to use the site if necessary.

@component('mail::button', ['url' => url('/admin')])
Please visit the site and answer
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
