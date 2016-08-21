<?php
// CLOUDNESS
define('CLOUDNESS_NOT_AVAILABLE', 0); // не определено, по каким-либо причинам
define('CLOUDNESS_CLOUDY', 		7);		// облачно
define('CLOUDNESS_PARTLY_CLOUDY', 8);// малооблачно, переменная облачность
define('CLOUDNESS_CLEAR', 		10);		// ясно

// PHENOMENA
define('PHENOMENA_NOT_AVAILABLE', 0); // не определено, по каким-либо причинам
define('PHENOMENA_STORM',	1);  // гроза
define('PHENOMENA_HAIL',	2);  // град
define('PHENOMENA_SNOW',	3);  // снег
define('PHENOMENA_FOG',		4);  // туман
define('PHENOMENA_RAIN',	5);  // дождь
define('PHENOMENA_SMALL_RAIN', 6);  // небольшой дождь
define('PHENOMENA_WIND',	9);  // ветер

class HistoryParser{
	private $month;
	public $a_calendar = array();
	
	function __construct($month){
		$this->month = $month;
	}

	function doParse($html){
		$this->parseCalendar($html);
	}
	
	function parseCalendar($html){
		preg_match_all("/(<table.*?table>)/is", $html, $patt);

		$table = @$patt[1][0];
		preg_match_all("/(<tr.*?tr>)/is", $table, $patt);
		
		foreach($patt[1] as $i => $tr){
			$tr = iconv('UTF-8', 'KOI8-R', $tr);
			if($i<=1) {
				continue;
			}
			$tr_parsed = $this->parseRow($tr);
			
			$this->a_calendar[$tr_parsed['day'].".".$this->month] 
				= $this->makeShortResRow($tr_parsed);		
		}
	}
	
	function makeShortResRow($tr_parsed){
		list($max, $min) = $this->getMaxMin($tr_parsed['weather_day']['t'], $tr_parsed['weather_evening']['t']);
		
		$a_res['max'] = $max;
		$a_res['min'] = $min;

		$a_res['w'] = $this->getW(	$tr_parsed['weather_day']['cloudiness']['code'],
									$tr_parsed['weather_day']['phenomena']['code'],
									$tr_parsed['weather_evening']['cloudiness']['code'],
									$tr_parsed['weather_evening']['phenomena']['code']
									);
		
		$status = 1;
		if(strlen($max) <= 0 || $a_res['w']<=0) $status = 0;
		
		$a_res['row_status'] = $status;
		
		return $a_res;
	}
	
	function getW($day_cloud_code, $day_phen_code, $even_cloud_code, $even_phen_code){
		if($day_phen_code) 		return $day_phen_code;
		if($day_cloud_code) 	return $day_cloud_code;
		if($even_phen_code) 	return $even_phen_code;
		return $even_cloud_code;
	}
	
	function getMaxMin($t_day, $t_evening){		
		$max = ''; 
		$min = '';
		if(strlen($t_day) <= 0  && strlen($t_evening) <= 0){
			return array('', '');
		}
		if(strlen($t_day) <= 0  && strlen($t_evening) > 0){
			return array($t_evening, $t_evening);
		}
		if(strlen($t_day) > 0  && strlen($t_evening) <= 0){
			return array($t_day, $t_day);
		}
		
		if($t_day >= $t_evening){
			$max = $t_day;
			$min = $t_evening;
		}else{
			$max = $t_evening;
			$min = $t_day;
		}
		return array($max, $min);
	}

	function clearT($may_be_temp){
		if(preg_match("/img/", $may_be_temp, $patt)){
			return "";
		}
		return $may_be_temp;
	}
	
	function parseRow($tr){
		preg_match_all("/<td[^>]*>(.*?)<\/td>/is", $tr, $patt);
		$row = $patt[1];

		$cloudiness_day 	= $this->getCloudnessByTd($row[3]);
		$cloudiness_evening = $this->getCloudnessByTd($row[8]);
		
		$phenomena_day 	= $this->getPhenomenaByTd($row[4]);
		$phenomena_evening = $this->getPhenomenaByTd($row[9]);		

		$row[1] = $this->clearT($row[1]);
		$row[6] = $this->clearT($row[6]);
		
		$a_res = array(
			'day'   => sprintf("%02d", $row[0]),
			'weather_day'		=> array(
						't' 		=> $row[1], 
						'cloudiness' => array(
									'code' => $cloudiness_day['code'],
									'title'=> $cloudiness_day['title'],
								),
						'phenomena' => array(
									'code' => $phenomena_day['code'],
									'title'=> $phenomena_day['title'],
								),
						),
			'weather_evening'	=> array(
						't' 		=> $row[6], 
						'cloudiness' => array(
									'code' => $cloudiness_evening['code'],
									'title'=> $cloudiness_evening['title'],
								),
						'phenomena' => array(
									'code' => $phenomena_evening['code'],
									'title'=> $phenomena_evening['title'],
								),
						),						
		);
		return $a_res;
	}
	
	function getCloudnessByTd($td){
		$img = "empty";
		if(preg_match("/img\/([\w]+)\.png/", $td, $patt)){
			$img = $patt[1];
		}
		switch($img){
			case 'sun':		$code = CLOUDNESS_CLEAR; // get ясно -> ясно
							$title= "clear";
					break; 
			case 'sunc':	$code = CLOUDNESS_PARTLY_CLOUDY;	// get малооблачно -> переменная облачность
							$title= "partly_cloudy";
					break; 
			case 'suncl':	$code = CLOUDNESS_CLOUDY;	// get облачно -> облачно
							$title= "cloudy";
					break; 
			case 'dull':	$code = CLOUDNESS_CLOUDY;	// get пасмурно -> облачно
							$title= "mainly_cloudy";
					break; 
			default:		$code = CLOUDNESS_NOT_AVAILABLE;	
							$title= "not_available";
					break;
		}
		return array( 'code' => $code, 'title' => $title );
	}
	
	function getPhenomenaByTd($td){
		$img = "empty";
		if(preg_match("/img\/([\w]+)\.png/", $td, $patt)){
			$img = $patt[1];
		}		
		switch($img){
			case 'rain':	$code = PHENOMENA_RAIN; //
							$title= "rain";
					break; 
			case 'snow':	$code = PHENOMENA_SNOW;	//
							$title= "snow";
					break; 
			case 'storm':	$code = PHENOMENA_STORM;	//
							$title= "storm";
					break;
			default:		$code = PHENOMENA_NOT_AVAILABLE;	
							$title= "not_available";
					break;
		}
		return array( 'code' => $code, 'title' => $title );
		
	}
}
?>