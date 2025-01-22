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
			<video controls width="60%" src="{{ asset('storage/'.$movie['download_link']) }}#t=0,2" muted class="contents_width"></video>
	@endforeach
@endif	
<br>
@if(isset(auth()->user()->id))
    @if($search_order_ids)
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
        Launch Favorite
        </button>
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <!-- Scrollable modal -->    
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Favorite</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        <form action="{{ route('products.favorite', ['id'=>Auth::user()->id]) }}" class="row gy-4 gx-3 align-items-center" method="POST">@csrf
                        <input type="hidden" name="user_id" id="" value="{{ auth()->user()->id }}">

                        <input type="hidden" name="shop_id" id="" value="{{ $productDetails['shop_id'] }}"> 

                        <input type="hidden" name="product_id" id="" value="{{ $id }}"> 
                        <div class="col-auto">
                            <label class="form-check-label" for="autoSizingCheck">Favorite
                            </label>
                            <select class="form-select" id="autoSizingSelect" name="wants">
                                <option selected>Choose...</option>
                                <option value="5">Five(like)</option>
                                <option value="4">Four(I obviously don't hate it)</option>
                                <option value="3">Three(I don't like it or dislike it.)</option>
                                <option value="2">Two(Maybe I'm not good at it)</option>
                                <option value="1">One(dislike)</option>
                            </select>
                            
                        </div>

                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="hidden" id="autoSizingCheck" name="store_personnel" value="0">
                                <input class="form-check-input" type="checkbox" id="autoSizingCheck" name="store_personnel" value="1">
                                <label class="form-check-label" for="autoSizingCheck">
                                    Store Person
                                </label>
                            </div>    
                        </div>                                                
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="hidden" id="autoSizingCheck" name="agree" value="0">
                                <input class="form-check-input" type="checkbox" id="autoSizingCheck" name="agree" value="1">
                                <label class="form-check-label" for="autoSizingCheck">
                                    <a href="{{ url('/privacy-policy') }}">Agree</a>
                                </label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-floating">
                              <textarea class="form-control" placeholder="Leave a comment here" name="review" id="floatingTextarea2" style="height: 100px"></textarea>
                              <label for="floatingTextarea2">Review</label>
                            </div>    
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                        </form>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
        </div>
    @endif
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
