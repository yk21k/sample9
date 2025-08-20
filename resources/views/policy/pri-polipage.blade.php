@extends('layouts.app')
@section('content')
<!DOCTYPE html>
<html data-bs-theme="auto">
	<head>
		<meta charset="utf-8">
		<title>プライバシーポリシー</title>
	    
	</head>
	<body>
	    <!-- スタイルタグを作成するスクリプト -->
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
		      
		      /* head要素にスタイルを追加 */
		      document.getElementsByTagName("head")[0].appendChild(css);
		    }
		    
		    /* スタイル要素を宣言 */
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

		    /* 関数を呼び出し */
		    window.onload = function() { addStyle(styles) };
	    </script>
        


	    <div class="wrapper">
	  		<main class="main">
	  			<h1>プライバシーポリシー</h1>
	  			<p class="m-p">
	  				<a name="title">
	  					[会社名]株式会社（以下「当社」といいます）は、当社が運営するECサイト（以下「本サイト」といいます）におけるお客様の個人情報を適切に保護し、利用するために、以下のプライバシーポリシー（以下「本ポリシー」といいます）を定めます。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title1">
	  					### 1. 個人情報の定義
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						本ポリシーにおける「個人情報」とは、氏名、住所、電話番号、メールアドレス、クレジットカード情報など、特定の個人を識別できる情報を指します。	
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title2">
	  					### 2. 個人情報の収集方法
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						本サイトを利用する際、以下の方法で個人情報を収集することがあります。<br><br>

						- ユーザー登録時の情報入力
						- 購入時の配送先住所や支払い情報
						- お問い合わせフォームからの問い合わせ
						- アンケートやキャンペーン申し込み情報
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title3">
	  					### 3. 個人情報の利用目的
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						収集した個人情報は、以下の目的で利用いたします。<br><br>

						- 商品の注文受付および発送
						- 顧客サポートおよび問い合わせ対応
						- サイト利用状況を分析し、サービスの改善を図る
						- メールマガジンやキャンペーン情報の送付
						- 法令に基づく必要な対応
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title4">
	  					### 4. 個人情報の第三者への提供
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						以下の場合を除き、お客様の個人情報を第三者に提供することはありません。<br><br>

						- お客様の同意がある場合
						- 法令に基づく場合
						- 人命、身体、財産の保護のために必要であり、本人の同意を得ることが困難な場合
						- 業務遂行のために必要な範囲で、委託先に個人情報を提供する場合（例：決済サービス会社）
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title5">
	  					### 5. 個人情報の管理
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						当社は、個人情報の適切な管理を行い、不正アクセス、紛失、改ざん、漏洩等を防ぐために、合理的な安全管理措置を講じます。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title6">
	  					### 6. クッキー等の利用
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						当社は、ユーザーの利便性を向上させ、サイト利用状況を分析するために、クッキー等の技術を利用することがあります。これにより、個人を特定できない情報が自動的に収集される場合があります。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title7">
	  					### 7. 個人情報の開示、訂正、削除
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						お客様は、当社に対して、個人情報の開示、訂正、削除を求めることができます。当社は、法令に基づき、合理的な範囲内で対応いたします。ただし、リクエストの内容に応じて、業務運営や法的義務により、すべてまたは一部のリクエストに応じられない場合があります。これに関するお問い合わせは、以下の連絡先までご連絡ください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title8">
	  					### 8. 未成年者の個人情報
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						18歳未満の方が本サイトを利用する場合は、親権者または保護者の同意を得た上で、個人情報を提供してください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title9">
	  					### 9. プライバシーポリシーの変更
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						当社は、必要に応じて本ポリシーを変更することがあります。変更がある場合は、本サイトでお知らせいたします。改訂後のポリシーは、本サイトに掲示された時点で効力を生じます。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title10">
	  					### 10. お問い合わせ
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						個人情報の取り扱いに関するお問い合わせは、以下までご連絡ください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title11">
	  					【お問い合わせ】
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						株式会社[会社名]  
						住所：[所在地]  
						電話番号：[電話番号]  
						メールアドレス：[メールアドレス]
	  				</a>
	  			</p>	
	  		</main>
			<!-- サイドバー -->
			<aside class="sidebar">
			    <div class="widget widget--sticky">
			    	
			    	<h3 class="w3-bar-item">メニュー</h3>
			    	
					<p class="m-p2"><a href="#title" class="w3-bar-item w3-button">タイトル</a></p>
					<p class="m-p2"><a href="#title1" class="w3-bar-item w3-button">### 1. 個人情報の定義</a></p>
					<p><a href="#title2" class="w3-bar-item w3-button">### 2. 個人情報の収集方法</a></p>
					<p><a href="#title3" class="w3-bar-item w3-button">### 3. 個人情報の利用目的</a></p>
					<p><a href="#title4" class="w3-bar-item w3-button">### 4. 個人情報の第三者への提供</a></p>
					<p><a href="#title5" class="w3-bar-item w3-button">### 5. 個人情報の管理</a></p>
					<p><a href="#title6" class="w3-bar-item w3-button">### 6. クッキー等の利用</a></p>
					<p><a href="#title7" class="w3-bar-item w3-button">### 7. 個人情報の開示、訂正、削除</a></p>
					<p><a href="#title8" class="w3-bar-item w3-button">### 8. 未成年者の個人情報</a></p>
					<p><a href="#title9" class="w3-bar-item w3-button">### 9. プライバシーポリシーの変更</a></p>
					<p><a href="#title10" class="w3-bar-item w3-button">### 10. お問い合わせ</a></p>
					<p><a href="#title11" class="w3-bar-item w3-button">【お問い合わせ】</a></p>
			    </div>
			</aside>				
		</div>
	</body>
</html>	
@endsection
