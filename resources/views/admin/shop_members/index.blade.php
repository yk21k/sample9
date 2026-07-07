{{-- resources/views/shop_members/index.blade.php --}}

<h1>スタッフ一覧（{{ $shop->name }}）</h1>

{{-- 追加ボタン --}}
@can('manageStaff', $shop)
    <a href="{{ route('shop_members.create', $shop) }}">追加AAAAA</a>
@endcan

<table border="1">
    <tr>
        <th>名前</th>
        <th>役割</th>
        <th>操作</th>
    </tr>

    @foreach($members as $member)
        <tr>
            <td>{{ $member->name }}</td>
            <td>{{ $member->role }}</td>

            <td>
                {{-- 削除（ownerのみ） --}}
                @can('delete', $shop)
                    <form method="POST" action="{{ route('shop_members.destroy', $member) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit">削除</button>
                    </form>
                @endcan
            </td>
        </tr>
    @endforeach
</table>