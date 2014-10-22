<?php
$url = 'http://'.$_SERVER['HTTP_HOST'].''.$_SERVER['SCRIPT_NAME'];
$url = substr($url,0,strrpos($url,'/')+1);//http://127.0.0.1/daily_timer/'
return array(
	'http_path'=>$url,
	'tasks' => array(
		'task1'=>"http://127.0.0.1/daily_timer/test_task/task1.php",	
	),
	'sleep_seconds' => 5,
	'status_ini_file' => 'status.ini',
);
?>