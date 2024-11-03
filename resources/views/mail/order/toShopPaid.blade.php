@component('mail::message')
# An order has been placed


Please check from the management screen

<!-- Total : {{$order->grand_total}} -->


@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
