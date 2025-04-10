@component('mail::message')
#  Answers from store personnel regarding products on the site

Please check the answer from the store representative.

We have received answers from store representatives regarding the products on the site, so please visit the site and check. Please note that this email is to notify customers of the response from the store representative regarding the product on the site, and we cannot accept replies to this email, so please access the site from the email to confirm.


@component('mail::button', ['url' => route('customer.inquiry', $customerInquiry->shop_id)])
Visit the site to check
@endcomponent



Thanks,<br>
{{ config('app.name') }}
@endcomponent
