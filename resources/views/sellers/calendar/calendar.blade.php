@extends('layouts.seller')
@section('content')
<div class="container">
   <div class="row justify-content-center">
       <div class="col-md-8">
           <div class="card">
                <a class="btn btn-outline-secondary float-left" href="{{ url('seller/holiday_setting') }}">休日設定</a>

                <a class="btn btn-outline-secondary float-left" href="{{ url('seller/extra_holiday_setting') }}">臨時休日設定</a>
               <div class="card-header text-center">
                    <a class="btn btn-outline-secondary float-left" href="{{ url('seller/calendar/.?date=' . $calendar->getPreviousMonth()) }}">前の月</a>
                    
                    <span>{{ $calendar->getTitle() }}</span>

                    <a class="btn btn-outline-secondary float-right" href="{{ url('seller/calendar/.?date=' . $calendar->getNextMonth()) }}">次の月</a>
                </div>

               <div class="card-body">
                    {!! $calendar->render() !!}
               </div>

           </div>
       </div>
   </div>
</div>
@endsection