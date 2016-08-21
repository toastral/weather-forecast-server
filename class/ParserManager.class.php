<?php
class ParserManager{
	const CALENDAR_PAGE_PARSE_ERROR	= 3000;
	const HISTORY_PAGE_PARSE_ERROR = 3001;
	
	function ripUpSearchPage($html){
		return array(
				'status' 	=> 'found',
				'city_id' 	=> 2341
			);
	}
	
	function ripUpCalendarPage($html){
		$CalendarParser = new CalendarParser();
		$res = $CalendarParser->parsePage($html);
		
		if($res['parse_result'] != 'ok')
			throw new Exception("Parser is not found data in calendar page:".$res['parse_result'], self::CALENDAR_PAGE_PARSE_ERROR);

		return array(
				'status' 	=> 'ok',
				'weather' => $res['weather'],
				'ext_info' => array('parse_result' => $res['parse_result'])
			);
	}

	function ripUpHistoryPage($html, $m){
		$HistoryParser = new HistoryParser($m);
		$HistoryParser->doParse($html);
		return array(
				'status' 	=> 'ok',
				'weather' => $HistoryParser->a_calendar,
				'ext_info' => array(),
		);
	}
	
	function fetchСityId($html){
		if(preg_match('/Населённые пункты<\/div.*?<a href="\/city\/daily\/([\d]+)/is', $html, $patt)){
			return intval($patt[1]);
		}
		if(preg_match('/var current_city_id = ([\d]+);/', $html, $patt)){
			return intval($patt[1]);
		}
		return 0;
	}
	
	function isCityNotFound($html){
		if(preg_match('/ничего не найдено/is', $html, $patt)){
			return true;
		}
		return false;
	}

	function fetchGisLabel($html){
		if(preg_match('/input type="text" name="(gis[\d]+)"/is', $html, $patt)){
			return $patt[1];
		}
		return '';
	}
}
?>