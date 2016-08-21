<?php
ini_set("display_errors",1);
error_reporting(E_ALL); 
set_time_limit(0);


include('class/Utills.class.php');
include('class/CacheManager.class.php');
include('class/Loader.class.php');
include('class/LogManager.class.php');
include('class/ParserManager.class.php');
include('class/PathManager.class.php');
include('class/UrlManager.class.php');
include('class/CalendarParser.class.php');
include('class/HistoryParser.class.php');
include('class/VerifierSettings.class.php');
include('class/HistoryDoctor.class.php');

try{
	$Api = new Api();
	list($cmd, $cityname, $a_date) = $Api->getDataFromQuery();
	$city_id = $Api->getCityIdFromCacheOrWeb($cityname);
	$urls = $Api->getUrlsForNoCached($city_id, $a_date);
	
	$Api->loadAndCache($urls);
	$Api->echoJsonFromCache($cmd, $city_id, $a_date);
	
}catch(Exception $e){
	echo $e->getMessage().".  Error Code: ".$e->getCode();
}
//$Api->L->printLog();


class Api{
	public $L;
	public $Cache;
	public $Parser;
	public $Loader;
	public $Url;

	const NOT_VALID_URL_DATEFORMAT 	= 2000;
	const NOT_VALID_CMD_FORMAT = 2001;
	const ZERO_LEN_OF_SEARCH_PAGE 	= 2002;
	const SEARCH_PAGE_NOT_FOUND_CITYNAME = 2003;
	const CANT_FOUND_CITY_ID = 2004;
	const CANT_FOUND_GIS_LABEL = 2005;
	
	function __construct(){
		$this->L		= new LogManager();
		$this->Cache	= new CacheManager();
		$this->Parser	= new ParserManager();
		$this->Loader	= new Loader();
		$this->Url		= new UrlManager();
	}
	function getCityIdFromCacheOrWeb($cityname){
		$this->L->toLog("cahce city title", __METHOD__);
		$city_id = $this->Cache->getCityId($cityname);		
		if($city_id){
			return $city_id;
		}
		$url = $this->Url->makeSearchUrl($cityname);
		$html = $this->Loader->download($url, $url);
		
		if(strlen($html)<=0) 
			throw new Exception("Can't get city_id, page has 0 byte len", self::ZERO_LEN_OF_SEARCH_PAGE);
		
		if($this->Parser->isCityNotFound($html)) 
			throw new Exception("Search page says: 'not found your cityname'", self::SEARCH_PAGE_NOT_FOUND_CITYNAME);

		$city_id = $this->Parser->fetchСityId($html);
		if(!$city_id)
			throw new Exception("Can't find a city_id in html, parser try do it, but not found", self::CANT_FOUND_CITY_ID);
		
		$this->Cache->setCityId($cityname, $city_id);
		
		$gis_label = $this->Parser->fetchGisLabel($html);
		if(strlen($gis_label) > 0)
			$this->Cache->setGisLabel($gis_label);
			//throw new Exception("Parser try find a gis_label value in html, but not found", self::CANT_FOUND_GIS_LABEL);
		
		return $city_id;
	}
	
	function getUrlsForNoCached($city_id, $a_date){
		$this->L->toLog("get urls", __METHOD__);
		$a_urls = array();
		foreach($a_date as $date){
			$a_info = $this->Cache->getCachedWeather($city_id, $date);
			if(count($a_info)<=0){
				$a_urls[] = array(	'date' 	=> $date,
									'url' 	=> $this->Url->makeWeatherUrl($city_id, $date),
									'city_id' 	=> $city_id,
									);
			}
		}
		return $a_urls;
	}
	
	function loadAndCache($a_urls){
		foreach($a_urls as $u){
			$u['html'] = $this->Loader->download($u['url'], $u['url']);
			if(Utills::isDateInCalendarRange($u['date'])) $a_info = $this->Parser->ripUpCalendarPage($u['html']);
			else{
				$m = Utills::getNumberMonthFromDateNormal($u['date']);
				$a_info = $this->Parser->ripUpHistoryPage(
											$u['html'], 
											$m
											);

				$a_info = Utills::roundAInfo('2015', $m, $a_info);				
				if(!Utills::isValidAInfo($a_info)){
					$HistoryDoctor = new HistoryDoctor($a_info, $u);
					$a_info = $HistoryDoctor->heal();
				}
				$a_info = Utills::clearRows($a_info);
				$a_info = Utills::add31Day($m, $a_info);
			}
			$this->Cache->setCachedWeather($a_info, $u['city_id'], $u['date']);
		}
	}

	function echoJsonFromCache($cmd, $city_id, $a_date){
		$this->L->toLog("echo json from cahce", __METHOD__);
		$a_res = array();
		foreach($a_date as $date){
			$a_weather = $this->Cache->getCachedWeather($city_id, $date);
			$date_url = Utills::dateNormalToDateUrl($date);
			$row_weather = $a_weather['weather'][$date_url];
			$a_res[$date_url] = $row_weather;
		}
		
		if($cmd == 'get') echo json_encode($a_res);
		else {
				echo "<PRE>"; 
				print_r($a_res); 
				echo "</PRE>";
		}
		return;
	}

	function getDataFromQuery(){
		global $_GET;
		$this->L->toLog("get data from query", __METHOD__);

		$a_date = array();
		$a_date[] = $this->checkDate($_GET['date_one']);
		$date_two = $this->checkDate($_GET['date_two']);
		if(strlen($date_two) > 0){
			$a_date[] = $date_two;
		}
		
		return array(
				$this->checkCmd($_GET['cmd']),
				$this->checkCity($_GET['cityname']),
				$a_date
				);
	}
	
	function checkCmd($cmd){
		if(!in_array($cmd, array('get', 'getdebug')))
			throw new Exception("Not valid cmd format in url:".$cmd, self::NOT_VALID_CMD_FORMAT);
		return $cmd;
	}
	
	function checkCity($city){
		return $city;
	}
	
	function checkDate($date_url){
		if(strlen($date_url) <= 0) return '';
		if(!Utills::isDateUrl($date_url)){
			throw new Exception("Not valid date format in url:".$date_url, self::NOT_VALID_URL_DATEFORMAT);
		}
		return Utills::dateUrlToDateNormal($date_url);
	}
}
?>