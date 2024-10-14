<!DOCTYPE html>
<html data-bs-theme="auto">
	<head>
		<meta charset="utf-8">
		<title>Privacy Policy</title>
	    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" >
	</head>
	<body>
	    <!-- Script to create style tag --> 
	    <script type="text/javascript">
	  
		    /* Function to add style */
		    function addStyle(styles) {
		      
		      /* Create style element */
		      var css = document.createElement('style');
		      css.type = 'text/css';
		  
		      if (css.styleSheet) 
		        css.styleSheet.cssText = styles;
		      else 
		        css.appendChild(document.createTextNode(styles));
		      
		      /* Append style to the head element */
		      document.getElementsByTagName("head")[0].appendChild(css);
		    }
		    
		    /* Declare the style element */
		    var styles = 'h1 {padding: 1rem 2rem; color: #fff; background: #327a33; -webkit-box-shadow: 5px 5px 0 #007032; box-shadow: 5px 5px 0 #007032;} ';
		    styles += ' body { text-align: center } ';
		    styles += ' #header { height: 50px; background: green } ';
		    styles += ' .wrapper {width: 94%; max-width: 1200px; margin: 0 auto; display: flex; justify-content:space-between;} ';
		    styles += ' .main {width: calc(100% - 150px);} ';
		    styles += ' p.m-p {text-align: left; color: #fff; background: #7da3a1;} ';
		    styles += ' p.m-p2 {text-align: center;} ';

		    styles += ' .sidebar {width: 280px;} ';
		    styles += ' .widget--sticky {position: sticky; top: 20px;} ';
		    styles += ' .btn-bd-primary {--bd-violet-bg: #7da3a1; --bd-violet-rgb: 112.520718, 44.062154, 249.437846; --bs-btn-font-weight: 600; --bs-btn-color: var(--bs-white); --bs-btn-bg: var(--bd-violet-bg); --bs-btn-border-color: var(--bd-violet-bg); --bs-btn-hover-color: var(--bs-white); --bs-btn-hover-bg: #28c1e0; --bs-btn-hover-border-color: #28c1e0; --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb); --bs-btn-active-color: var(--bs-btn-hover-color); --bs-btn-active-bg: #3657c2; --bs-btn-active-border-color: #3657c2;} ';
		    styles += ' .bd-mode-toggle {z-index: 1500;} ';
		    styles += ' [data-bs-theme=dark] .element {color: var(--bs-primary-text-emphasis); background-color: var(--bs-primary-bg-subtle);} ';
		    styles += ' @media (prefers-color-scheme: dark) {.element {color: var(--bs-primary-text-emphasis); background-color: var(--bs-primary-bg-subtle);}} ';
		    styles += ' .widget--sticky {position: sticky; top: 20px;} ';   

		    /* Function call */
		    window.onload = function() { addStyle(styles) };
	    </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
			 /*!
			 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
			 * Copyright 2011-2023 The Bootstrap Authors
			 * Licensed under the Creative Commons Attribution 3.0 Unported License.
			 */

			(() => {
			  'use strict'

			  const getStoredTheme = () => localStorage.getItem('theme')
			  const setStoredTheme = theme => localStorage.setItem('theme', theme)

			  const getPreferredTheme = () => {
			    const storedTheme = getStoredTheme()
			    if (storedTheme) {
			      return storedTheme
			    }

			    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
			  }

			  const setTheme = theme => {
			    if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			      document.documentElement.setAttribute('data-bs-theme', 'dark')
			    } else {
			      document.documentElement.setAttribute('data-bs-theme', theme)
			    }
			  }

			  setTheme(getPreferredTheme())

			  const showActiveTheme = (theme, focus = false) => {
			    const themeSwitcher = document.querySelector('#bd-theme')

			    if (!themeSwitcher) {
			      return
			    }

			    const themeSwitcherText = document.querySelector('#bd-theme-text')
			    const activeThemeIcon = document.querySelector('.theme-icon-active use')
			    const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
			    const svgOfActiveBtn = btnToActive.querySelector('svg use').getAttribute('href')

			    document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
			      element.classList.remove('active')
			      element.setAttribute('aria-pressed', 'false')
			    })

			    btnToActive.classList.add('active')
			    btnToActive.setAttribute('aria-pressed', 'true')
			    activeThemeIcon.setAttribute('href', svgOfActiveBtn)
			    const themeSwitcherLabel = `${themeSwitcherText.textContent} (${btnToActive.dataset.bsThemeValue})`
			    themeSwitcher.setAttribute('aria-label', themeSwitcherLabel)

			    if (focus) {
			      themeSwitcher.focus()
			    }
			  }

			  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
			    const storedTheme = getStoredTheme()
			    if (storedTheme !== 'light' && storedTheme !== 'dark') {
			      setTheme(getPreferredTheme())
			    }
			  })

			  window.addEventListener('DOMContentLoaded', () => {
			    showActiveTheme(getPreferredTheme())

			    document.querySelectorAll('[data-bs-theme-value]')
			      .forEach(toggle => {
			        toggle.addEventListener('click', () => {
			          const theme = toggle.getAttribute('data-bs-theme-value')
			          setStoredTheme(theme)
			          setTheme(theme)
			          showActiveTheme(theme, true)
			        })
			      })
			  })
			})()
        </script>


	   	<!-- Icon settings -->
	    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
	      <symbol id="circle-half" viewBox="0 0 16 16">
	        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
	      </symbol>
	      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
	        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
	        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
	      </symbol>
	      <symbol id="sun-fill" viewBox="0 0 16 16">
	        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
	      </symbol>
	    </svg>

	    <div class="wrapper">
	  		<main class="main">
	  			<h1>Privacy Policy</h1>
	  			<p class="m-p">
	  				<a name="title">
	  					[Company Name] Co., Ltd. (hereinafter referred to as "the Company") has established the following privacy policy (hereinafter referred to as the "Policy") in order to appropriately protect and use the personal information of customers on the EC site (hereinafter referred to as the "Site") operated by the Company.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title1">
	  					### 1. Definition of Personal Information
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						For the purposes of this Policy, "personal information" refers to information about an individual that can identify a specific individual, such as name, address, telephone number, email address, and credit card information.	
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title2">
	  					### 2. How do we collect personal information?
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						When you use this site, we may collect personal information in the following ways.<br><br>

						- Input of information at the time of user registration
						- Shipping address and payment information at the time of purchase
						- Inquiries from the inquiry form
						Questionnaire and campaign application information
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title3">
	  					### 3. Purpose of use of personal information
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						We will use the collected personal information for the following purposes.<br><br>

						- Acceptance and delivery of orders for products
						- Customer support and inquiries
						- Improvement of services by analyzing site usage
						- Sending e-mail newsletters and campaign information (if the customer agrees)
						Necessary actions based on laws and regulations
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title4">
	  					### 4. Provision of Personal Information to Third Parties
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						We will not provide your personal information to third parties except in the following cases.<br><br>

						- With the consent of the customer
						- When required by law
						- When it is necessary to protect the life, body, or property of an individual and it is difficult to obtain the consent of the person
						When providing personal information to a subcontractor to the extent necessary for business execution (e.g., a payment service company)
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title5">
	  					### 5. Management of personal information
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						We will take reasonable safety measures to properly manage your personal information and prevent unauthorized access, loss, falsification, leakage, etc.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title6">
	  					### 6. Use of Cookies, etc.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						We may use cookies and similar technologies to improve user convenience and analyze site usage. This may automatically collect information that does not identify you personally.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title7">
	  					### 7. Disclosure, Correction and Deletion of Personal Information
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						You may request that we disclose, correct, or delete your personal information. The Company will respond to such requests within a reasonable range in accordance with laws and regulations. However, depending on the content of the request, we may not be able to respond to some or all of the requests due to our business operations or legal obligations. For inquiries regarding these, please contact the following.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title8">
	  					### 8. Personal Information of Minors
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						If you are under the age of 18 and wish to use this site, please provide personal information with the consent of a parent or guardian.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title9">
	  					### 9. Changes to the Privacy Policy
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						The Company may change this policy as necessary. Any changes will be announced on this site. The revised policy shall take effect when it is posted on this site.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title10">
	  					### 10. Inquiries
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						For inquiries regarding the handling of personal information, please contact the following.
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title11">
	  					【Inquiries】
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						FCo., Ltd. [Company Name]  
						Address: [Business Address]  
						Phone Number: [Phone Number]  
						E-mail Address: [e-mail address]
	  				</a>
	  			</p>	
	  		</main>
			<!-- Sidebar -->
			<aside class="sidebar">
			    <div class="widget widget--sticky">
			    	<!-- drop down button -->
				    <div class="dropdown position-fixed mb-3 me-3 bd-mode-toggle">
				      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="テーマの切替（ダーク）">
				        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
				        <span class="visually-hidden" id="bd-theme-text">カラーモードの切替</span>
				      </button>
				      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
				        <li>
				          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
				            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#sun-fill"></use></svg>
				            light
				            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
				          </button>
				        </li>
				        <li>
				          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="dark" aria-pressed="true">
				            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
				            dark
				            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
				          </button>
				        </li>
				        <li>
				          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="false">
				            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#circle-half"></use></svg>
				            auto
				            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
				          </button>
				        </li>
				      </ul>
				    </div>
			    	<h3 class="w3-bar-item">Menu</h3>
			    	
					<p class="m-p2"><a href="#title" class="w3-bar-item w3-button">Title</a></p>
					<p class="m-p2"><a href="#title1" class="w3-bar-item w3-button">### 1. Definition of Personal Information</a></p>
					<p><a href="#title2" class="w3-bar-item w3-button">### 2. How do we collect personal information?</a></p>
					<p><a href="#title3" class="w3-bar-item w3-button">### 3. Purpose of use of personal information</a></p>
					<p><a href="#title4" class="w3-bar-item w3-button">### 4. Provision of Personal Information to Third Parties</a></p>
					<p><a href="#title5" class="w3-bar-item w3-button">### 5. Management of personal information</a></p>
					<p><a href="#title6" class="w3-bar-item w3-button">### 6. Use of Cookies, etc.</a></p>
					<p><a href="#title7" class="w3-bar-item w3-button">### 7. Disclosure, Correction and Deletion of Personal Information</a></p>
					<p><a href="#title8" class="w3-bar-item w3-button">### 8. Personal Information of Minors</a></p>
					<p><a href="#title9" class="w3-bar-item w3-button">### 9. Changes to the Privacy Policy</a></p>
					<p><a href="#title10" class="w3-bar-item w3-button">### 10. Inquiries</a></p>
					<p><a href="#title11" class="w3-bar-item w3-button">【Inquiries】</a></p>
			    </div>
			</aside>				
		</div>
	</body>
</html>	
