<?php
class Utills{
	static function roundAInfo($year, $m, $a_info){
		$m = sprintf("%02d", $m);
		$max = date("t", strtotime($year."-".$m));
		$a = $a_info['weather'];
		$a_res = array();
		for($i=1; $i<=$max; $i++){
			$ind = sprintf("%02d", $i).".".$m;
			if(!isset($a[$ind])){			
				$a_res[$ind] = array('max' => '', 'min' => '', 'w' => 0, 'row_status' => 0 );
			}else{
				$a_res[$ind] = $a[$ind];
			}
		}
		$a_info['weather'] = $a_res;
		return $a_info;
	}
	
	static function clearRows($a_info){
		$a = $a_info['weather'];
		foreach($a as $key => $row){
			$status = $row['row_status'];
			unset($row['row_status']);
			if(!$status){
				$row = 'null';
			}
			$a_info['weather'][$key] = $row;
		}
		return $a_info;
	}

	static function add31Day($m, $a_info){
		$a = $a_info['weather'];
		$m = sprintf("%02d", $m);
		
		if(intval($m) != 2){			
			if(!isset($a['31.'.$m])){
				$a_info['weather']['31.'.$m] = array_pop($a);
			}
		}else{
			if(!isset($a['29.'.$m])){
				$a_info['weather']['29.'.$m] = array_pop($a);
			}
		}
		return $a_info;
	}

	static function isValidAInfo($a_info){
		foreach($a_info['weather'] as $row){
			if($row['row_status'] <= 0 ) return false;
		}
		return true;
	}
	
	static function concantPaths($p1, $p2){
		if(!preg_match("/.*?\/$/", $p1, $patt)){
			$p1=$p1."/";
		}
		if(preg_match("/^\/(.*?)$/", $p2, $patt)){
			$p2 = $patt[1];
		}
		return $p1.$p2;
	}
	
	static function getWeatherCodeByTitle($title){
		$title = Utills::strtolower_ru($title);
		$a_seeking_words = array(
			0 => array("гроза", 1),
			1 => array("град", 	2),
			2 => array("снег", 	3),
			3 => array("туман", 4),
			4 => array("небольшой дождь", 6),
			5 => array("дождь", 5),
			6 => array("ветер", 9),
			7 => array("переменная облачность", 8),
			8 => array("облачн",7),
			9 => array("ясно", 10)
		);
		
		foreach($a_seeking_words as $row){
			list($word, $index) = $row;
			if(preg_match("/".$word."/", $title)){
				return $index;
			}
		}
		return 0;
	}
	
	static function isDateUrl($date_url){
		if(preg_match('/(\d+){2}\.(\d+){2}\.(\d+){4}/', $date_url, $patt)){
			return true;
		}
		return false;
	}
	
	static function dateUrlToDateNormal($date_url){
		preg_match('/(\d+)\.(\d+)\.(\d+)/', $date_url, $patt);
		return sprintf("%04d-%02d-%02d", $patt[3],$patt[2],$patt[1]);
	}
	
	static function dateNormalToDateUrl($date_normal){
		preg_match('/(\d+)-(\d+)-(\d+)/', $date_normal, $patt);
		return sprintf("%02d.%02d.%04d", $patt[3],$patt[2],$patt[1]);
	}
	
	static function getNumberMonthFromDateNormal($date_normal){
		preg_match('/(\d+)-(\d+)-(\d+)/', $date_normal, $patt);
		return $patt[2];
	}
	
	static function getYearFromDateNormal($date_normal){
		preg_match('/(\d+)-(\d+)-(\d+)/', $date_normal, $patt);
		return $patt[1];
	}	
	
	static function isDateInCalendarRange($date){		
		$date_stamp = strtotime($date);
		$left_range_boundary = strtotime(date("Y-m-d", time()));
		$sutki = 86400;
		$right_range_boundary = strtotime(date("Y-m-d", time()+$sutki*27));
		if($date_stamp < $left_range_boundary){
			return false;
		}
		if($date_stamp > $right_range_boundary){
			return false;
		}
		return true;
	}
	static function strtolower_ru($text) {
		$alfavitlover = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
		$alfavitupper = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
		return str_replace($alfavitupper,$alfavitlover,strtolower($text));
	}
}
?>