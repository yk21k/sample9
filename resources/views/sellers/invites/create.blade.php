@extends('layouts.seller')

@section('content')
<h2>スタッフ招待</h2>

<form method="POST" action="{{ route('invite.store') }}">
    @csrf

    <div class="mb-3">
        <label>名前</label>
        <input type="text" name="name" class="form-control">
    </div>

    <div class="mb-3">
        <label>メール</label>
        <input type="email" name="email" class="form-control">
    </div>

    <button class="btn btn-primary">招待送信</button>
</form>
@endsection