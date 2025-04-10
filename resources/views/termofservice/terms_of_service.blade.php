@extends('layouts.app')
@section('content')
<!DOCTYPE html>
<html data-bs-theme="auto">
	<head>
		<meta charset="utf-8">
		<title>利用規約</title>
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
	  			<h1>**[あなたのECショップ名] 利用規約**</h1>
	  			<p class="m-p">
	  				<a name="title">
	  					**最終更新日：[日付]**
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title1">
	  					### 1. 利用規約への同意
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						このサイトを利用することにより、あなたはこれらの利用規約とプライバシーポリシーを読み、理解し、同意したことを意味します。この規約に同意しない場合は、このサイトを利用しないでください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title2">
	  					### 2. 利用資格
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						このサイトを利用するには、18歳以上であるか、親または保護者の同意を得ている必要があります。このサイトを利用することにより、あなたはこれらの資格要件を満たしていることを表明し、保証します。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title3">
	  					### 3. アカウント登録
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						当サイトの特定の機能にアクセスするには、アカウントを作成する必要があります。登録時に正確かつ最新の情報を提供し、その情報を正確かつ最新の状態に保つことに同意します。また、アカウントとパスワードの機密性を維持し、アカウントへのアクセスを制限する責任があります。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title4">
	  					### 4. 商品情報
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						当社は正確な商品説明と画像を提供するよう努めていますが、商品説明、画像、またはサイト上のその他のコンテンツが正確、完全、信頼性がある、最新である、または誤りがないことを保証するものではありません。サイト上で提供される商品が記載内容と異なる場合は、販売者に確認を求めてください。返品や交換に関する保証はありませんのでご注意ください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title5">
	  					### 5. 注文と支払い
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						このサイトを通じて行われたすべての注文は、当社の承認を受ける必要があります。商品の在庫切れや商品説明や価格の誤り、注文の誤りなど、いかなる理由であっても注文を拒否またはキャンセルする権利を当社は有します。支払いは購入時に行われなければなりません。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title6">
	  					### 6. 配送と納品
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						配送および納品の時間はおおよその目安であり、変更される可能性があります。当社は第三者の配送サービスによる遅延について責任を負いません。詳細については各店舗（販売者）に確認してください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title7">
	  					### 7. 返品と返金
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						支払い後のキャンセルはできませんが、返品や返金を希望する場合は、各店舗に連絡してください。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title8">
	  					### 8. 知的財産権
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						このサイト上のすべてのコンテンツ（テキスト、グラフィック、ロゴ、画像など）は、[あなたのECショップ名]またはそのコンテンツ提供者の所有物であり、著作権、商標、その他の知的財産権法によって保護されています。コンテンツを無断で使用、複製、配布することはできません。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title9">
	  					### 9. 責任の制限
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						法令が許す限り、[あなたのECショップ名]は、サイトの利用またはサイトを通じて購入された商品の使用に起因する間接的、偶発的、特別、結果的、または懲罰的損害について責任を負いません。当社は一切の損害に対して責任を負いません。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title10">
	  					### 10. 免責
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						あなたは、[あなたのショップ名]、その関連会社、ならびにそれらの役員、取締役、従業員および代理人に対して、当サイトの利用またはこれらの利用規約に違反することによって生じるすべての請求に関して賠償し、免責することに同意します。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title11">
	  					### 11. 利用規約の変更
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						当社は、これらの利用規約をいつでも変更する権利を留保します。変更はサイトに掲載された時点で即時に効力を生じます。変更後もこのサイトを引き続き利用することにより、あなたは新しい利用規約に同意したこととなります。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title12">
	  					### 12. 準拠法
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						これらの利用規約は、[あなたの国]の法律に従って解釈され、適用されます。
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a name="title13">
	  					### 13. お問い合わせ
	  				</a>
	  			</p>
	  			<p class="m-p">
	  				<a>	
						これらの利用規約について質問がある場合は、[お問い合わせ先]までご連絡ください。
	  				</a>
	  			</p>		
	  		</main>
			<!-- サイドバー -->
			<aside class="sidebar">
			    <div class="widget widget--sticky">
			    	<h3 class="w3-bar-item">メニュー</h3>
			    	<p class="m-p2"><a href="#title" class="w3-bar-item w3-button">**[あなたのECショップ名] 利用規約**</a></p>
					<p class="m-p2"><a href="#title1" class="w3-bar-item w3-button">### 1. 利用規約への同意</a></p>
					<p><a href="#title2" class="w3-bar-item w3-button">### 2. 利用資格</a></p>
					<p><a href="#title3" class="w3-bar-item w3-button">### 3. アカウント登録</a></p>
					<p><a href="#title4" class="w3-bar-item w3-button">### 4. 商品情報</a></p>
					<p><a href="#title5" class="w3-bar-item w3-button">### 5. 注文と支払い</a></p>
					<p><a href="#title6" class="w3-bar-item w3-button">### 6. 配送と納品</a></p>
					<p><a href="#title7" class="w3-bar-item w3-button">### 7. 返品と返金</a></p>
					<p><a href="#title8" class="w3-bar-item w3-button">### 8. 知的財産権</a></p>
					<p><a href="#title9" class="w3-bar-item w3-button">### 9. 責任の制限</a></p>
					<p><a href="#title10" class="w3-bar-item w3-button">### 10. 免責</a></p>
					<p><a href="#title11" class="w3-bar-item w3-button">### 11. 利用規約の変更</a></p>
					<p><a href="#title12" class="w3-bar-item w3-button">### 12. 準拠法</a></p>
					<p><a href="#title13" class="w3-bar-item w3-button">### 13. お問い合わせ</a></p>
			    </div>
			</aside>				
		</div>
	</body>
</html>	
@endsection
