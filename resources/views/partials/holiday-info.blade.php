{{-- ===== Holiday Information ===== --}}
@if($holidays->isNotEmpty() || $extra_holidays->isNotEmpty())
<div class="holiday-bar">

    <div class="holiday-track">
        @foreach($holidays as $holiday)
            <span class="holiday-item holiday-normal">
                Holiday Store: {{ $holiday['shop_name'] }}
            </span>
        @endforeach

        @foreach($extra_holidays as $ex_holiday)
            @if($ex_holiday['date_flag'] === 2)
                <span class="holiday-item holiday-close">
                    Stores Temporarily Closed :: {{ $ex_holiday['shop_name'] }}
                </span>
            @endif

            @if($ex_holiday['date_flag'] === 1)
                <span class="holiday-item holiday-temp">
                    📣 Temporary Store :: {{ $ex_holiday['shop_name'] }} 📣
                </span>
            @endif
        @endforeach
    </div>

</div>
@endif