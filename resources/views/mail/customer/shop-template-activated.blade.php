@component('mail::message')
# Congratulations

Your shop site is now active

@component('mail::button', ['url' => route('shops.show', $desplay->shop_id)])
Visit Your Shop Site
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
