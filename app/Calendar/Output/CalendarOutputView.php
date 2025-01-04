<?php
namespace App\Calendar\Output;
use Carbon\Carbon;

use App\Calendar\CalendarView;
use App\Calendar\CalendarWeekDay;
/**
* 表示用
*/
class CalendarOutputView extends CalendarView {
	
	/**
	 * 日を描画する
	 */
	protected function renderDay(CalendarWeekDay $day){

		$html = [];
		$extraHoliday = null;

		//臨時営業日設定で上書き
		if(isset($this->holidays[$day->getDateKey()])){
			$extraHoliday = $this->holidays[$day->getDateKey()];
			
			if($extraHoliday->isOpen()){
				$day->setHoliday(false);
			}else if($extraHoliday->isClose()){
				$day->setHoliday(true);
			}
		}

		$html[] = '<td class="'.$day->getClassName().'">';
		$html[] = $day->render();

        //コメントを表示
		if($extraHoliday){
			$html[] = '<p class="comment">' . e($extraHoliday->comment) . '</p>';
		}

		$html[] = '</td>';

		return implode("", $html);
	}
}