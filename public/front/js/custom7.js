document.addEventListener('DOMContentLoaded', function () {
    const bidButton = document.querySelector('.btn-bid-auction');
    const bidMessage = document.querySelector('#bid-message');
    const errorMessage = document.querySelector('#error-message');
    const currentPriceElement = document.querySelector('#current-price-auction'); // 現在の価格を表示している要素
    // const initialPrice = {{ $product->initial_price }}; // 初期価格（希望額）
    const initialPrice = '1000'; // 初期価格（希望額）
    let currentPrice = parseInt(currentPriceElement.textContent.replace('¥', '').replace(/,/g, ''), 10); // 現在の価格（初期価格）を取得
    window.alert(currentPrice);
    // 初期設定で、希望額と最初の入札額を比較
    if (currentPrice < initialPrice) {
        errorMessage.textContent = "現在価格よりも高い金額を入力してください。";
    } else {
        errorMessage.textContent = "";
    }

    if (bidButton) {
        bidButton.addEventListener('click', function () {
            // ユーザーから入札金額を入力してもらう（プロンプトなどで取得）
            const bidAmount = prompt('入札額を入力してください:');

            if (bidAmount && !isNaN(bidAmount)) {
                // 入札額が有効な場合、数値に変換
                const bid = parseInt(bidAmount.replace(/,/g, '').replace('¥', '').trim(), 10);
                // window.alert(currentPrice);
                // window.alert(bid);

                // 入札額が現在の価格より高い場合
                if (bid > currentPrice) {
                    // 入札メッセージを青色で表示
                    window.alert(${bid.toLocaleString()});
                    bidMessage.textContent = `¥${bid.toLocaleString()}で入札しました！`; // 入札額のみを表示
                    bidMessage.style.color = 'blue'; // 青色
                    bidMessage.style.fontSize = '20px'; // フォントサイズを大きくする
                    bidMessage.style.transition = 'all 0.5s ease'; // アニメーション効果

                    // アニメーションを加えて、メッセージがスライドイン
                    setTimeout(function () {
                        bidMessage.style.opacity = 0;
                        bidMessage.style.transition = 'opacity 1s ease';
                    }, 2000);

                    errorMessage.textContent = ''; // エラーメッセージをクリア
                } else {
                    // 入札額が現在価格より低い場合
                    bidMessage.textContent = ''; // 入札メッセージをクリア
                    errorMessage.textContent = "現在価格よりも高い金額を入力してください。";
                    errorMessage.style.color = 'red'; // 赤色でエラーメッセージ
                    errorMessage.style.fontSize = '18px'; // フォントサイズを設定
                }
            } else {
                // 無効な入力（NaNの場合）はエラーメッセージ
                errorMessage.textContent = "無効な金額です。再度入力してください。";
                errorMessage.style.color = 'red'; // 赤色でエラーメッセージ
                bidMessage.textContent = ''; // 入札メッセージをクリア
            }
        });
    }
});
