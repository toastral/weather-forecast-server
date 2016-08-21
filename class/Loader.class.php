<?php
class Loader{
	function download($url, $ref){
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_setopt($ch, CURLOPT_VERBOSE, 0);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0');
	  curl_setopt($ch, CURLOPT_REFERER, $ref);
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_TIMEOUT, 40);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	  $response = curl_exec($ch);
	  curl_close($ch);
	  return $response;
	}
}
?>