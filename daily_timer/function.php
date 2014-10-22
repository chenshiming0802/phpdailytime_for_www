<?php
/**
定时器启动
Auth:陈市明@2012-12-8
*/
function daily_timer_run(){
	
	$config = include('config.php');
	//单例运行，防止多例运行
	if(daily_timer_get_status($config,'is_alive')=='1'){return;}
	//获取url信息
	$url_info = parse_url($config['http_path']);
	if($url_info['port']==null){$url_info['port'] = '80';}
	if($url_info['path']==null){$url_info['path'] = '';}

	//异步调用
	$fp = @fsockopen($url_info['host'], $url_info['port'], $errno, $errstr, 5);
	//var_dump($url_info['host'].':'.$url_info['port']);
	//var_dump("{$url_info['path']}run.php");
	//var_dump($errno.'/'.$errstr);
	if (!$fp) {
		daily_timer_set_status($config,array('is_alive'=>'0'),false);	
		throw new Exception("Daily Timer launch error:{$errno} while connecting:".$url_info['host'].':'.$url_info['port']);
	} else {
		$out = "GET {$url_info['path']}/run.php  / HTTP/1.1\r\n";
		$out .= "Host: {$url_info['host']}\r\n";
		$out .= "Connection: Close\r\n\r\n";
	 
		fwrite($fp, $out);
		fclose($fp);
		daily_timer_set_status($config,array('is_alive'=>'1'),false);	
	}
}

/**
定时器运行process(需要异步调用)
Auth:陈市明@2012-12-8
*/
function daily_timer_process(){
	//配置读取
	$config = include('config.php');
	$_config = include('_config.php');

	ignore_user_abort(true);//设置与客户机断开是否会终止脚本的执行。
	set_time_limit(0); //设置脚本超时时间，为0时不受时间限制
	
	//启动任务,把取消状态值为否，并设置为运行中
	daily_timer_set_status($config,array('is_alive'=>'1','is_user_cancel'=>'0'),false);
	daily_timer_log_info('info',"Timer start success!");
	while(1){
	//if(true){	
		$status_ini = array();
		//是否要用户触发取消定时器
		if(daily_timer_is_stop($config)=='1'){
			daily_timer_log_info('info',"Timer stop success!");
			return;
		}
		//判断今天是否已经运行了定时器
		if(daily_timer_has_today_run($config,$_config)!='1'){
			//记录运行状态
			$status_ini['is_user_cancel'] = '0';
			$status_ini['last_awake_timer'] = daily_timer_datetime();//设置定时器醒来时间
			$status_ini['last_execute_timer_begin'] = daily_timer_datetime();
			
			foreach($config['tasks'] as $task_name=>$task_url){
				$i_result = @daily_timer_invoke_task($config,$task_name,$task_url);		
				if(!$i_result){
					daily_timer_log_info('fail',"{$task_name} invoke fail!({$task_url})");
				}
			}

			//记录运行状态
			$status_ini['last_execute_timer_end'] = daily_timer_datetime();
			$status_ini['last_execute_date'] = daily_timer_date();
			//运行状态写入文件
			daily_timer_set_status($config,$status_ini,false);
		}else{
			$status_ini['last_awake_timer'] = daily_timer_datetime();
			daily_timer_set_status($config,$status_ini,false);
		}
		//定时器休息
		sleep($config['sleep_seconds']);
	}
	daily_timer_set_status($config,array('is_alive'=>'0'),false);	
}

/**
是否停止任务
TestUnit:test_daily_timer_is_stop
Auth:陈市明@2012-12-8
*/
function daily_timer_is_stop($config){
	$is_user_cancel = daily_timer_get_status($config,'is_user_cancel');
	if($is_user_cancel=='1'){
		return '1';
	}else{
		return '0';
	}
}
/**
判断今天是否已经运行了定时器
TestUnit:test_daily_timer_has_today_run
Auth:陈市明@2012-12-8
*/
function daily_timer_has_today_run($config,$_config){
	if($_config['debug']===true){
		return 0;
	}
	$last_execute_date = daily_timer_get_status($config,'last_execute_date');
	if($last_execute_date==daily_timer_date()){
		return '1';
	}else{
		return '0';
	}
}

