@extends('layouts.seller')


@section('content')
    <div class="bg-secondary text-white">
        <h4 class="bg-primary text-white"> Orders</h4>


        <table class="table table-striped">
            <thead>
                <tr class="table-secondary">
                    <th>Order number</th>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Item count</th>
                    <th>Shipping Name</th>
                    <th>Shipping Phone</th>
                    <th>Shipping Zipcode</th>
                    <th>Shipping Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $subOrder)
                    <tr class="table-secondary">
                        <td scope="row">

                            {{$subOrder->order->order_number}}
                        </td>
                        <td>
                            {{$subOrder->order->id}}  
                        </td>
                        <td>
                            {{$subOrder->status}}<br>

                            @if($subOrder->status=="pending" && $subOrder->payment_status=="")
                                <a href=" {{route('seller.order.delivered_accepted', $subOrder)}} " class="btn btn-info btn-sm" style="margin: 2px;">Mark as Accepted</button><br>
                            @endif


                            @if($subOrder->status == 'pending' && !empty($subOrder->shipping_company) && !empty($subOrder->invoice_number))

                                <a href=" {{route('seller.order.delivered_arranged', $subOrder)}} " class="btn btn-success btn-sm" style="margin: 2px;">Mark as Delivery Arranged</button><br>
                            @elseif(($subOrder->status == 'pending') && $subOrder->payment_status=="accepted")
                                <a href=" {{route('seller.order.delivered_arranged', $subOrder)}} " class="btn btn-success btn-sm disabled" style="margin: 2px;">Mark as Delivery Arranged</button><br>
                                <a class="btn btn-warning btn-sm" id="hide_bill_button" style="margin: 2px;">Air Waybill</button></a><br> 
                                <div class="form_bill">
                                <form action=" {{route('seller.order.delivered_company', $subOrder)}} " method="get">@csrf
                                    <label for="shipping_company">Shipping Company</label>
                                    <input type="text" class="form-control" name="shipping_company" id="">
                                    <label for="invoice_number">Invoice Number</label>
                                    <input type="text" class="form-control" name="invoice_number" id="">
                                    <button type="submit" class="btn btn-primary mb-2 mr-2">Submit</button>

                                </form>    
                                </div>
                            @endif


                            @if($subOrder->status == 'processing')
                                <a href=" {{route('seller.order.delivered', $subOrder)}} " class="btn btn-primary btn-sm" style="margin: 2px;">Mark as delivered</button><br>
                                        
                            @endif
                            
                        </td>

                        <td>
                            {{$subOrder->item_count}}
                        </td>

                        <td>
                           {!! $subOrder->order->shipping_fullname !!}
                        </td>
                        <td>
                           {!! $subOrder->order->shipping_phone !!}
                        </td>
                        <td>
                           {!! $subOrder->order->shipping_zipcode !!}
                        </td>
                        <td>
                           {!! $subOrder->order->shipping_state !!}
                        
                           {!! $subOrder->order->shipping_city !!}
                          
                           {!! $subOrder->order->shipping_address !!}
                        </td>
                        
                        <td>

                            <!-- 1. メール送信ボタン（ファザード） -->
                            <button class="btn btn-primary" data-toggle="modal" data-target="#templateModal">Mail</button>

                            <!-- 2. テンプレート選択モーダル -->
                            <div class="modal" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="templateModalLabel">テンプレート選択</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <div class="modal-body">
                                    <!-- 2種類のテンプレート選択 -->
                                    <form id="templateForm">@csrf
                                      <div class="form-group">
                                        <label for="templateSelect">テンプレートを選択</label>
                                        <select class="form-control" id="templateSelect">

                                          @if(empty($coupons)) 
                                            <option value="template1" disabled>テンプレート1 (挨拶とクーポン)</option>
                                          @else
                                            <option value="template1">テンプレート1 (挨拶とクーポン)</option>
                                          @endif  
                                          @if(empty($campaigns)) 
                                            <option value="template2" disabled>テンプレート2 (キャンペーン開催)</option>
                                          @else
                                            <option value="template2" >テンプレート2 (キャンペーン開催)</option>
                                          @endif
                                          <option value="template3">テンプレート3 (商品レビュー依頼)</option>
                                        </select>
                                      </div>
                                      <button type="button" class="btn btn-info" id="confirmBtn">確認</button>
                                    </form>
                                    <!-- 3. 確認ページ -->
                                    <form action=" {{route('seller.order.shop_mail', $subOrder)}} " method="get">@csrf
                                        <div id="confirmationPage" class="d-none">
                                          <h3>確認ページ</h3>
                                          <p id="selectedTemplate"></p>
                                          <input type="hidden" name="user_id" value="{{ $subOrder->user_id }}">
                                          <input type="hidden" name="shop_id" value="{{ $subOrder->seller_id }}">
                                          <input type="hidden" name="template" id="templateValue">
                                          <button class="btn btn-success" id="sendBtn">送信</button>
                                        </div>
                                    </form>    
                                  </div>
                                </div>
                              </div>
                            </div>
                            <br>&nbsp;
                            <a name="" id="" class="btn btn-primary btn-sm" href="{{route('seller.orders.show', $subOrder)}}" role="button">View</a>
                        </td>
                    </tr>
                @empty

                @endforelse
                {{ $orders->links() }}

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
    
    
@endsection
