<?php
class LogManager{
	private $time_start;
	private $a_log = array();

	function __construct(){
		$this->time_start 			= $this->microtimeFloat();
	}
	function toLog($txt, $func_name=''){
		$this->a_log[]="[".round($this->calcTimeSpend(), 5)."][$func_name]:".$txt;
	}
	function calcTimeSpend(){
		return ($this->microtimeFloat() - $this->time_start);
	}
	function microtimeFloat(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	function printLog(){
		echo "<PRE>";
		print_r($this->a_log);
		echo "</PRE>";
	}
}
?>