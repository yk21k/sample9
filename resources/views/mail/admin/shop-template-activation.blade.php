@component('mail::message')
# Shop Template activation request

Please activate shop Template. Here are shop details.

Shop Name : {{$desplay->shop_name}}
Shop Owner : {{$desplay->shop->name}}
Shop ID : {{$desplay->shop_id}}

@component('mail::button', ['url' => url('/admin/shops')])
Manage Shops
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
