<?php 

	error_reporting(E_ALL & ~E_NOTICE);

	require_once 'autoClass.php';
	require_once '../config/config.php';	//$link - database

	use Classes\Schedule;

	//Check date
	$date = file_get_contents('php://input');
	try {
		preg_match_all("#^\d{4}-\d{2}-\d{2}$#", $date, $matches);
		$date = $matches[0][0];

		if(empty($date)){
			throw new Exception();
		}
	} catch (Exception $e) {
		header("HTTP/1.1 403 Forbidden");
		exit;
	}

	//Get free hours
	$schedule = new Schedule($link);
	$hours = $schedule->getFreeHours($date);

	//Return json-answer
	header('Content-Type: application/json');
	exit(json_encode($hours));
?>