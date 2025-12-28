@extends('layouts.public')

@section('content')
<div class="container py-5">

    {{-- ヘッダー --}}
    <div class="text-center mb-5">
        <h1 class="fw-bold">出店を始める前に</h1>
        <p class="text-muted mt-3">
            まずは出店の流れと条件をご確認ください
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- カード --}}
            <div class="card shadow-sm">
                <div class="card-body">

                    <h3 class="fw-bold mb-3">出店の流れ（出店の意思がない場合は、対応不要です。）</h3>
                    
                    <h3><a href="{{ route('seller.bye') }}" class="small text-muted">出店しません</a></h3>

                    <ul class="small">
                        <li>アカウント作成（無料）
                            <br>必要なもの：<br>1.送受信可能なメールアドレス(出店時には、運転免許証などの個人情報をアップしていただきますのでそちらもご留意ください。)
                            2.連絡可能な携帯電話番号
                            <br>
                        </li>
                        <li>出店申請内容の入力</li>
                        <li>審査（通常1〜2営業日）</li>
                        <li>商品登録・販売開始</li>
                    </ul>

                    <hr class="my-4">

                    <h3 class="fw-bold mb-3">よくある質問</h3>
                    <p class="text-muted small">
                        出店費用や手数料などは、<br>
                        <strong>FAQ からいつでも確認できます</strong>。
                    </p>

                    {{-- CTA --}}
                    <div class="mt-4">
                        <a href="{{ route('register') }}"
                           class="btn btn-primary w-100">
                            無料で出店を始める
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <a href="/seller/about" class="small text-muted">
                            ← 出店について詳しく見る
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection
