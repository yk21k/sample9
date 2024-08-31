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
  <form class="h-adr" action="" method="post" enctype="multipart/form-data">@csrf
    {{-- {{ csrf_field() }} --}}

    <div class="form-group">
        <label for="description"><h3>Reason</h3></label>
        <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
    </div>
    &nbsp;

    <button type="submit" class="btn btn-primary mt-3">Submit</button>
  </form>
</div>
&nbsp;

@endsection