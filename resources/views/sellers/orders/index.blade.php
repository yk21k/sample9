@extends('layouts.seller')


@section('content')
    <div class="bg-secondary text-white">
        <h4 class="bg-primary text-white"> Orders</h4>

        <!-- 🔍 検索ボックス -->
        <div class="form-inline mb-2">
            
            <input type="text" name="search" id="orderSearch" class="form-control mb-3" placeholder="名前や電話番号、郵便番号、statusで検索" value="{{ request('search') }}">



        </div>
        <!-- ここまで🔍 検索ボックス -->

        <a href="{{ route('seller.orders.export.full') }}" class="btn btn-warning mb-3">注文一覧＋アイテムをExcel出力</a>


        <table class="table table-striped" id="orderTable">
            <thead>
                <tr class="table-secondary">
                    <th><a href="#" class="sortable" data-sort="order_number">Order number</a></th>
                    <th><a href="#" class="sortable" data-sort="order_id">Order ID</a></th>
                    <th><a href="#" class="sortable" data-sort="status">Status</a></th>
                    <th>Item count</th>
                    <th><a href="#" class="sortable" data-sort="shipping_name">Shipping Name</a></th>
                    <th>Shipping Phone</th>
                    <th>Shipping Zipcode</th>
                    <th>Shipping Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                @include('sellers.orders.suborders.order_partials')
            </tbody>   
        </table>


        
    </div>
    @php
        use Carbon\Carbon;

        $today = Carbon::today();

        // 有効なクーポンだけフィルタ
        $validCoupons = collect($coupons)->filter(function ($coupon) use ($today) {
            return \Carbon\Carbon::parse($coupon['expiry_date'])->gte($today);
        })->values(); // → インデックスを振り直し
    @endphp


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <script>
        $(function() {
            $("#hide_bill_button").click(function() {
                $(".form_bill").slideToggle();
            });
        });
    </script>

    <script>
        const coupons = @json($validCoupons);
    </script>

    <script>
        const campaigns = @json($campaigns);
    </script>

    <script>
        const template4Wrapper = document.getElementById('template4Wrapper');


        const template4SelectHTML = `
            <label for="template4Purpose">送信目的を選択してください</label>
            <select id="template4Purpose" class="form-control">
                <option value="">-- 送信目的を選択してください --</option>
                <option value="arrival_check">1. 商品が到着したか確認</option>
                <option value="mypage_reminder">2. マイページの確認を促す</option>
                <option value="store_visit">3. 実店舗への来店を依頼</option>
                <option value="want_receipt">4. レシート（領収書）の送信</option>
            </select>
        `;


        // テンプレート選択変更時に制御
        document.getElementById('templateSelect').addEventListener('change', function () {
            const selectedTemplate = this.value;

            if (selectedTemplate === "template4") {
                template4Wrapper.innerHTML = template4SelectHTML;
                template4Wrapper.classList.remove('d-none');

                // select が生成された後にイベントを登録
                 
                // イベントリスナーを再登録（selectが再生成されるので毎回必要）
                const template4Select =　document.getElementById('template4Purpose');
                template4Select.addEventListener('change', function () {
                    // 選択値を hidden input にセット
                    document.getElementById('purposeInput').value = this.value;
                    updateEmailPreview();
                });

                // 初期値が空の場合は hidden も空に
                document.getElementById('purposeInput').value = template4Select.value;
                
            } else {
                template4Wrapper.innerHTML = '';
                template4Wrapper.classList.add('d-none');
                document.getElementById('purposeInput').value = ''; // 他テンプレートの場合は空
            }
            updateEmailPreview();
            
        });


        // 確認ボタン押下時の処理（従来のままでもOK）
        document.getElementById('confirmBtn').addEventListener('click', function () {
            $('#templateValue').val(document.getElementById('templateSelect').value);

            // 確認ページに表示
            document.getElementById('templateModal').classList.remove('show');
            document.getElementById('confirmationPage').classList.remove('d-none');
        });

        let currentOrderNumber = "";
        let currentOrderId = "";
        let currentUserId = "";

        // モーダルを開いたときに、order number と user_id をセット
        document.querySelectorAll('.open-template-modal').forEach(button => {
            button.addEventListener('click', function () {
                console.log("Order ID:", currentOrderId);

                currentOrderNumber = this.dataset.orderNumber;
                currentOrderId = this.dataset.orderId;
                currentUserId = this.dataset.userId;

                const orderNumber = this.dataset.orderNumber;
                const orderId = this.dataset.orderId;
                const userId = this.dataset.userId;

                const confirmForm = document.querySelector('#confirmationPage').closest('form');

                // hidden input に上書き
                document.querySelector('input[name="order_number"]').value = orderNumber;
                document.querySelector('input[name="order_id"]').value = orderId;
                document.querySelector('input[name="user_id"]').value = userId;

                updateEmailPreview();

                console.log("Order Number:", currentOrderNumber);
                console.log("User ID:", currentUserId);
            });
        });


        // メール本文の更新処理（関数化）
        function updateEmailPreview() {
            const selectedTemplate = document.getElementById('templateSelect').value;
            let emailContent = "";

            const sendBtn = document.getElementById("sendBtn");
            let isSendable = false; // ← 各テンプレートごとの「送信可否」をここで判断

            if (selectedTemplate === "template1") {
                let optionsHtml = '';

                if (coupons.length > 0) {
                    // ここで最初の選択肢を1回だけ追加
                    optionsHtml += `<option value="">クーポンをお選びください</option>`;

                    coupons.forEach(coupon => {
                        optionsHtml += `<option value="${coupon.id}">${coupon.code} 利用期限: ${coupon.expiry_date}</option>`;
                        isSendable = true; // クーポンがあるときだけ送信可能
                    });
                } else {
                    optionsHtml = '<option disabled>本日時点で有効なクーポンはありません</option>';
                }

                emailContent = `
                    <strong>こんにちは!</strong><br>
                    この度はご利用いただきありがとうございます。<br><br>
                    ご愛顧の感謝の気持ちを込めて、次回のお買い物で使えるクーポンをプレゼントします！<br><br>
                    こちらのクーポンは、オークション出品の商品に対しては、適用されません。
                    オークション出品の商品と同じ商品が当サイトの通常の出品物として出品されている場合は、適用されます。
                    ひとつの商品に一度だけ利用できます。同じ商品を２つ以上購入時もひとつの商品に一度だけ利用できます。
                    クーポンコード: 
                    <select name="coupon_id" id="couponSelect">
                        ${optionsHtml}
                    </select>
                    <br><br>
                    今後ともよろしくお願いいたします。<br>
                    <em>{{ $mail_part->name ?? 'ショップ名未設定' }}</em>
                `;

                // テンプレート描画後、イベント登録する（ここが重要！）
                setTimeout(() => {
                    const select = document.getElementById('couponSelect');
                    if (select) {
                        select.addEventListener('change', function (e) {
                            const selectedCouponId = e.target.value;
                            document.getElementById('orderCouponInput').value = selectedCouponId;
                            console.log("Coupon ID:", selectedCouponId);
                        });
                    }
                }, 0);

            } else if (selectedTemplate === "template2") {
                let optionsHtml = '';
                let campaignPeriod = '現在開催中のキャンペーンはありません。';

                const today = new Date(); // 今日の日付（時刻含む）
                const validCampaigns = campaigns.filter(campaign => {
                    const endDate = new Date(campaign.end_date); // 例: '2025-07-25'
                    return endDate >= today;
                });

                const formatDateToMonthDay = (isoDateStr) => {
                    const date = new Date(isoDateStr);
                    const month = date.getMonth() + 1; // 0〜11なので +1
                    const day = date.getDate();
                    return `${month}月${day}日`;
                };

                if (validCampaigns.length > 0) {
                    validCampaigns.forEach(campaign => {
                        optionsHtml += `<option value="${campaign.id}">${campaign.name}</option>`;
                    });

                    const first = validCampaigns[0];
                    campaignPeriod = `期間: ${formatDateToMonthDay(first.start_date)}〜${formatDateToMonthDay(first.end_date)}<br>`;

                    isSendable = true; // 有効なキャンペーンがあるときだけ送信可能
                } else {
                    optionsHtml = '<option disabled>有効なキャンペーンはありません</option>';
                }

                emailContent = `
                    <strong>お知らせ</strong><br>
                    現在、{{ $mail_part->name ?? 'ショップ名未設定' }}ではキャンペーンを
                    <select name="campaign_id">
                        ${optionsHtml}
                    </select>
                    開催中です！<br><br>
                    【キャンペーン内容】<br>
                    以下には開催中のすべてのキャンペーンの内容が送付されます。<br>
                    キャンペーン名<br>
                    キャンペンの説明<br>
                    <br>

                    - {{ $mail_part->name ?? 'ショップ名未設定' }}のオークション出品以外の商品がオフ、オークション出品と同じ商品でも通常商品ならオフ<br><br>
                    期間:⚪︎月⚪︎日〜⚪︎月⚪︎日
                    詳細については、ウェブサイトをご覧ください。<br><br>
                    ぜひご確認ください！<br>
                    <em>{{ $mail_part->name ?? 'ショップ名未設定' }}</em>
                `;
            } else if (selectedTemplate === "template3") {
                // テンプレート3は常に送信可能となってしまっている
                isSendable = true;

                emailContent = `
                    <strong>こんにちは!</strong><br>
                    この度はオーダーNO：<strong>${currentOrderId}</strong>の内容でご購入いただきありがとうございます！<br><br>
                    商品をご使用いただいた後、ぜひレビューを投稿してください。お客様の貴重な意見が、他のお客様の参考になります。
                    当サイトでは、評価に対してクーポンや金銭などの対価は一切提供しておりません。
                    公平で信頼性のある評価を維持するため、
                    報酬・特典などの提供を受けた上でのレビューやスコア投稿はご遠慮くださいますようお願い申し上げます。
                    <br><br>
                    レビューのご投稿はこちらのリンクからお願いします：<br>
                    ※この評価は、他のお客様にも参考にしていただく目的でランキング等に使用されます。 
                    
                    <a href="レビュー投稿ページのURL" target="_blank">レビュー投稿ページ</a><br><br>
                    ご協力よろしくお願いします！<br>
                    <em>{{ $mail_part->name ?? 'ショップ名未設定' }}</em>
                `;
            } else if (selectedTemplate === "template4") {
                // すでにセレクトが存在しているかをチェック
                let template4PurposeSelect = document.getElementById('template4Purpose');
                
                if (!template4PurposeSelect) {
                    // なければ挿入してイベント登録
                    template4Wrapper.innerHTML = template4SelectHTML;
                    template4Wrapper.classList.remove('d-none');

                    // 新たにセレクトを取得してイベントを登録
                    template4PurposeSelect = document.getElementById('template4Purpose');
                    template4PurposeSelect.addEventListener('change', updateEmailPreview);
                }

                const selectedPurpose = template4PurposeSelect.value;

                if (!selectedPurpose) {
                    isSendable = false;
                    emailContent = `<em>送信目的を選択してください。</em>`;
                } else if (selectedPurpose === "arrival_check") {
                    isSendable = true;
                    emailContent = `
                        <strong>こんにちは！</strong><br>
                        オーダー番号：<strong>${currentOrderId}</strong> の商品は無事に到着されましたでしょうか？<br>
                        ご確認の上、何かございましたらご連絡くださいませ。<br><br>
                        <em>{{ $mail_part->name ?? 'ショップ名未設定' }}</em>
                    `;
                } else if (selectedPurpose === "mypage_reminder") {
                    isSendable = true;
                    emailContent = `
                        <strong>こんにちは！</strong><br>
                        オーダー番号：<strong>${currentOrderId}</strong> に関する詳細はマイページからご確認いただけます。<br>
                        <a href="" target="_blank">▶ マイページを開く</a><br><br>
                        ご確認よろしくお願いいたします。<br>
                        <em>{{ $mail_part->name ?? 'ショップ名未設定' }}</em>
                    `;
                } else if (selectedPurpose === "store_visit") {
                    isSendable = true;
                    emailContent = `
                        <strong>こんにちは！</strong><br>
                        オーダー番号：<strong>${currentOrderId}</strong> に関して、<br>
                        実店舗でのご相談・受け取りをご希望の場合はお気軽にご来店くださいませ。<br>
                        ご来店前にご連絡いただけますと幸いです。<br><br>
                        <em>{{ $mail_part->name ?? 'ショップ名未設定' }}</em>
                    `;
                } else if (selectedPurpose === "want_receipt") {
                    isSendable = true;
                    emailContent = `
                        <strong>ご購入いただきましてありがとうございます</strong><br>
                        オーダーID：<strong>${currentOrderId}</strong> に関して、<br>
                        レシート兼領収書をお送りいたします<br>
                        <br><br>
                        <em>{{ $mail_part->name ?? 'ショップ名未設定' }}</em>
                    `;
                }
            }


            // ✅ ボタンの有効／無効化
            sendBtn.disabled = !isSendable;
            document.getElementById('selectedTemplate').innerHTML = emailContent;
        }

        // 初期選択状態でのプレビュー表示（必要に応じて）
        window.addEventListener('DOMContentLoaded', updateEmailPreview);
    </script>

    <script>
        let sortField = 'id';
        let sortDirection = 'desc';
        let currentPage = 1;

        function fetchOrders() {
            $.ajax({
                url: "{{ route('seller.orders.index') }}",
                type: 'GET',
                data: {
                    search: $('#orderSearch').val(),
                    sort: sortField,
                    direction: sortDirection,
                    page: currentPage
                },
                success: function (html) {
                    // デバッグ用ログ（必要に応じて）
                    console.log("✅ 成功:", html);

                    // tbody 部分の差し替え
                    $('#orders-table-body').html($(html).find('tbody').html());
                    $('#pagination-links').html($(html).find('#pagination-links').html());
                },
                error: function (xhr, status, error) {
                    console.error("❌ エラーが発生しました:");
                    console.error("ステータスコード:", xhr.status);
                    console.error("ステータス:", status);
                    console.error("エラー内容:", error);
                    console.error("レスポンス本文:", xhr.responseText);
                    alert('データの取得中にエラーが発生しました。開発者ツールを確認してください。');
                }
            });
        }


        $(document).ready(function () {
            // 🔍 入力検索
            $('#orderSearch').on('input', function () {
                currentPage = 1;
                fetchOrders();
            });

            // 🔃 ソートクリック
            $(document).on('click', '.sortable', function (e) {
                e.preventDefault();
                const clickedField = $(this).data('sort');

                if (sortField === clickedField) {
                    sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
                } else {
                    sortField = clickedField;
                    sortDirection = 'asc';
                }

                fetchOrders();
            });

            // ⏭️ ページネーション
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                currentPage = $(this).attr('href').split('page=')[1];
                fetchOrders();
            });
        });
    </script>
    
@endsection
