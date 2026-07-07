@extends('layouts.seller')
<!DOCTYPE html>
<html>
<head>
    <title>スタッフ登録</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="container mt-5">

<h2>スタッフ登録</h2>

<p>メール：{{ $member->email }}</p>

<form method="POST">
    @csrf

    <input type="hidden" name="token" value="{{ request('token') }}">

    <div class="mb-3">
        <label>パスワード</label>
        <input type="password" name="password" class="form-control">
    </div>

    <div class="mb-3">
        <label>パスワード確認</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <button class="btn btn-primary">登録</button>
</form>

</body>
</html>