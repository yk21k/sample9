@component('mail::message')
# Thank you for using our service.

Your shop is now Not active

@component('mail::button', ['url' => route('home')])
Visit to find out more
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
