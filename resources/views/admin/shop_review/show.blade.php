@extends('voyager::master')

@section('content')

<div class="container-fluid">

    <h2>出店申請 審査</h2>

    @php
        $data = $application->after_data;
    @endphp

    {{-- =========================
        基本情報
    ========================== --}}
    <div class="panel panel-bordered">
        <div class="panel-heading">
            <h3 class="panel-title">基本情報</h3>
        </div>
        <div class="panel-body">

            <table class="table table-bordered">
                <tr><th>種別</th><td>{{ $data['type'] }}</td></tr>
                <tr><th>店名</th><td>{{ $data['common']['shop_name'] }}</td></tr>
                <tr><th>インボイス</th><td>{{ $data['common']['invoice_number'] ?? 'なし' }}</td></tr>
                <tr><th>税方式</th><td>{{ $data['common']['tax_type'] }}</td></tr>
                <tr><th>概要</th><td>{{ $data['common']['description'] }}</td></tr>
                <tr><th>代表</th><td>{{ $data['common']['representative'] }}</td></tr>
                <tr><th>所在地</th><td>{{ $data['common']['address'] }}</td></tr>
                <tr><th>配送先</th><td>{{ $data['common']['shipping_address'] }}</td></tr>
                <tr><th>電話</th><td>{{ $data['common']['phone'] }}</td></tr>
                <tr><th>Email</th><td>{{ $data['common']['email'] }}</td></tr>
            </table>

        </div>
    </div>

    {{-- =========================
        タイプ別
    ========================== --}}
    <div class="panel panel-bordered">
        <div class="panel-heading">
            <h3 class="panel-title">タイプ別情報</h3>
        </div>
        <div class="panel-body">

            <table class="table table-bordered">

                @if($data['type_specific']['id_card_front'])
                <tr>
                    <th>ID表</th>
                    <td>
                        <img src="{{ Storage::disk('s3')->url($data['type_specific']['id_card_front']) }}" width="200">
                    </td>
                </tr>
                @endif

                @if($data['type_specific']['id_card_back'])
                <tr>
                    <th>ID裏</th>
                    <td>
                        <img src="{{ Storage::disk('s3')->url($data['type_specific']['id_card_back']) }}" width="200">
                    </td>
                </tr>
                @endif

                @if($data['type_specific']['business_registration'])
                <tr>
                    <th>開業届</th>
                    <td>
                        <a href="{{ Storage::disk('s3')->url($data['type_specific']['business_registration']) }}" target="_blank">確認</a>
                    </td>
                </tr>
                @endif

                @if($data['type_specific']['business_card'])
                <tr>
                    <th>名刺</th>
                    <td>
                        <img src="{{ Storage::disk('s3')->url($data['type_specific']['business_card']) }}" width="200">
                    </td>
                </tr>
                @endif

                @if($data['type_specific']['corporate_number'])
                <tr>
                    <th>法人番号</th>
                    <td>{{ $data['type_specific']['corporate_number'] }}</td>
                </tr>
                @endif

            </table>

        </div>
    </div>

    {{-- =========================
        添付ファイル
    ========================== --}}
    <div class="panel panel-bordered">
        <div class="panel-heading">
            <h3 class="panel-title">請求書</h3>
        </div>
        <div class="panel-body">

            <a href="{{ Storage::disk('s3')->url($data['common']['billing_file']) }}" target="_blank">
                請求書を確認
            </a>

        </div>
    </div>

    {{-- =========================
        担当者
    ========================== --}}
    <div class="panel panel-bordered">
        <div class="panel-heading">
            <h3 class="panel-title">担当者</h3>
        </div>
        <div class="panel-body">

        @foreach($data['members'] as $member)
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <strong>{{ $member['name'] }}</strong><br>

                @if(!empty($member['file1']))
                    <a href="{{ Storage::disk('s3')->url($member['file1']) }}" target="_blank">証明書1</a><br>
                @endif

                @if(!empty($member['file2'] ?? null))
                    <a href="{{ Storage::disk('s3')->url($member['file2']) }}" target="_blank">証明書2</a>
                @endif
            </div>
        @endforeach

            <h4>👥 担当者</h4>

            @foreach($after['members'] ?? [] as $i => $member)
                <div class="card mb-2 p-2">
                    <strong>担当者{{ $i+1 }}</strong><br>
                    名前: {{ $member['name'] }}<br>

                    @if(!empty($member['file1']))
                        <img src="{{ Storage::disk('s3')->url($member['file1']) }}" width="200">
                    @endif
                </div>
            @endforeach

        </div>
    </div>

    {{-- =========================
        承認ボタン
    ========================== --}}
    <div style="margin-top:20px;">

        <form method="POST" action="{{ route('admin.shop.approve', $application->id) }}">
            @csrf
            <button class="btn btn-success">承認</button>
        </form>

        <form method="POST" action="{{ route('admin.shop.reject', $application->id) }}">
            @csrf
            <button class="btn btn-danger">却下</button>
        </form>

    </div>

    @php
        $after = $application->after_data;
        $before = $application->before_data ?? [];
    @endphp

    {{-- =========================
        差分比較（共通）
    ========================= --}}
    <div class="panel panel-bordered">
        <div class="panel-heading">
            <h3 class="panel-title">変更内容（差分）</h3>
        </div>
        <div class="panel-body">

            <table class="table table-bordered">

                @php
                    $fields = [
                        'shop_name' => '店名',
                        'invoice_number' => 'インボイス',
                        'tax_type' => '税方式',
                        'description' => '概要',
                        'representative' => '代表',
                        'address' => '所在地',
                        'shipping_address' => '配送先',
                        'phone' => '電話',
                        'email' => 'Email',
                    ];
                @endphp

                @foreach($fields as $key => $label)

                    @php
                        $beforeVal = $before['common'][$key] ?? null;
                        $afterVal  = $after['common'][$key] ?? null;
                        $changed = $beforeVal != $afterVal;
                    @endphp

                    <tr style="{{ $changed ? 'background:#ffecec;' : '' }}">
                        <th>{{ $label }}</th>

                        <td>
                            <div style="color:#888;">旧：{{ $beforeVal ?? '-' }}</div>
                            <div style="font-weight:bold;">新：{{ $afterVal ?? '-' }}</div>
                        </td>
                    </tr>

                @endforeach

            </table>

        </div>
    </div>

</div>

@endsection