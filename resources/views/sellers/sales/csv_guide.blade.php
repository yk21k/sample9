{{-- resources/views/sellers/sales/csv_guide.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>CSVダウンロードガイド</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color:#222 }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; font-size:14px }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left }
        th { background-color: #f7f7f7 }
        .section { margin-bottom: 28px }
        .note { background:#fff8e1; padding:10px; border-left:4px solid #ffd54f }
        .muted { color:#666; font-size:13px }
        .formula { font-family:monospace; background:#f4f8fb; padding:6px; display:inline-block }
    </style>
</head>
<body>

<h1>CSVダウンロードガイド（ダミーデータ）</h1>

<div class="section">
    <h2>主なポイント（要約）</h2>
    <ul>
        <li>このファイルはダミーデータと解説をまとめたガイドです。実データの構造や CSV の列説明、計算ルールを確認できます。</li>
        <li>出力は「店舗ごと（1ページ＝1店舗）」を想定しています。ダウンロード時は該当店舗の売上のみを含めてください。</li>
        <li>課税判定は<strong>出品者のインボイス番号（出品者登録番号）の有無</strong>で行います（有：課税業者、無：免税業者）。</li>
        <li>キャンペーン・クーポンがある場合は<strong>最安値ベース（＝割引後の最も低い単価）</strong>を 1 個目に適用し、2 個目以降は通常単価で計算します。</li>
        <li>配送料は「配送料単価 × 個数」の合計を用い、課税事業者の場合は配送料にも消費税を掛けます。</li>
        <li>サイト運営者への手数料は **税込み金額** として扱い、入金予定金額から差し引きます。</li>
    </ul>
</div>

<div class="section">
    <h2>日付フィールドの説明（CSV 列）</h2>
    <table>
        <thead>
            <tr><th>列名</th><th>意味</th><th>補足 / 例</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>購入日</td>
                <td>お客様がカード決済（注文確定）した日</td>
                <td class="muted">例：注文作成日時 → 2025-09-08</td>
            </tr>
            <tr>
                <td>取引日（到着確認日）</td>
                <td>購入者が商品到着を確認した日（受取確認/到着確認日）</td>
                <td class="muted">購入者が到着を確定しない場合は空欄になります。</td>
            </tr>
            <tr>
                <td>入金日（手数料以外）</td>
                <td>出品者に対して当サイトが入金した日（手数料は差し引かれている／別扱い）</td>
                <td class="muted">振込日など。サイト手数料は別列で表示します。</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h2>税区分の判定方法</h2>
    <p class="muted">CSV の <strong>税区分</strong> 列は、出品者の <em>出品者登録番号（インボイス番号）</em> の有無で自動判定します。</p>
    <ul>
        <li>登録番号あり → <strong>課税業者</strong>（消費税を計算して表示）</li>
        <li>登録番号なし → <strong>免税業者</strong>（消費税は 0／表示なし）</li>
    </ul>
</div>

<div class="section">
    <h2>割引（キャンペーン／クーポン）の適用ルール（詳細）</h2>

    <p>CSV では各商品について以下の手順で割引・税額を計算します。</p>

    <ol>
        <li><strong>候補価格を算出</strong>
            <ul>
                <li>通常単価 = <span class="formula">unit_price</span></li>
                <li>キャンペーン価格 = <span class="formula"></span>（該当する期間かつ条件を満たす場合）</li>
                <li>クーポン価格 = <span class="formula"></span>（そのクーポンが当該商品に紐づく場合）</li>
            </ul>
        </li>

        <li><strong>適用ルール（最安値）</strong>
            <p>上記で算出した候補のうち <strong>最も低い価格</strong> を <em>割引後単価</em> として 1 個目に適用します。複数クーポンがある場合は、<strong>全てのクーポン候補をチェックし</strong>、対象商品に合致するものの中で最小の割引価格を採用します。</p>
        </li>

        <li><strong>複数個購入時の取り扱い</strong>
            <p>数量が 2 個以上の場合は 1 個目に上記の割引（最安値）を適用し、2 個目以降は通常単価（unit_price）で計算します。</p>
        </li>

        <li><strong>キャンペーンとクーポンの同時存在</strong>
            <p>同じ商品に対してキャンペーンとクーポンが同時に存在する場合でも、両方の候補価格を比較し <strong>最小の金額</strong> を採用します（＝最安値優先）。</p>
        </li>
    </ol>

    <div class="note">補足：キャンペーンはショップ単位で設定されることが一般的です（同ショップ内の対象商品に対して適用）。クーポンは商品単位で紐づけられます。両者とも「同一商品に対して1点分だけ適用する」仕様になっている点に注意してください。</div>
</div>

<div class="section">
    <h2>消費税（課税業者の場合）の計算例</h2>

    <p>以下は消費税率 10% を想定したサンプル計算です（税抜ベースで計算）。</p>

    <h4>例：単価 10,000 円、クーポンで -500、キャンペーンで 10% 引き、数量 2、配送料 2,000/個、サイト手数料（税込）1,500</h4>

    <ul>
        <li>通常単価 = 10,000</li>
        <li>キャンペーン価格 = 9,000</li>
        <li>クーポン価格 = 9,500</li>
        <li>割引後単価（最安） = 9,000（1 個目）</li>
        <li>2 個目 = 10,000（通常単価）</li>
        <li>商品小計（税抜） = 9,000 + 10,000 = 19,000</li>
        <li>商品消費税 = 900 + 1,000 = 1,900</li>
        <li>配送料（税抜） = 2,000 × 2 = 4,000</li>
        <li>配送料消費税 = 400</li>
        <li>税込合計（顧客支払） = 19,000 + 1,900 + 4,000 + 400 = 25,300</li>
        <li>サイト手数料は税込 1,500 として計上（差し引き）</li>
        <li>入金予定金額 = 25,300 - 1,500 = 23,800</li>
    </ul>

    <p class="muted">※消費税は端数切捨で計算しています。端数処理は要件に合わせて調整してください。</p>
</div>

<div class="section">
    <h2>免税業者の計算例（消費税なし）</h2>
    <p>免税業者の場合は消費税は発生しません（CSV では税額列は空または 0）。同じ条件の例：</p>
    <ul>
        <li>割引後単価（最安） = 9,000（1 個目）</li>
        <li>2 個目 = 10,000</li>
        <li>商品合計 = 9,000 + 10,000 = 19,000</li>
        <li>配送料合計 = 2,000 × 2 = 4,000</li>
        <li>税込合計（＝税抜合計）= 19,000 + 4,000 = 23,000</li>
        <li>入金予定金額 = 23,000 - サイト手数料（税込）</li>
    </ul>
</div>

<div class="section">
    <h2>CSV 列と説明（簡潔なマッピング）</h2>
    <table>
        <thead><tr><th>CSV 列</th><th>説明</th></tr></thead>
        <tbody>
            <tr><td>購入日</td><td>カード決済（注文作成）日</td></tr>
            <tr><td>取引日(到着確認日)</td><td>顧客が商品到着を確認した日（ない場合は空欄）</td></tr>
            <tr><td>入金日(手数料以外)</td><td>当サイトから出品者へ振込が行われた日（振込日）</td></tr>
            <tr><td>商品名 / 数量 / 単価</td><td>各商品情報（unit_price は税抜）</td></tr>
            <tr><td>割引後単価</td><td>クーポン・キャンペーン等を比較して最も低い金額（1 個目に適用）</td></tr>
            <tr><td>消費税額(1個目)</td><td>割引後単価に対する消費税（課税業者のみ）</td></tr>
            <tr><td>配送料(税抜)</td><td>配送料単価 × 個数（課税業者は別途配送料消費税を計算）</td></tr>
            <tr><td>サイト運営者手数料</td><td>税込み金額として表記。入金予定金額から差し引かれます。</td></tr>
            <tr><td>入金予定金額</td><td>顧客支払総額（割引・送料・消費税込） − サイト運営者手数料（税込）</td></tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h2>注意点（運用面）</h2>
    <ul>
        <li>HTML はダミーデータを用いた説明用です。実際に出力する CSV は該当店舗のデータのみを含めるよう認可・認証チェックを行ってください。</li>
        <li>クーポンコードが複数存在する場合、コードの優先順位や有効期限、対象商品が複雑になり得ます。CSV 生成ロジックでは <strong>すべてのコードを参照し、対象商品に合致するものは必ず比較対象とします</strong>（最小値を採用）。</li>
        <li>端数処理（消費税の切捨て/切上げ/四捨五入）は会計ルールに合わせて統一してください（ここでは floor を例にしています）。</li>
    </ul>
</div>

<p class="muted">このガイドは CSV 生成ロジックの説明用ダミーです。実際の帳票を出力する際は、個人情報保護やアクセス制御にご注意ください。</p>

</body>
</html>
