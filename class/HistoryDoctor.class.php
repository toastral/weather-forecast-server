<?php
class HistoryDoctor{
	private $city_id;
	private $m;
	private $a_info;
	private $Url;
	private $Loader;
	
	
	function __construct($a_info, $u){
		$this->city_id 	= $u['city_id'];
		$this->m 		= Utills::getNumberMonthFromDateNormal($u['date']);
		$this->a_info 	= $a_info;		
		$this->Loader	= new Loader();
		$this->Url		= new UrlManager();
	}
	
	function heal(){
		$a_years = array('2014', '2013', '2012');
		foreach($a_years as $year){
			$url = $this->Url->makeWeatherUrlByYearMonth($this->city_id, $year, $this->m);
			$html = $this->Loader->download($url, $url);
			$HP = new HistoryParser($this->m);
			$HP->doParse($html);
			$this->a_info['weather'] = $this->mergeAInfoLeft($this->a_info['weather'], $HP->a_calendar);
			$this->a_info = Utills::roundAInfo($year, $this->m, $this->a_info);
			if(Utills::isValidAInfo($this->a_info)){
				return $this->a_info;
			}
		}
		return $this->a_info;		
	}
	
	function mergeAInfoLeft($a_info_1, $a_info_2){
		$a = $a_info_1;
		foreach($a as $key => $row){
			if(!$row['row_status']){
				if(isset($a_info_2[$key])) $a_info_1[$key] = $a_info_2[$key];
			}
		}
		return $a_info_1;
	}

}
?>