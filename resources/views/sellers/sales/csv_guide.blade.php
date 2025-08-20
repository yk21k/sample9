<!-- resources/views/sales/csv_guide.blade.php -->

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>CSV 出力ガイド</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        table { border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px 10px; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>CSV 出力ファイルの解説</h1>
    <p>以下は CSV 出力ファイルの各列の説明です。</p>
    $headers = [
                '購入日', '取引日(到着確認日)', '入金日(手数料以外)', '注文番号', '出品者名称', '出品者登録番号',
                '商品名', '数量', '単価(税抜)', '税率(%)', '消費税額(商品)', '税込金額', 
                '単価(税抜)割引対象', '税率(%)', '消費税額', '税込金額', '配送料(税抜)', 
                '税率(%)', '消費税額(配送料)', '税込金額', '税込金額合計', '消費税合計', '税区分'
            ];

    <table>
        キャンペーンやクーポンは、カート内の一つ目の商品にしか適用されません。また、一度しか使えません。
        <tr><th>列名</th><th>内容</th></tr>
        <tr><td>購入日</td><td>お客様が注文をカード決済した日</td></tr>
        <tr><td>取引日(到着確認日)</td><td>商品到着確認が行われた日</td></tr>
        <tr><td>入金日(手数料以外)</td><td>出品者に当サイトへの定数量以外が振込された日</td></tr>
        <tr><td>単価(税抜)</td><td>商品税抜価格</td></tr>
        <tr><td>単価(税抜)割引対象</td><td>キャンペーンやクーポン適用後の価格</td></tr>
        <tr><td>配送料(税抜)</td><td>商品ごとの配送料</td></tr>
        <tr><td>税率(%)</td><td>税率(現状10%)</td></tr>
        <tr><td>消費税額</td><td>税抜金額 × 税率</td></tr>
        <tr><td>税込金額</td><td>税抜金額 + 消費税</td></tr>
        <tr><td>税区分</td><td>課税/非課税</td></tr>
    </table>
</body>
</html>
