@extends('layouts.seller')


@section('content')
<h3>Order Summary</h3>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Qty</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
        <tr>
            @if($item->shop_id == $shopMane)
            <td scope="row">
                {{$item->name}}
            </td>
            <td>
                {{$item->pivot->quantity}}
            </td>
            <td>
                {{$item->pivot->price*$item->pivot->quantity}}
            </td>
            @endif
        </tr>
        @endforeach


    </tbody>
</table>

@endsection