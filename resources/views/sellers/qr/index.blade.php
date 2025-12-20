@extends('voyager::master')

@section('content')
<div class="container" style="max-width: 600px; text-align: center; padding: 20px;">

    <h2 class="mb-4">本日のスタッフログイン用 QR コード</h2>

    @if(isset($qr))
        <div id="qr-area" class="p-3" style="background:white; border:1px solid #ddd; display:inline-block;">
            {!! $qr !!}
            <p class="mt-2" style="font-size: 16px;">
                有効期限：{{ $expiresAt }}
            </p>
        </div>

        <br><br>

        <!-- ✔ 印刷ボタン -->
        <button onclick="window.print()" class="btn btn-primary mt-3 no-print">
            印刷する
        </button>

    @else
        <p>まだQRコードが生成されていません。</p>
    @endif
</div>

{{-- ✔ 印刷用 CSS --}}
<style>
    /* 印刷時は Voyager のUIを全部隠す */
    @media print {
        .navbar-header,
        .app-container,
        .container-fluid,
        .breadcrumb,
        .side-menu,
        .navbar,
        .no-print {
            display: none !important;
        }

        /* 印刷エリアだけ中央に */
        #qr-area {
            margin-top: 60px;
        }

        body {
            background: white !important;
        }
    }
</style>
@endsection

