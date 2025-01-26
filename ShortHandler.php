<?php 
//ShortHandler.php
use App as A;
require_once 'DBConfig.php';

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
clearstatcache();
header("X-Robots-Tag: noindex");
header("Referrer-Policy: no-referrer");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: -1");
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	//Generate URL
	if(!empty($_POST['url']) && !empty($_POST['action'])){
		$url = trim(strip_tags($_POST['url']));
		$action = trim(strip_tags($_POST['action']));
		if(!empty($action)){
			usleep(5000);
			$db = new A\DBConfig();
			$uri = $db->GenerateURL($url);
			if(!empty($uri)){
				http_response_code(200);
				echo json_encode($uri);
			}
		}else{
			http_response_code(404);
			//echo 'Invalid request';
		}
	}else{
		http_response_code(404);
		// echo 'Invalid request';
	}

}else{
	http_response_code(404);
	//echo 'Invalid request';
}