@extends('layouts.seller')

<h2>スタッフ登録</h2>

<form method="POST" action="{{ route('invite.staff.register', $member->invite_token) }}">
    @csrf

    email<input type="email" name="email">
    PW<input type="password" name="password">
    再PW<input type="password" name="password_confirmation">

    <button type="submit">登録</button>
</form>