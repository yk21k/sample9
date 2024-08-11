@extends('layouts.app')


@section('content')

<div class="container">
    <div class="card border-dark mb-3">
        <div class="card-header">
          <h3>Confirmation of withdrawal</h3>
        </div>
      <div class="card-body">
        <p class="card-text">When you unsubscribe, all --- will also be deleted.</p>
        <p class="card-text">Do you still want to unsubscribe?</p>
      </div>
    </div>

    <div class="btn-group">


        <form action="{{ route('users.withdraw',Auth::user()->id) }}" method="POST">
           @csrf
           <button class="btn btn-danger" type="submit">Withdraw</button>
        </form>

        <div class="ml-3">
            <a href="/" class="btn btn-primary">Cancel</a>
        </div>
    </div>
</div>

@endsection