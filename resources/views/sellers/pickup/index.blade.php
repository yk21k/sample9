@extends('layouts.seller')

@section('content')
<h1>スロット一覧</h1>

{{-- ✅ フラッシュメッセージの表示 --}}
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<a href="{{ route('seller.pickup.slots.create', $product) }}" class="link-info">新規スロット作成</a>

<table border="1" cellpadding="5" cellspacing="0" style="margin-top:10px;">
    <thead>
        <tr>
            <th>商品名</th>
            <th>日付</th>
            <th>時間</th>
            <th>定員</th>
            <th>残枠</th>
            <th>状態</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach($slots as $slot)
        <tr>
            <td>{{ $slot->product->name }}</td>
            <td>{{ $slot->date }}</td>
            <td>{{ $slot->start_time }}〜{{ $slot->end_time }}</td>
            <td>{{ $slot->capacity }}</td>
            <td>{{ $slot->remaining_capacity }}</td>
            <!-- <td>{{ $slot->available() }}</td> -->
            
            <td>{{ $slot->is_active ? '有効' : '無効' }}</td>
            <td>
                <a href="{{ route('seller.pickup.slots.edit', $slot) }}" class="link-info">編集</a>
                <!-- 削除はフォームで送信 -->
                <form method="POST" action="{{ route('seller.pickup.slots.destroy', $slot) }}" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('削除しますか？')">削除</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
