@component('mail::message')
# 商品配達完了のお知らせ

注文ID {{ $subOrder->order_id }} の商品の配達完了いたしました。

	@component('mail::button', ['url' => route('account.account', $subOrder->seller_id)])
	サイトにアクセス

	ありがとうございます。
	@endcomponent
<br>
{{ config('app.name') }}

@endcomponent