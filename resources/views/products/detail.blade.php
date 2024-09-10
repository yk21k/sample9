@extends('layouts.app')

@section('content')
@push('css')
    <link href="{{ asset('front/css/custom3.css') }}" rel="stylesheet">
@endpush
<style>
#preview {
  display: grid;
  justify-content: right;
}
</style>
<div class="wrap">
	<div class="scale-box">
		<div class="scale-img">
			<a class="magnifier-thumb-wrapper" href="{{ asset( 'storage/'.$productDetails['cover_img'] ) }}">
				<img id="thumb" src="{{ asset( 'storage/'.$productDetails['cover_img']  ) }}" ><br><br>
			</a>
			<div class="magnifier-preview" id="preview" style="width: 176px; height: 176px;">{{ $productDetails['name']  }}</div>	
		</div>

		

	</div>
</div>	

<div class="scale-img">
	
	<a>{{ $productDetails['cover_img'] }}</a>

</div>
  
		

@endsection	
