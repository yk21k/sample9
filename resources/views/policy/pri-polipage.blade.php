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


	   <!-- アイコンの設定 -->
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
	  			<p>
				  <a href="#link">クリックすると飛びます</a><br><br><br><br><br><br><br><br><br><br><br><br><br>
				</p>
				<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
				<p style="padding-top: 1000px">
				  <a name="link">ジャンプ先です</a>
				</p>	
	  		</main>
			<!-- Sidebar -->
			<aside class="sidebar">
			    <div class="widget widget--sticky">
			    	<!-- ドロップダウンボタン -->
				    <div class="dropdown position-fixed mb-3 me-3 bd-mode-toggle">
				      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="テーマの切替（ダーク）">
				        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
				        <span class="visually-hidden" id="bd-theme-text">カラーモードの切替</span>
				      </button>
				      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
				        <li>
				          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
				            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#sun-fill"></use></svg>
				            ライト
				            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
				          </button>
				        </li>
				        <li>
				          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="dark" aria-pressed="true">
				            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
				            ダーク
				            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
				          </button>
				        </li>
				        <li>
				          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="false">
				            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#circle-half"></use></svg>
				            オート
				            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
				          </button>
				        </li>
				      </ul>
				    </div>
			    	<h3 class="w3-bar-item">Menu</h3>
			    	
					<p class="m-p2"><a href="#title" class="w3-bar-item w3-button">Title</a></p>
					<p class="m-p2"><a href="#title1" class="w3-bar-item w3-button">Definition of Personal Information</a></p>
					<p><a href="#" class="w3-bar-item w3-button">Link 2</a></p>
					<p><a href="#" class="w3-bar-item w3-button">Link 3</a></p>
			    </div>
			</aside>				
		</div>
	</body>
</html>	