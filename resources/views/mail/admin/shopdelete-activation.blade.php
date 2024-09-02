@component('mail::message')
# Termination of Membership activation request

Please make your shop In-Active. Shop details are here.

User Name : {{$deleteShop->deleteShopp->name}}

@component('mail::button', ['url' => url('/admin/shops')])
Manage In-Active or Active Shops
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent