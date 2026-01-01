{{-- footer css --}}
<link rel="stylesheet" href="{{ asset('front/css/footer.css') }}">


<footer class="site-footer">
	<div class="footer-scroll">
	    <div class="footer-inner">

	        <section class="footer-section">
	            <h4 class="footer-title">運営者情報</h4>
	            <ul class="footer-list">
	                <li><a href="{{ url('/company-profile') }}">Company Profile</a></li>
	                <li><a href="{{ url('/contact') }}">Contact Us</a></li>
	            </ul>
	        </section>

	        <section class="footer-section">
	            <h4 class="footer-title">出店者向け</h4>
	            <ul class="footer-list">
	                <li><a href="{{ url('/shop-prof') }}">Company Profile (Product Seller)</a></li>
	                <li><a href="{{ url('/listing_terms') }}">Listing terms</a></li>
	            </ul>
	        </section>

	        <section class="footer-section">
	            <h4 class="footer-title">規約・ポリシー</h4>
	            <ul class="footer-list">
	                <li><a href="{{ url('/privacy-policy') }}">Privacy Policy</a></li>
	                <li><a href="{{ url('/personal-information') }}">Personal Information</a></li>
	                <li><a href="{{ url('/terms-of-service') }}">Terms of Service</a></li>
	                <li>
	                    <a href="https://www.recall.caa.go.jp/" target="_blank" rel="noopener">
	                        消費者庁リコール情報
	                    </a>
	                </li>
	            </ul>
	        </section>

	    </div>

	    <div class="footer-bottom">
	        <small>© {{ date('Y') }} Your Mall Name</small>
	    </div>
	</div>    
</footer>

