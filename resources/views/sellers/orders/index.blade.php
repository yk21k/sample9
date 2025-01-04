@extends('layouts.seller')


@section('content')
    <div class="bg-secondary text-white">
        <h4 class="bg-primary text-white">Orders</h4>

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
@endsection
