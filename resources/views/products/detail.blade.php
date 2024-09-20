@extends('layouts.app')

@section('content')
@push('css')
    <link href="{{ asset('front/css/custom3.css') }}" rel="stylesheet">
@endpush

<!-- Slideshow container -->
<div class="slideshow-container">

  <!-- Full-width images with number and caption text -->

  <div class="mySlides">
    <div class="numbertext">1 / 3</div>
		   <div class="text">Caption Text {{ $productDetails['name'] }}</div>

    	<div class="content-detail1">
    		<div class="base_img_box1">

		    	@if($productDetails['cover_img'])
		    		<img class="base_img1" src="{{ asset( 'storage/'.$productDetails['cover_img']  ) }}" style="width:430px; height:430px">
		    	@else
		    		<img class="base_img1" src="{{ asset('images/no_image.jpg') }}" style="width:430px; height:430px">		
		    	@endif

		    </div>		
    	</div>
  </div>

  <div class="mySlides">
    <div class="numbertext">2 / 3</div>
		   <div class="text">Caption Two {{ $productDetails['name'] }}</div>

    	<div class="content-detail2">
    		<div class="base_img_box2">

		    	@if($productDetails['cover_img2'])
		    		<img class="base_img2" src="{{ asset( 'storage/'.$productDetails['cover_img2']  ) }}" style="width:430px; height:430px">
		    	@else
		    		<img class="base_img2" src="{{ asset('images/no_image.jpg') }}" style="width:430px; height:430px">
		    	@endif

		    </div>		
    	</div>
  </div>

  <div class="mySlides">
    <div class="numbertext">3 / 3</div>
		   <div class="text">Caption Two {{ $productDetails['name'] }}</div>

    	<div class="content-detail3">
    		<div class="base_img_box3">
		    
		    	@if($productDetails['cover_img3'])
		    		<img class="base_img3" src="{{ asset( 'storage/'.$productDetails['cover_img3']  ) }}" style="width:430px; height:430px">
		    	@else
		    		<img class="base_img3" src="{{ asset('images/no_image.jpg') }}" style="width:430px; height:430px">
		    	@endif

		    </div>		
    	</div>
  </div>

  <!-- Next and previous buttons -->
  <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
  <a class="next" onclick="plusSlides(1)">&#10095;</a>


</div>
  	<!-- The dots/circles -->
<div style="position: relative;">
  <span class="dot" onclick="currentSlide(1)"></span>
  <span class="dot" onclick="currentSlide(2)"></span>
  <span class="dot" onclick="currentSlide(3)"></span>
</div>

<br>
@if(json_decode($product_movies, true))
	@foreach(json_decode($product_movies, true) as $movie)
			<video controls width="250" src="{{ asset('storage/'.$movie['download_link']) }}#t=0,2" muted class="contents_width"></video>
	@endforeach
@endif	


<script>

let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}    
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active";
}





</script>

@endsection	
