<?php
namespace App\Calendar\Form;

use Carbon\Carbon;
use App\Calendar\CalendarWeek;
use App\Models\Calendar\HolidaySetting;

class CalendarWeekForm extends CalendarWeek {

	/**
	 * ExtraHoliday[]
	 */
	public $holidays = [];

	/**
	 * @return CalendarWeekDayForm
	 */
	function getDay(Carbon $date, HolidaySetting $setting){
		$day = new CalendarWeekDayForm($date);
		$day->checkHoliday($setting);
		if(isset($this->holidays[$day->getDateKey()])){
			$day->extraHoliday = $this->holidays[$day->getDateKey()];
		}
		return $day;
	}
}