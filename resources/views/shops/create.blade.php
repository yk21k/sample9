@extends('layouts.app')


@section('content')


<h2>Submit Your Shop </h2>

<form action="{{route('shops.store')}}" method="post">
    @csrf

    <div class="form-group">
        <label for="name">Name of Shop</label>
        <input type="text" class="form-control" name="name" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" name="description" id="" rows="3"></textarea>
    </div>

    <div class="form-group">
        <label for="name"> Representative Name　</label>
        <input type="text" class="form-control" name="name" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div class="form-group">
        <label for="name"> Administrator　</label>
        <input type="text" class="form-control" name="name" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div class="form-group">
        <label for="name"> Location　</label>
        <input type="text" class="form-control" name="name" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div class="form-group">
        <label for="name"> Contact Address　</label>
        <input type="text" class="form-control" name="name" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div> The Others </div>

    <button type="submit" class="btn btn-primary">Submit</button>

</form>


@endsection