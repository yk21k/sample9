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
    <tr>
        <td colspan="9">データがありません</td>
    </tr>
@endforelse
<div id="pagination-links">
    {{ $orders->links() }}
</div>
