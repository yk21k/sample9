@component('mail::message')
# サイト掲載商品に関するストア担当者からの回答

ストア担当者からの回答をご確認ください。

サイト掲載商品に関して、ストア担当者より回答をいただいておりますので、サイトにアクセスしてご確認ください。なお、このメールはサイト掲載商品に関するストア担当者からの回答をお客様にお知らせするためのものであり、このメールへのご返信は受け付けておりませんので、メールからサイトにアクセスしてご確認ください。


@component('mail::button', ['url' => route('customer.inquiry', $customerInquiry->shop_id)])
サイトにアクセス
@endcomponent



<br>
{{ config('app.name') }}
@endcomponent
