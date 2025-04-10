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
<form action="{{ route('inquiries.store', ['id'=>auth()->user()->id]) }}" method="post">
    @csrf

    <input class="" type="hidden" id="user_id" name="user_id" value="{{ Auth::user()->id }}" readonly="">

    <div class="form-group">
        <label for="inq_subject">Subject</label>
        <input type="text" class="form-control" name="inq_subject" id="" aria-describedby="helpId" placeholder="Subject">
    </div><br>

    <div class="form-group">
        <label for="inquiry_details">Inquiry Detail</label>
        <textarea class="form-control" name="inquiry_details" id="" rows="3" placeholder="Detail"></textarea>
    </div><br>

    <div class="form-group">
    	<button class="btn btn-primary" type="submit">Submit</button>
    </div><br>

</form>

<a href="{{ route('inquiries.answers') }}">Answers</a>    
@endsection