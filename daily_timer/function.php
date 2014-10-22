<?php
/**
��ʱ������
Auth:������@2012-12-8
*/
function daily_timer_run(){
	
	$config = include('config.php');
	//�������У���ֹ��������
	if(daily_timer_get_status($config,'is_alive')=='1'){return;}
	//��ȡurl��Ϣ
	$url_info = parse_url($config['http_path']);
	if($url_info['port']==null){$url_info['port'] = '80';}
	if($url_info['path']==null){$url_info['path'] = '';}

	//�첽����
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
��ʱ������process(��Ҫ�첽����)
Auth:������@2012-12-8
*/
function daily_timer_process(){
	//���ö�ȡ
	$config = include('config.php');
	$_config = include('_config.php');

	ignore_user_abort(true);//������ͻ����Ͽ��Ƿ����ֹ�ű���ִ�С�
	set_time_limit(0); //���ýű���ʱʱ�䣬Ϊ0ʱ����ʱ������
	
	//��������,��ȡ��״ֵ̬Ϊ�񣬲�����Ϊ������
	daily_timer_set_status($config,array('is_alive'=>'1','is_user_cancel'=>'0'),false);
	daily_timer_log_info('info',"Timer start success!");
	while(1){
	//if(true){	
		$status_ini = array();
		//�Ƿ�Ҫ�û�����ȡ����ʱ��
		if(daily_timer_is_stop($config)=='1'){
			daily_timer_log_info('info',"Timer stop success!");
			return;
		}
		//�жϽ����Ƿ��Ѿ������˶�ʱ��
		if(daily_timer_has_today_run($config,$_config)!='1'){
			//��¼����״̬
			$status_ini['is_user_cancel'] = '0';
			$status_ini['last_awake_timer'] = daily_timer_datetime();//���ö�ʱ������ʱ��
			$status_ini['last_execute_timer_begin'] = daily_timer_datetime();
			
			foreach($config['tasks'] as $task_name=>$task_url){
				$i_result = @daily_timer_invoke_task($config,$task_name,$task_url);		
				if(!$i_result){
					daily_timer_log_info('fail',"{$task_name} invoke fail!({$task_url})");
				}
			}

			//��¼����״̬
			$status_ini['last_execute_timer_end'] = daily_timer_datetime();
			$status_ini['last_execute_date'] = daily_timer_date();
			//����״̬д���ļ�
			daily_timer_set_status($config,$status_ini,false);
		}else{
			$status_ini['last_awake_timer'] = daily_timer_datetime();
			daily_timer_set_status($config,$status_ini,false);
		}
		//��ʱ����Ϣ
		sleep($config['sleep_seconds']);
	}
	daily_timer_set_status($config,array('is_alive'=>'0'),false);	
}

/**
�Ƿ�ֹͣ����
TestUnit:test_daily_timer_is_stop
Auth:������@2012-12-8
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
�жϽ����Ƿ��Ѿ������˶�ʱ��
TestUnit:test_daily_timer_has_today_run
Auth:������@2012-12-8
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
��������
TestUnit:test_daily_timer_invoke_task
Auth:������@2012-12-8
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
��־��¼ level=info,fail,warn
TestUnit:daily_timer_test_ini_operate
Auth:������@2012-12-8
*/
function daily_timer_log_info($level,$str){
	$f = fopen('logs.txt','a');
	fwrite($f,daily_timer_datetime()."[{$level}] {$str}\r\n");
	fclose($f);	
}
/**
��״̬
TestUnit:daily_timer_test_ini_operate
Auth:������@2012-12-8
*/
function daily_timer_get_status($config,$key){
	$ini_array = @parse_ini_file($config['status_ini_file']);
	return $ini_array[$key];

}

/**
д״̬
TestUnit:daily_timer_test_ini_operate
Auth:������@2012-12-8
*/
function daily_timer_set_status($config,$data=array(),$isClear=false){
	if($isClear===false){
		//�����������е�,����Ҫ������ini�ļ��е����ݶ�ȡ����
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
��ȡ��ǰʱ��,��ʽΪ:��-��-�� ʱ:��:��
Auth:������@2012-12-8
*/
function daily_timer_datetime(){
	return date('y-m-d H:i:s');
}
/**
��ȡ��ǰ����,��ʽΪ:��-��-�� ʱ:��:��
Auth:������@2012-12-8
*/
function daily_timer_date(){
	return date('y-m-d');
}
/**
ֹͣ��ʱ��
Auth:������@2012-12-8
*/
function daily_timer_stop(){
	$config = include('config.php');
	if(daily_timer_get_status($config,'is_alive')=='1'){
		//�������ǰʱ���У������֣�����ֹͣ��������������ֹͣ�����񣻵�Ϊ�˿��ܳ��ֵ�״̬���󣬻�����Ҫ����ֹͣ����
		daily_timer_log_info('info','Timer is stopping,please wait!');	
	}
	daily_timer_set_status($config,array('is_alive'=>'0','is_user_cancel'=>'1','user_cancel_timer'=>daily_timer_datetime()),false);
}
/**
��ʼ��ʱ��
Auth:������@2012-12-8
@deprecated
*/
function daily_timer_start($config){
	daily_timer_set_status($config,array('is_user_cancel'=>'0','user_cancel_timer'=>''),false);
}
/**
�����־�ļ�
Auth:������@2012-12-8
*/
function daily_timer_clear_log(){
	@unlink('logs.txt');
}
/**
��ȡ��ǰ��ʱ��״̬
Auth:������@2012-12-8
*/
function daily_timer_get_info($config,$_config){
	$info = array();
	$info['is_alive'] = daily_timer_get_status($config,'is_alive');//��ʱ���Ƿ�����
	$info['is_user_cancel'] = daily_timer_get_status($config,'is_user_cancel');//��ʱ���Ƿ���ͣ
	$info['last_execute_timer_begin'] = daily_timer_get_status($config,'last_execute_timer_begin');//�ϴ���������ʱ��(��)
	$info['last_execute_timer_end'] = daily_timer_get_status($config,'last_execute_timer_end');//�ϴ���������ʱ��(��)
	$info['last_awake_timer'] = daily_timer_get_status($config,'last_awake_timer');//�ϴζ�ʱ��������ʱ��
	$info['has_today_run'] = daily_timer_has_today_run($config,$_config);//����������Ƿ��Ѿ�ִ��

	$info['log_context'] = @file_get_contents('logs.txt');
	return $info;
}

?>