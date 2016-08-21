<?php
class VerifierSettings{
	
//	const DIR_HAS_CREATED 	= 1000;
	const UNSUCC_CREATE_DIR = 1001;
	const UNSUCC_FOUND_DIR 	= 1002;
	const UNSUCC_DEL_DIR 	= 1003;
	const FILE_HAS_CREATED 	= 1004;
	const UNSUCC_CREATE_FILE= 1005;
	const UNSUCC_FOUND_FILE	= 1006;
	const UNSUCC_DEL_FILE 	= 1007;
	
	private $Path;
	
	function __construct(){
		$this->Path	=	new PathManager();
		$this->checkAvailabilityAndPermissions();
	}	
	function checkAvailabilityAndPermissions(){		
		$this->tryCreateDir($this->Path->path_cache);
		
		$d_cityname = $this->Path->path_cache_cityname;
		$d_month 	= $this->Path->path_cache_calendar;
		$d_history 	= $this->Path->path_cache_history;
		
		$this->tryCreateDir($d_cityname);
		$this->tryCreateDir($d_month);
		$this->tryCreateDir($d_history);
		
		$d_history_id = Utills::concantPaths($d_history, '111000111');
		$this->tryCreateDir($d_history_id);
		
		$this->tryCreateFile(Utills::concantPaths($d_cityname, "111.txt"));
		$this->tryDeleteFile(Utills::concantPaths($d_cityname, "111.txt"));
		
		$this->tryCreateFile(Utills::concantPaths($d_month, "111.txt"));
		$this->tryDeleteFile(Utills::concantPaths($d_month, "111.txt"));		
		
		$this->tryCreateFile(Utills::concantPaths($d_history_id, "111.txt"));
		$this->tryDeleteFile(Utills::concantPaths($d_history_id, "111.txt"));
		
		$this->tryDeleteDir($d_history_id);
	}
	
	function tryCreateDir($path){
		if(!is_dir($path)){
			mkdir($path);
		}
		if(!is_dir($path)) throw new Exception("Can't create dir:".$path, self::UNSUCC_CREATE_DIR);
	}

	function tryDeleteDir($path){
		if(!is_dir($path)) throw new Exception("Can't find dir to delete:".$path, self::UNSUCC_FOUND_DIR);
		rmdir($path);
		if(is_dir($path)) throw new Exception("Can't delete dir:".$path, self::UNSUCC_DEL_DIR);
	}

	function tryCreateFile($path){
		if(is_file($path)) throw new Exception("File to create has already been created:".$path, self::FILE_HAS_CREATED);
		file_put_contents($path, rand(10000,99999));
		if(!is_file($path)) throw new Exception("Can't create file:".$path, self::UNSUCC_CREATE_FILE);
	}
	
	function tryDeleteFile($path){
		if(!is_file($path)) throw new Exception("Can't find file to delete:".$path, self::UNSUCC_FOUND_FILE);
		@unlink($path);
		if(is_file($path)) throw new Exception("Can't delete file:".$path, self::UNSUCC_DEL_FILE);
	}
}
?>