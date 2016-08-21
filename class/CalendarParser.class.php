<?php
class CalendarParser{
	
	function parsePage($html){
		if(!preg_match_all('/(<table class="calendar".*?table>)/is', $html, $patt)){
			return array('parse_result' => 'not_found_tables', 'weather' => array() );
		}
		
		$table_cur_month = $patt[1][0];
		$table_next_month = $patt[1][1];

		$cur_month = date("m", time());
		$cur_year = date("Y", time());
		
		$next_month = $cur_month+1 > 12 ? 1 : $cur_month+1;
		$next_year = $cur_month+1 > 12 ? $cur_year+1 : $cur_year;
		
		$a_res_cur = $this->parseCalendar($table_cur_month, $cur_month, $cur_year);
		$a_res_next = $this->parseCalendar($table_next_month, $next_month, $next_year);
		
		$merge = array_merge($a_res_cur, $a_res_next);
		
		if(count($merge) <= 0) return array('parse_result' => 'empty_parsing_calendar', 'weather' => array() );
		
		return array('parse_result' => 'ok', 'weather' => $merge );
	}


	function parseCalendar($table_month, $month, $year){
		$a_res = array();
		preg_match_all('/(<td.*?td>)/is', $table_month, $patt);
		
		$month_year = sprintf("%02d.%04d", $month, $year);
		foreach($patt[1] as $td){
			if($this->isEmptyCell($td)) continue;
			$a_cell = $this->parseCell($td);
			$day = sprintf("%02d", $a_cell['day']);
			unset($a_cell['day']);
			unset($a_cell['title']);
			$a_res[$day.'.'.$month_year] = $a_cell;
		}
		return $a_res;
	}

	function isEmptyCell($td){
		if(preg_match('/<td class="([\w\s]*empty[\w\s]*)"/is', $td, $patt_class)){
				return true;
		}
		return false;
	}

	function parseCell($td){
		/**
		* meat
		*/
		preg_match('/="day">([\d]+)<\/span/is', $td, $patt_day);
		preg_match('/<td[^>]+title="(.*?)">/is', $td, $patt_title);
		preg_match('/div[^>]+max">\s+<dd[^>]+>([^<]+)<\/dd/is', $td, $patt_max);
		preg_match('/div[^>]+min">\s+<dd[^>]+>([^<]+)<\/dd/is', $td, $patt_min);
		return array(	'day' => $patt_day[1], 
						'title' => $patt_title[1],
						'w' => Utills::getWeatherCodeByTitle($patt_title[1]),
						'max' => $patt_max[1], 
						'min' => $patt_min[1]);
	}
}
?>