@extends('layouts.seller')
@section('content')

<!-- resources/views/email_histories/index.blade.php -->

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール送信履歴</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;

        }
        th {
            background-color: #f4f4f4;
            color: #333;

        }
    </style>
</head>
<body>

    <h1>メール送信履歴</h1>

    <table>
        <thead>
            <tr>
                <th>送信先</th>
                <th>名前</th>

                <th>メール種類</th>
                <th>送信日時</th>
                <th>クーポン</th>
                <th>キャンペーン</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mails_histories as $history)
                <tr>
                    <td>{{ $history->mail }}</td>
                    <td>
                        @if(!is_null($history->forMailUser?->name))
                            {{ $history->forMailUser->name }}
                        @else
                            未設定
                        @endif
                    </td>

                    @if($history->template == 'template1')

                    	<td>(挨拶とクーポン)</td>

                    @elseif($history->template == 'template2')

                    	<td>(キャンペーン開催)</td>
                    	

                    @elseif($history->template == 'template3')

                    	<td>(商品レビュー依頼)</td>

                    @endif
                    	<td>{{ $history->created_at }}</td>

	                @php
                        $mail_history_coupon = App\Models\ShopCoupon::find($history->order_coupon);
                    @endphp

                    @if($mail_history_coupon)
                        <td>
                            (コード: {{ $mail_history_coupon->code }})
                            （期限: {{ \Carbon\Carbon::parse($mail_history_coupon->expiry_date)->format('Y年m月d日') }}）
                        </td>
                    @elseif($history->template === 'template3')
                        <td>設定できません</td>
                    @else
                        <td>未設定</td>
                    @endif

                    @if(!empty($history->forMailCampaign ))
                        <td>{{ $history->forMailCampaign->name  }}</td>
                    @else
                        <td>未設定</td>    
                    @endif


                </tr>
            @empty
                <tr>
                    <td colspan="4">送信履歴はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <!-- ページネーション -->
    <div>
        {{ $mails_histories->links() }}
    </div>
</body>
</html>


@endsection