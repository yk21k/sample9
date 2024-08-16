@extends('layouts.app')


@section('content')


<h2>Submit Your Shop </h2>
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
  <form class="h-adr" action="{{route('shops.store')}}" method="post" enctype="multipart/form-data">@csrf
    {{-- {{ csrf_field() }} --}}

    <div class="form-group">
        <label for="name"><h3>Name of Shop *</h3></label>
        <input type="text" class="form-control" name="name" id="name" aria-describedby="helpId" placeholder="" required>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="description"><h3>Description *</h3></label>
        <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="representative"> <h3>Representative *</h3></label>
        <input type="text" class="form-control" name="representative" id="representative" aria-describedby="helpId" placeholder="" required>
    </div>
    <br>

    <div class="form-group">
        <label for="location_1"> <h3>Location * </h3><small>Please enter the address after entering the postal code.</small></label><br>
        <span class="p-country-name" style="display:none;">Japan</span>
        <label for="post-code">Postal Code:</label>
        <input type="text" class="p-postal-code" size="8" maxlength="8"><br>
        <input type="text" class="form-control p-region p-locality p-street-address p-extended-address" name="location_1" id="location_1" aria-describedby="helpId" placeholder="" required>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="location_2"> <h3>Mailable Address </h3></label>
        <input type="text" class="form-control" name="location_2" id="location_2" aria-describedby="helpId" placeholder="Please enter the address if different from the address above.">
    </div>
    &nbsp;

    <div class="form-group">
        <label for="telephone"> <h3>Telephone *</h3></label>
        <input type="text" class="form-control" id="telephone" name="telephone" aria-describedby="helpId" placeholder="You can accept numbers with HYPHEN from both mobile and landline phones." required>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="email"> <h3>Email *</h3></label>
        <input type="email" class="form-control" name="email" id="email" aria-describedby="helpId" placeholder="" required>
    </div>
    &nbsp;

    <label for="identification_1" class="form-label"><h3>ID card (driver's license or passport)</h3></label>
    <input class="form-control" list="identification1" name="identification_1" id="identification_1" >
      <datalist id="identification1">
          <option value="driver's license">
          <option value="passport">
      </datalist>
    &nbsp;

    <label for="identification_2" class="form-label"><h3>Identification　(Individual business start notification certificate or Corporate registration)</h3></label>
    <input class="form-control" list="identification2" name="identification_2" id="identification_2" required>
      <datalist id="identification2">
          <option value="Individual business start notification certificate or Corporate">
          <option value="Corporate registration">
      </datalist>
    &nbsp;

    <label for="identification_3" class="form-label"><h3>Identification(Resident record business card) </h3></label>
    <input class="form-control" list="identification3" name="identification_3" id="identification_3" required>
      <datalist id="identification3">
          <option value="Resident record">
          <option value="business card">
      </datalist>
    &nbsp;
    &nbsp;
    &nbsp;

    <div class="form-group">
        <label for="photo_1">  <h3>Photo1(Contact person) *</h3></label>
        <input type="file" class="form-control" id="photo_1" name="photo_1" class="form-control" multiple>
    </div>
    &nbsp;
    &nbsp;

    <div class="form-group">
        <label for="photo_2">  <h3>Photo2(Other personnel1)</h3></label>
        <input type="file" class="form-control" id="photo_2" name="photo_2" class="form-control" multiple>

    </div>
    &nbsp;

    <div class="form-group">
        <label for="photo_3">  <h3>Photo3(Other personnel2)</h3></label>
        <input type="file" class="form-control" id="photo_3" name="photo_3" class="form-control" multiple>

    </div>
    &nbsp;

    <div class="form-group">
        <label for="file_1">  <div id="output1" style="font-size:20pt">file_1 * (Compatible with jpg, jpeg, png, pdf)</div></label>
        <input type="file" class="form-control" id="file_1" name="file_1" class="form-control" multiple>

    </div>
    &nbsp;

    <div class="form-group">
        <label for="file_2">  <div id="output2" style="font-size:20pt">file_2 * (Compatible with PDF only)</div></label>
        <input type="file" class="form-control" id="file_2" name="file_2" class="form-control" multiple>

    </div>
    &nbsp;

    <div class="form-group">
        <label for="file_3"> <div id="output3" style="font-size:20pt">file_3 * (Compatible with jpg, jpeg, png, pdf)</div></label>
        <input type="file" class="form-control" id="file_3" name="file_3" class="form-control" multiple>

    </div>
    &nbsp;

    <div class="form-group">
        <label for="file_4">  <h3>file_4 (Compatible with PDF only)　Please use it when necessary</h3></label>
        <input type="file" class="form-control" id="file_4" name="file_4" class="form-control" multiple>

    </div>
    &nbsp;

    <div class="form-group">
        <label for="manager">  <h3>manager *</h3></label>
        <input type="text" class="form-control" name="manager" id="manager" aria-describedby="helpId" placeholder="" >
    </div>
    &nbsp;

    <div class="form-group">
        <label for="product_type">  <h3>product_type *</h3></label>
        <input type="text" class="form-control" name="product_type" id="product_type" aria-describedby="helpId" placeholder="" >
    </div>
    &nbsp;

    <div class="form-group">
        <label for="volume">  <h3>volume *</h3></label>
        <input type="text" class="form-control" name="volume" id="volume" aria-describedby="helpId" placeholder="">
    </div>
    &nbsp;

    <div class="form-group">
        <label for="note">  <h3>note</h3></label>
        <input type="text" class="form-control" name="note" id="" aria-describedby="helpId" placeholder="">
    </div>
    &nbsp;


    <button type="submit" class="btn btn-primary mt-3">Submit</button>
  </form>
</div>
&nbsp;





@endsection