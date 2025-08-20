@component('mail::message')
# 商品配達完了のお知らせ

注文ID **{{ $subOrder->order_id }}** の商品の配達が完了いたしました。
追跡番号や内容は、マイページからも確認ができます。<br>

追跡番号：{{ $subOrder->invoice_number }}<br>
配送会社：{{ $subOrder->shipping_company }}

@component('mail::button', ['url' => route('account.account', $subOrder->seller_id)])
サイトにアクセス
@endcomponent

ありがとうございます。  
{{ config('app.name') }}
@endcomponent
