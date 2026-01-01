{{-- footer css --}}
<link rel="stylesheet" href="{{ asset('front/css/footer.css') }}">

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-section">
            <h3 class="footer-title">Company Profile</h3>
            <ul class="footer-list">
                <li><a href="{{ url('/privacy-policy') }}">Privacy Policy</a></li>
                <li><a href="{{ url('/personal-information') }}">Personal Information</a></li>
                <li><a href="{{ url('/terms-of-service') }}">Terms of Service</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3 class="footer-title">Product Seller</h3>
            <ul class="footer-list">
                <li><a href="{{ url('/shop-prof') }}">Company Profile (Product Seller)</a></li>
                <li><a href="{{ url('/listing_terms') }}">Listing Terms</a></li>
                <li><a href="https://www.recall.caa.go.jp/" target="_blank">消費者庁リコール情報サイト</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; 2026 Your Company
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const footer = document.querySelector('.site-footer');
    let lastScrollY = window.scrollY;

    window.addEventListener('scroll', () => {
		const scrollBottom = window.scrollY + window.innerHeight;

		if (scrollBottom >= document.body.scrollHeight - 100) {
		    footer.classList.add('show');
		} else {
		    footer.classList.remove('show');
		}

        lastScrollY = currentScrollY;
    }, { passive: true });
});
</script>



