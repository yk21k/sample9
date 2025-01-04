@extends('layouts.seller')
@section('content')
<div class="container">
   <div class="row justify-content-center">
       <div class="col-md-8">
           <div class="card">
               <div class="card-header">定休日設定</div>
               <div class="card-body">
               	
               	@if (session('status'))
                       <div class="alert alert-success" role="alert">
                           {{ session('status') }}
                       </div>
                   @endif
                   
					<form method="post" action="{{ route('seller.update_holiday_setting') }}">
						@csrf

						<table class="table table-borderd">
							<input class="" type="hidden" id="shop_name" name="shop_name" value="{{ auth()->user()->shop->name  }}" readonly="">
							<input class="" type="hidden" id="shop_id" name="shop_id" value="{{ auth()->user()->shop->id  }}" readonly="">

							<tr>
								<th>月曜日</th>
								<td>
									<input type="radio" name="flag_mon" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenMonday()) ? 'checked' : '' }} id="flag_mon_open" />
									<label for="flag_mon_open">営業日</label>
									<input type="radio" name="flag_mon" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseMonday()) ? 'checked' : '' }} id="flag_mon_close" />
									<label for="flag_mon_close">休み</label>
								</td>
							</tr>
							<tr>
								<th>火曜日</th>
								<td>
									<input type="radio" name="flag_tue" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenTuesday()) ? 'checked' : '' }} id="flag_tue_open" />
									<label for="flag_tue_open">営業日</label>
									<input type="radio" name="flag_tue" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseTuesday()) ? 'checked' : '' }} id="flag_tue_close" />
									<label for="flag_tue_close">休み</label>
								</td>
							</tr>
							<tr>
								<th>水曜日</th>
								<td>
									<input type="radio" name="flag_wed" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenWednesday()) ? 'checked' : '' }} id="flag_wed_open" />
									<label for="flag_wed_open">営業日</label>
									<input type="radio" name="flag_wed" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseWednesday()) ? 'checked' : '' }} id="flag_wed_close" />
									<label for="flag_wed_close">休み</label>
								</td>
							</tr>
							<tr>
								<th>木曜日</th>
								<td>
									<input type="radio" name="flag_thu" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenThursday()) ? 'checked' : '' }} id="flag_thu_open" />
									<label for="flag_thu_open">営業日</label>
									<input type="radio" name="flag_thu" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseThursday()) ? 'checked' : '' }} id="flag_thu_close" />
									<label for="flag_thu_close">休み</label>
								</td>
							</tr>
							<tr>
								<th>金曜日</th>
								<td>
									<input type="radio" name="flag_fri" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenFriday()) ? 'checked' : '' }} id="flag_fri_open" />
									<label for="flag_fri_open">営業日</label>
									<input type="radio" name="flag_fri" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseFriday()) ? 'checked' : '' }} id="flag_fri_close" />
									<label for="flag_fri_close">休み</label>
								</td>
							</tr>
							<tr>
								<th>土曜日</th>
								<td>
									<input type="radio" name="flag_sat" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenSaturday()) ? 'checked' : '' }} id="flag_sat_open" />
									<label for="flag_sat_open">営業日</label>
									<input type="radio" name="flag_sat" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseSaturday()) ? 'checked' : '' }} id="flag_sat_close" />
									<label for="flag_sat_close">休み</label>
								</td>
							</tr>
							<tr>
								<th>日曜日</th>
								<td>
									<input type="radio" name="flag_sun" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenSunday()) ? 'checked' : '' }} id="flag_sun_open" />
									<label for="flag_sun_open">営業日</label>
									<input type="radio" name="flag_sun" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseSunday()) ? 'checked' : '' }} id="flag_sun_close" />
									<label for="flag_sun_close">休み</label>
								</td>
							</tr>
							<tr>
								<th>祝日</th>
								<td>
									<input type="radio" name="flag_holiday" value="{{ $FLAG_OPEN }}" {{ ($setting->isOpenHoliday()) ? 'checked' : '' }} id="flag_holiday_ope" />
									<label for="flag_holiday_open">営業日</label>
									<input type="radio" name="flag_holiday" value="{{ $FLAG_CLOSE }}" {{ ($setting->isCloseHoliday()) ? 'checked' : '' }} id="flag_holiday_clos" />
									<label for="flag_holiday_close">休み</label>
								</td>
							</tr>
						</table>
						<button type="submit" class="btn btn-primary">保存</button>
					</form>
               </div>
           </div>
       </div>
   </div>
</div>
@endsection