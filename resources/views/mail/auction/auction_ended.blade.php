@component('mail::message')
# オークション終了のお知らせ

こんにちは、{{ $winner->name }}さん

オークション「{{ $auction->name }}」は終了しました。

入札金額: ¥{{ number_format($auction->spot_price) }} で終了しています。

結果を確認するには以下のリンクをご覧ください：

@component('mail::button', ['url' => route('home.auction.show', $auction->id)])
オークション詳細ページ
@endcomponent

@endcomponent
