@extends('layouts.seller')

@section('content')
<div class="container">
    <h1>受け取りスロット作成</h1>

    @if($errors->any())
        <div style="color: red; margin-bottom: 10px;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ✅ フラッシュメッセージの表示 --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('seller.pickup.slots.store') }}">
        @csrf

        <div style="margin-bottom: 10px;">
            <label>商品</label>
            <select name="pickup_product_id">
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 10px;">
            <label>日付</label>
            <input type="date" name="date" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>開始時間</label>
            <input type="time" name="start_time" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>終了時間</label>
            <input type="time" name="end_time" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>定員</label>
            <input type="number" name="capacity" min="1" required>
        </div>

        <button type="submit">作成</button>
    </form>

    <!-- 月単位生成フォーム -->
    <form method="POST" action="{{ route('seller.pickup.slots.generate', $product) }}">
        @csrf
        <label>対象月:</label>
        <input type="month" name="month" required>

        <label>時間帯・定員（例）:</label>
        <input type="text" name="times[0][start]" placeholder="10:00" required>
        <input type="text" name="times[0][end]" placeholder="12:00" required>
        <input type="number" name="times[0][capacity]" placeholder="5" required>

        <button type="submit">スロット自動生成</button>
    </form>

    <!-- 前月コピー -->
    <form method="POST" action="{{ route('seller.pickup.slots.copyPreviousMonth', $product) }}">
        @csrf
        <button type="submit">前月と同じスロットをコピー</button>
    </form>

    <!-- スロット一覧 -->
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>日付</th>
                <th>時間</th>
                <th>定員</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->date }}</td>
                    <td>{{ $product->start_time }}〜{{ $product->end_time }}</td>
                    <td>{{ $product->capacity }}</td>
                    <td>{{ $product->is_active ? '有効' : '無効' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection



