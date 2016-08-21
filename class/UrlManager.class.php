<?php
class UrlManager{
	public $http_domain = "https://www.gismeteo.ru/";
	//public $http_domain = "http://test-gis.com/";
	function __construct(){
		
	}	
	function makeSearchUrl($cityname){
		$Cache		=	new CacheManager();
		$gis_label 	= $Cache->getGisLabel();
		return $this->http_domain."city/?".$gis_label."=".$cityname."&searchQueryData=";
	}
	function makeWeatherUrl($city_id, $date){
		if(Utills::isDateInCalendarRange($date)){
			// return calendar url
			return $this->http_domain."month/".$city_id."/";
		}
		$m = Utills::getNumberMonthFromDateNormal($date);
		return $this->http_domain."diary/".$city_id."/2015/".intval($m)."/";
	}
	
	function makeWeatherUrlByYearMonth($city_id, $year, $month){
		return $this->http_domain."diary/".$city_id."/".$year."/".intval($month)."/";
	}
}
?>