/**
触发任务
TestUnit:test_daily_timer_invoke_task
Auth:陈市明@2012-12-8
*/
function daily_timer_invoke_task($config,$task_name,$task_url){
	$result = '';
	$file = @fopen ($task_url, "r");
	if ($file) {
		$line = '';
		while (!feof ($file)) {
			$line = fgets ($file, 1024);
		}
		fclose($file);
		$result = "Task[{$task_name}] start,message:\r\n   ".$line;
		daily_timer_log_info('info',$result);	
	}else{
		$result = "{$task_name}:Url not found:".$task_url;
		daily_timer_log_info('fail',$result);	
	}
	return $result;
}

/**
日志记录 level=info,fail,warn
TestUnit:daily_timer_test_ini_operate
Auth:陈市明@2012-12-8
*/
function daily_timer_log_info($level,$str){
	$f = fopen('logs.txt','a');
	fwrite($f,daily_timer_datetime()."[{$level}] {$str}\r\n");
	fclose($f);	
}
/**
读状态
TestUnit:daily_timer_test_ini_operate
Auth:陈市明@2012-12-8
*/
function daily_timer_get_status($config,$key){
	$ini_array = @parse_ini_file($config['status_ini_file']);
	return $ini_array[$key];

}

/**
写状态
TestUnit:daily_timer_test_ini_operate
Auth:陈市明@2012-12-8
*/
function daily_timer_set_status($config,$data=array(),$isClear=false){
	if($isClear===false){
		//如果不清空所有的,则需要将现有ini文件中的数据读取出来
		$ini_array = @parse_ini_file($config['status_ini_file']);
		if(!isset($ini_array)) $ini_array = array();
		foreach($data as $k=>$v){
			$ini_array[$k] = $v;
		}
		$data = $ini_array;
	}
	$f = fopen($config['status_ini_file'],'w');
	foreach($data as $key=>$value){
		fwrite($f,"{$key}={$value}\r\n");
	}
	fclose($f);			
}
/**
获取当前时间,格式为:年-月-日 时:分:秒
Auth:陈市明@2012-12-8
*/
function daily_timer_datetime(){
	return date('y-m-d H:i:s');
}
/**
获取当前日期,格式为:年-月-日 时:分:秒
Auth:陈市明@2012-12-8
*/
function daily_timer_date(){
	return date('y-m-d');
}
/**
停止定时器
Auth:陈市明@2012-12-8
*/
function daily_timer_stop(){
	$config = include('config.php');
	if(daily_timer_get_status($config,'is_alive')=='1'){
		//如果任务当前时运行，才文字，否则停止的任务会出现正在停止的任务；但为了可能出现的状态错误，还是需要触发停止任务
		daily_timer_log_info('info','Timer is stopping,please wait!');	
	}
	daily_timer_set_status($config,array('is_alive'=>'0','is_user_cancel'=>'1','user_cancel_timer'=>daily_timer_datetime()),false);
}
/**
开始定时器
Auth:陈市明@2012-12-8
@deprecated
*/
function daily_timer_start($config){
	daily_timer_set_status($config,array('is_user_cancel'=>'0','user_cancel_timer'=>''),false);
}
/**
清空日志文件
Auth:陈市明@2012-12-8
*/
function daily_timer_clear_log(){
	@unlink('logs.txt');
}
/**
获取当前定时器状态
Auth:陈市明@2012-12-8
*/
function daily_timer_get_info($config,$_config){
	$info = array();
	$info['is_alive'] = daily_timer_get_status($config,'is_alive');//定时器是否启动
	$info['is_user_cancel'] = daily_timer_get_status($config,'is_user_cancel');//定时器是否暂停
	$info['last_execute_timer_begin'] = daily_timer_get_status($config,'last_execute_timer_begin');//上次运行任务时间(起)
	$info['last_execute_timer_end'] = daily_timer_get_status($config,'last_execute_timer_end');//上次运行任务时间(至)
	$info['last_awake_timer'] = daily_timer_get_status($config,'last_awake_timer');//上次定时器醒来的时间
	$info['has_today_run'] = daily_timer_has_today_run($config,$_config);//今天的任务是否已经执行

	$info['log_context'] = @file_get_contents('logs.txt');
	return $info;
}

?>