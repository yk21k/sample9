@extends('layouts.app')


@section('content')


<h2>Termination of Membership</h2>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-3">
  <form class="h-adr" action="{{route('users.delete_shop')}}" method="post" enctype="multipart/form-data">@csrf
    {{-- {{ csrf_field() }} --}}

    <div class="form-group">
        <input class="" type="hidden" id="user_id" name="user_id" value="{{ Auth::user()->id }}" readonly="">
        <label for="reason"><h3>Reason</h3></label>
        <textarea class="form-control" name="reason" id="reason" rows="3" required></textarea>
    </div>
    &nbsp;

    <button type="submit" class="btn btn-primary mt-3">Submit</button>
  </form>
</div>
&nbsp;

@endsection