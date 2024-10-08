@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="wrapper">

	<h3> Your Account </h3>

	<div class="">
		<form action="{{route('account.account', Auth::user()->id)}}" method="post">@csrf
			<p><input type="text" name="name" value="{{ ($profiles->name) }}"></p>
			<p><input type="text" name="email" value="{{ ($profiles->email) }}"></p>
			<p><input type="text" name="password" value=""></p>
			<p>start : {{ ($profiles->created_at) }}</p>
			<p>latest : {{ ($profiles->updated_at) }}</p>
	        <button class="btn btn-primary" type="submit">Update</button>

		</form>
	</div>

	<a class="" href="{{ route('shops.create') }}">Open Your Shop</a>

	<a class="" href="{{ route('account.inquiry') }}">Contact Us</a>

	<a>　Contact Us　</a>
	<a>　Your Contact 　</a>
</div>	

@endsection