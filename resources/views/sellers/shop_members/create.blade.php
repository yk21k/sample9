@extends('layouts.seller')

@section('content')
<h2>メンバー追加</h2>

{{-- =========================
    ① 既存ユーザー追加
========================= --}}
<h3>既存ユーザーを追加</h3>

<form method="POST" action="{{ route('seller.shop_members.store', $shop->id) }}">
    @csrf

    <h3>既存ユーザーを選択</h3>
    <small>※保存できない場合は、当サイトに登録がる可能性があるので、ご確認ください</small><br>
    @foreach($shop_members as $shop_member)

    <tr>
        <td>{{ $shop_member->name }}</td>

        <td>
            @if($shop_member->email)
                {{ $shop_member->email }}
            @else
                <span style="color:red;">未設定</span>
            @endif
        </td>

        <td>
            @if(!$shop_member->user_id)
                未登録
            @else
                登録済
            @endif
        </td>

        <td>
            @if(!$shop_member->email)
                {{-- メール入力 --}}
                <form method="POST" action="{{ route('member.set.email', $shop_member->id) }}">
                    @csrf
                    <input type="email" name="email" required>
                    <button>保存</button>
                </form>
            @elseif(!$shop_member->user_id)
                {{-- 招待 --}}
                <form method="POST" action="{{ route('member.invite', $shop_member->id) }}">
                    @csrf
                    <button>招待送信</button>
                </form>
            @else
                ✔
            @endif
        </td>
    </tr>

    @endforeach

    <p>または</p>

    <h3>新規招待</h3>
    <input type="text" name="name" placeholder="名前">
    <input type="email" name="email" placeholder="メール">

    <h3>権限</h3>
    <select name="role" required>
        <option value="manager">manager</option>
        <option value="staff">staff</option>
    </select>

    <button type="submit">追加 / 招待</button>
</form>


<hr>

{{-- =========================
    ② 新規招待
========================= --}}
<h3>新規メンバーを招待</h3>

<form method="POST" action="{{ route('seller.shop_members.store', $shop->id) }}">
    @csrf

    <input type="text" name="name" placeholder="名前" required>
    <input type="email" name="email" placeholder="メール" required>

    <select name="role">
        <option value="manager">manager</option>
        <option value="staff">staff</option>
    </select>

    <button type="submit">招待メール送信</button>
</form>

@endsection