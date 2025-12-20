@extends('layouts.seller')
@section('content')
	<div class="container">
	   <div class="row justify-content-center">
	       <div class="col-md-12">
	           <div class="card">
	               <div class="card-header text-center">
		               <a class="btn btn-outline-secondary float-left" href="{{ url('seller/extra_holiday_setting/.?date=' . $calendar->getPreviousMonth()) }}">前の月</a>
		               <span>{{ $calendar->getTitle() }}の臨時営業日設定</span> 
		               <a class="btn btn-outline-secondary float-right" href="{{ url('seller/extra_holiday_setting/.?date=' . $calendar->getNextMonth()) }}">次の月</a>
	               </div>
	               <small>月が相違の場合でももう一度サイドバーのShop Calendarを押してもう一度ご確認下さい</small>
	               <div class="card-body">
						@if (session('status'))
	                       <div class="alert alert-success" role="alert">
	                           {{ session('status') }}
	                       </div>
	                   	@endif
						<form method="post" action="{{ route('seller.update_extra_holiday_setting') }}">
							@csrf
							<input class="" type="hidden" id="shop_name" name="shop_name" value="{{ auth()->user()->shop->name  }}" readonly="">
							<input class="" type="hidden" id="shop_id" name="shop_id" value="{{ auth()->user()->shop->id  }}" readonly="">
							
							<div class="card-body">
								{!! $calendar->render() !!}
								<div class="text-center">
									<button type="submit" class="btn btn-primary">保存</button>
								</div>
							</div>
							
						</form>
	               </div>
	           </div>
	       </div>
	   </div>
	</div>
@endsection