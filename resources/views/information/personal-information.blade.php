@extends('layouts.app')
@section('content')
<!DOCTYPE html>
<html data-bs-theme="auto">
	<head>
		<meta charset="utf-8">
		<title>個人情報の取り扱いに関する同意</title>
	</head>
	<body>
	    <!-- スタイルタグを作成するためのスクリプト -->
	    <script type="text/javascript">
	  
		    /* スタイルを追加する関数 */
		    function addStyle(styles) {
		      
		      /* スタイル要素を作成 */
		      var css = document.createElement('style');
		      css.type = 'text/css';
		  
		      if (css.styleSheet) 
		        css.styleSheet.cssText = styles;
		      else 
		        css.appendChild(document.createTextNode(styles));
		      
		      /* ヘッド要素にスタイルを追加 */
		      document.getElementsByTagName("head")[0].appendChild(css);
		    }
		    
		    /* スタイル要素の宣言 */
		    var styles = 'h1 {padding: 1rem 2rem; color: #fff; background: #327a33; -webkit-box-shadow: 5px 5px 0 #007032; box-shadow: 5px 5px 0 #007032;} ';
		    styles += ' body { text-align: center } ';
		    styles += ' #header { height: 50px; background: green } ';
		    styles += ' .wrapper {width: 94%; max-width: 1200px; margin: 0 auto; display: flex; justify-content:space-between;} ';
		    styles += ' .main {width: calc(100% - 150px);} ';
		    styles += ' p.m-p {text-align: left; color: #fff; background: #2c6594;} ';
		    styles += ' p.m-p2 {text-align: center;} ';

		    styles += ' .sidebar {width: 280px;} ';
		    styles += ' .widget--sticky {position: sticky; top: 20px;} ';
		    
		    styles += ' .widget--sticky {position: sticky; top: 20px;} ';   

		    /* 関数呼び出し */
		    window.onload = function() { addStyle(styles) };
	    </script>

	    <div class="wrapper">
	  		<main class="main">
	  			<h1>**個人情報の取り扱いに関する同意**</h1>
	  			<p class="m-p">
	  				<a name="title">
	  					お客様（以下「ユーザー」といいます）は、[会社名]株式会社（以下「会社」といいます）が運営するECサイト（以下「サイト」といいます）を利用する際に、以下の内容に従ってユーザーの個人情報を収集、利用、保存することに同意するものとします。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title1">
	  					### 1. 収集する個人情報
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						このサイトの運営およびサービス提供のために、以下の個人情報を収集します。<br><br>

						- 身分証明
						- 住所
						- 電話番号
						- Eメールアドレス
						- クレジットカード情報などの支払い情報
						- 商品の配送先情報
						- ユーザーIDやパスワードなどのアカウント情報
						- 購入履歴、閲覧履歴、アクセスログなどのサイト使用情報
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title2">
	  					### 2. 個人情報の利用目的
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						収集した個人情報は、以下の目的で利用します。<br><br>

						- ユーザーからの注文受付および商品の発送
						- 支払い処理および購入履歴の管理
						- ユーザーからの問い合わせ対応
						- ユーザーの同意を得た上でのメールニュースレターおよびキャンペーン情報の提供
						- サービスの改善およびサイトの運営管理
						- 法律および規制への対応および必要な通知
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title3">
	  					### 3. 第三者への個人情報提供
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						会社は、以下の場合を除き、ユーザーの個人情報を第三者に提供しません。<br><br>

						- ユーザーの同意を得た場合
						- 法律で要求される場合
						- サイトの運営に必要な業務委託先（支払い代行業者等）に提供する場合
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title4">
	  					### 4. 個人情報の保管および管理
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						収集した個人情報は適切に管理し、不正アクセス、情報漏洩、改ざん、紛失等を防止するために合理的な安全措置を講じます。ユーザーが同意を撤回するか、目的が達成された場合には、適切な方法で個人情報を削除します。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title5">
	  					### 5. 同意の撤回および個人情報の開示、訂正、削除
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						個人情報の利用に対する同意を撤回したい場合、または個人情報の開示、訂正、削除を希望する場合は、当社にリクエストをすることができます。会社は、法令に従い合理的かつ必要な範囲でこれらのリクエストに対応します。ただし、同意を撤回したり、個人情報を削除したりすると、当社のサービスの全部または一部を利用できなくなる場合があります。これらに関するお問い合わせは以下の連絡先までご連絡ください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title6">
	  					### 6. クッキー等の利用
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						当社は、ユーザーがサイトをどのように利用しているかを分析し、利便性向上のためにクッキーや類似の技術を使用する場合があります。クッキーの利用に関する詳細は、クッキーポリシーをご参照ください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title7">
	  					### 7. お問い合わせ
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						個人情報の取り扱いに関するお問い合わせは、以下の連絡先までご連絡ください。<br><br>

						【お問い合わせ】  
						株式会社[会社名]  
						住所: [事業所住所]  
						電話番号: [電話番号]  
						メールアドレス: [メールアドレス]

						---<br><br>

						**同意**

						本サイトをご利用いただくことで、上記の「個人情報の取り扱いに関する同意」に同意したものと見なされます。

						---
	  				</a>	
	  		</main>
			<!-- サイドバー -->
			<aside class="sidebar">
			    <div class="widget widget--sticky">

			    	<h3 class="w3-bar-item">メニュー</h3>
			    	
					<p class="m-p2"><a href="#title" class="w3-bar-item w3-button">タイトル</a></p>
					<p class="m-p2"><a href="#title1" class="w3-bar-item w3-button">### 1. 個人情報の定義</a></p>
					<p><a href="#title2" class="w3-bar-item w3-button">### 2. どのように個人情報を収集するか</a></p>
					<p><a href="#title3" class="w3-bar-item w3-button">### 3. 第三者への個人情報の提供</a></p>
					<p><a href="#title4" class="w3-bar-item w3-button">### 4. 個人情報の保管および管理</a></p>
					<p><a href="#title5" class="w3-bar-item w3-button">### 5. 個人情報の管理</a></p>
					<p><a href="#title6" class="w3-bar-item w3-button">### 6. クッキー等の利用</a></p>
					<p><a href="#title7" class="w3-bar-item w3-button">### 7. 個人情報の開示、訂正、削除</a></p>
					<p><a href="#title8" class="w3-bar-item w3-button">### 8. 未成年者の個人情報</a></p>
					<p><a href="#title9" class="w3-bar-item w3-button">### 5. 同意の撤回および個人情報の開示、訂正、削除</a></p>
					<p><a href="#title10" class="w3-bar-item w3-button">### 6. クッキー等の利用</a></p>
					<p><a href="#title11" class="w3-bar-item w3-button">### 7. お問い合わせ</a></p>
			    </div>
			</aside>				
		</div>
	</body>
</html>	
@endsection
