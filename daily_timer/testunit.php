<pre>
<?php
require 'function.php';

//var_dump(function_exists('fsockopen'));

//test_daily_timer_ini_operate();
//测试ini的操作API
function test_daily_timer_ini_operate(){
	$config = include('config.php');
	daily_timer_set_status($config,array('last_execute_timer'=>date('y-m-d H:i:s'),'last_execute_date'=>date('y-m-d')));
	var_dump(daily_timer_get_status($config,'last_execute_date'));
	daily_timer_set_status($config,array('abc'=>'1234'),false);
}
//测试任务触发的API
//test_daily_timer_invoke_task();
function test_daily_timer_invoke_task(){
	$config = include('config.php');
	var_dump($config);
	echo daily_timer_invoke_task($config,'task1','http://www.sprcore.com/daily_timer/test_task/task1.php');
}
//测试停止任务
//test_daily_timer_is_stop();
function test_daily_timer_is_stop(){
	$config = include('config.php');
	daily_timer_set_status($config,array('is_user_cancel'=>'1'));
	var_dump(daily_timer_is_stop($config));
}
//测试判断今天是否已经运行了定时器
//test_daily_timer_has_today_run();
function test_daily_timer_has_today_run(){
	$config = include('config.php');
	daily_timer_set_status($config,array('last_execute_date'=>daily_timer_date()));
	var_dump(daily_timer_has_today_run($config));
}

?>
</pre>