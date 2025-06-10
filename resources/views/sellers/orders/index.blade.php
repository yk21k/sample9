@extends('layouts.seller')


@section('content')
    <div class="bg-secondary text-white">
        <h4 class="bg-primary text-white"> Orders</h4>

        <!-- 🔍 検索ボックス -->
        <div class="form-inline mb-2">
            
            <input type="text" name="search" id="orderSearch" class="form-control mb-3" placeholder="名前や電話番号で検索" value="{{ request('search') }}">



        </div>
        <!-- ここまで🔍 検索ボックス -->

        <a href="{{ route('seller.orders.export.full') }}" class="btn btn-warning mb-3">注文一覧＋アイテムをExcel出力</a>


        <table class="table table-striped" id="orderTable">
            <thead>
                <tr class="table-secondary">
                    <th><a href="#" class="sortable" data-sort="order_number">Order number</a></th>
                    <th><a href="#" class="sortable" data-sort="id">Order ID</a></th>
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
      // 確認ボタンが押されたときの処理
      document.getElementById('confirmBtn').addEventListener('click', function() {
        // 選択されたテンプレートを取得
        var selectedTemplate = document.getElementById('templateSelect').value;
        
        $('#templateValue').val(selectedTemplate);

        // メール本文のテンプレート
        var emailContent = "";
        

        // テンプレート1（挨拶とクーポン）の内容
        if (selectedTemplate === "template1") {
          emailContent = `
            <strong>こんにちは!</strong><br>
            この度はご利用いただきありがとうございます。<br><br>
            ご愛顧の感謝の気持ちを込めて、次回のお買い物で使えるクーポンをプレゼントします！<br><br>
            クーポンコード: <select name="coupon_id">@foreach($coupons as $coupon)<option value="{{ $coupon['id'] }}">{{ $coupon['name'] }} 利用期限:{{ $coupon['expiry_date'] }}</option>@endforeach</select>
            
            <br>
            ※ご利用期限は までです。<br><br>
            今後ともよろしくお願いいたします。<br>
            <em>（会社名）</em>            
          `;
            
        }
        // テンプレート2（キャンペーン開催）の内容
        else if (selectedTemplate === "template2") {
          emailContent = `
            <strong>お知らせ</strong><br>
            現在、（会社名）では大規模なキャンペーンを<select name="campaign_id" >@foreach($campaigns as $campaign)<option value="{{ $campaign['id'] }}">@if($campaign){{ $campaign['name'] }}@endif</option>@endforeach</select>
            開催中です！<br><br>
            【キャンペーン内容】<br>
            - すべての製品が10%オフ<br>
            - 特定の製品に限り、最大50%オフ<br><br>
            期間: 2025年2月10日〜2025年3月15日<br>
            詳細については、当社のウェブサイトをご覧ください。<br><br>
            ぜひご参加ください！<br>
            <em>（会社名）</em>
          `;
        }
        // テンプレート3（商品レビュー依頼）の内容
        else if (selectedTemplate === "template3") {
          emailContent = `
            <strong>こんにちは!</strong><br>
            この度は（商品名）をご購入いただきありがとうございます！<br><br>
            商品をご使用いただいた後、ぜひレビューを投稿してください。お客様の貴重な意見が、他のお客様の参考になります。<br><br>
            レビューのご投稿はこちらのリンクからお願いします：<br>
            <a href="レビュー投稿ページのURL" target="_blank">レビュー投稿ページ</a><br><br>
            ご協力いただきありがとうございます！<br>
            <em>（会社名）</em>
          `;
        }

        // 確認ページに内容を設定
        document.getElementById('templateModal').classList.remove('show');
        document.getElementById('confirmationPage').classList.remove('d-none');
        document.getElementById('selectedTemplate').innerHTML = emailContent;
        
      });

      // 送信ボタンが押されたときの処理
      document.getElementById('sendBtn').addEventListener('click', function() {
        // 送信処理（ここでは表示を変えるだけ）
        alert("メールが送信されました！");
        // 確認ページを非表示にし、モーダルを閉じる
        document.getElementById('confirmationPage').classList.add('d-none');
        document.getElementById('templateModal').classList.remove('show');
      });
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
