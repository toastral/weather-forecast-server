<?php
class PathManager{
	public $document_root;
	public $path_cache;
	public $path_cache_cityname;
	public $path_cache_calendar;
	public $path_cache_history;
	public $path_gis_label_file;

	function __construct(){
		$this->document_root 		= $_SERVER['DOCUMENT_ROOT'];
		$this->path_cache 			= Utills::concantPaths($this->document_root,'cache');
		$this->path_cache_cityname 	= Utills::concantPaths($this->path_cache, 	'cityname');
		$this->path_cache_calendar	= Utills::concantPaths($this->path_cache,	'calendar');
		$this->path_cache_history	= Utills::concantPaths($this->path_cache,	'history');
		$this->path_gis_label_file	= Utills::concantPaths($this->document_root,'gis_label.txt');
	}
}
?>