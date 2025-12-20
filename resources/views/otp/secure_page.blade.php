@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- ✅ ヘッダー --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">店舗受け取り：本人確認済み</h4>
        <a href="{{ route('pickup.otp.index') }}" class="btn btn-outline-secondary btn-sm">← 戻る</a>
    </div>

    {{-- ✅ メッセージ表示 --}}
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <div class="card shadow-sm p-4">
        <h5 class="text-success mb-3">
            ✅ この注文は本人確認（ログイン）が完了しています
        </h5>

        <p>
            以下のワンタイムパスワードでログインが確認されました。<br>
            店舗スタッフがこの情報と引き換えに受け渡しを行えます。
            商品受取前に店舗から番号開示の依頼があった際は、ご自身のご判断でご対応お願いします。
        </p>

        <div class="mt-4 text-center">
            <h6 class="text-muted">使用済みOTPコード：</h6>
            <h2 class="fw-bold text-primary">{{ $otp->code }}</h2>
            <p class="text-muted mb-0">発行日時：{{ $otp->created_at->format('Y/m/d H:i') }}</p>
            <p class="text-muted">有効期限：{{ $otp->expires_at->format('Y/m/d H:i') }}</p>
        </div>

        {{-- ✅ ログイン解除フォーム --}}
        <hr>
        <form method="POST" action="{{ route('pickup.otp.logout', $otp->id) }}">
            @csrf
            <button type="submit" class="btn btn-warning mt-3"
                onclick="return confirm('このOTPを未使用に戻しますか？再度ログインできます。')">
                ログイン状態を解除する（OTPを未使用に戻す）
            </button>
        </form>
    </div>
</div>
@endsection
