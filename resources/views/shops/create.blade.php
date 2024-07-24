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
        <label for="representative"> Representative </label>
        <input type="text" class="form-control" name="representative" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div class="form-group">
        <label for="administrator"> Administrator　</label>
        <input type="text" class="form-control" name="administrator" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div class="form-group">
        <label for="location"> Location　</label>
        <input type="text" class="form-control" name="location" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div class="form-group">
        <label for="contact-address"> Contact Address　</label>
        <input type="text" class="form-control" name="contact-address" id="" aria-describedby="helpId" placeholder="">
    </div>

    <div> The Others </div>

    <button type="submit" class="btn btn-primary">Submit</button>

</form>


@endsection