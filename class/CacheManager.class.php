<?php
class CacheManager{
	const CALENDAR_CACHE_TIMEOUT_MINS 	= 720; // 12 hours
	private $Path;
	function __construct(){
		$this->Path	=	new PathManager();
	}
	
	function isValidCalendarCache($timestamp){
		$secs = (time()-$timestamp);
		$mins = $secs/60;
		if($mins<self::CALENDAR_CACHE_TIMEOUT_MINS) return true;
		return false;
	}

	
	function getCityId($cityname){
		$p = Utills::concantPaths($this->Path->path_cache_cityname, md5($cityname).".txt");
		if(is_file($p)){
			return file_get_contents($p);
		}
		return 0;
	}
	
	function setCityId($cityname, $city_id){
		$p = Utills::concantPaths($this->Path->path_cache_cityname, md5($cityname).".txt");
		file_put_contents($p, $city_id);
	}
		
	function getCachedWeather($city_id, $date){
		if(Utills::isDateInCalendarRange($date)){
			return $this->_getCalendar($city_id);
		}
		return $this->_getHistory(	$city_id, 
									$date
									);
	}
	
	function setCachedWeather($a_info, $city_id, $date){
		if(Utills::isDateInCalendarRange($date)){
			$this->_setCalendar($a_info, $city_id);
		}else{
			$this->_setHistory(	
									$a_info,
									$city_id, 
									Utills::getNumberMonthFromDateNormal($date)
									);		
		}
	}
	
	private function _getCalendar($city_id){
		$p = Utills::concantPaths($this->Path->path_cache_calendar, $city_id.".txt");
		
		if(is_file($p)){
			$a_info = unserialize(file_get_contents($p));
			if($this->isValidCalendarCache($a_info['timestamp'])){
				return $a_info;
			}
		}
		return array();
	}
	
	private function _setCalendar($a_info, $city_id){
		$a_info['timestamp'] = time();
		$p = Utills::concantPaths($this->Path->path_cache_calendar, $city_id.".txt");
		file_put_contents($p, serialize($a_info));
	}
	
	private function _getHistory($city_id, $date){
		$number_month = Utills::getNumberMonthFromDateNormal($date);
		$p = Utills::concantPaths($this->Path->path_cache_history, $city_id.'/'.$number_month.'.txt');
		if(is_file($p)){
			$a_info_cutted_year = unserialize(file_get_contents($p));
			$a_info = $this->addYearToKeyAInfo(
									$a_info_cutted_year, 
									Utills::getYearFromDateNormal($date)
									);
			return $a_info;
		}
		return array();
	}
	
	private function addYearToKeyAInfo($a_info, $year){
		$a_weather = array();
		foreach($a_info['weather'] as $w_key => $w_row){
			$a_weather[$w_key.".".$year] = $w_row;
		}
		$a_info['weather'] = $a_weather;
		return $a_info;
	}
	
	private function _setHistory($a_info, $city_id, $number_month){
		$p = Utills::concantPaths($this->Path->path_cache_history, $city_id);
		if(!is_dir($p)) mkdir($p);
		$p = Utills::concantPaths($p, $number_month.'.txt');
		file_put_contents($p, serialize($a_info));
	}
	
	function getGisLabel(){
		return file_get_contents($this->Path->path_gis_label_file);
	}
	function setGisLabel($gis_label){
		file_put_contents($this->Path->path_gis_label_file, $gis_label);
	}		
}
?>