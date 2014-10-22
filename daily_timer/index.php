<?php
require 'function.php';
$_config = include('_config.php');
$config = include('config.php');
$info = daily_timer_get_info($config,$_config);


$method = $_GET['method'];
$message = '';
$is_error = '0';
if($method=='run'){
	try{
		daily_timer_run();
		$message = '启动成功';

	}catch(Exception $e){
		$message = '启动失败，原因：'.$e->getMessage(); 
		$is_error = '1';
	}
}else if($method=='stop'){
	daily_timer_stop();
	$message = '关闭成功';
}else if($method=='clear_log'){
	daily_timer_clear_log();
}



//如果醒来时间远小于当前时间，则认为运行有问题
$now=date("y-m-d h:i:s");
$last_awake_timer = $info['last_awake_timer'];
//var_dump($now);
//var_dump($last_awake_timer);
//echo (strtotime($now)-strtotime($last_awake_timer))."<BR>";
if($info['is_alive']=='1' && $info['last_awake_timer']!='' && strtotime($last_awake_timer)+(int)$config['sleep_seconds']*10<strtotime($now)){
	$message .= '&nbsp;定时器有异常,请重新关闭后重新启动;如果还是这样,可能是主机不支持!';
}
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html;charset=UTF-8">
<title></title>
</head>
<body>
<?php
echo '<span style="font-size:26px;font-weight:bold;">'.$_config['project_name'].'</span> '.$_config['project_version'].' <hr>';

?>

<?php
echo '<B>当前运行状态:</B>:'.($info['is_alive']=='1'?'运行中 <a href="?method=stop">停止定时器</a>':'停止中 <a href="?method=run">启动定时器</a>').'<BR>';
echo "<span style='color:red'>{$message}</span><BR>";

echo '上次运行任务时间:'.$info['last_execute_timer_begin'].' 至 '.$info['last_execute_timer_end'].'<BR>';
echo '今天的任务是否已经执行:'.($info['has_today_run']=='1'?'已运行':'未运行').'<BR>';
echo '定时器上次醒来时间:'.$info['last_awake_timer'].'<!-- (已经休眠'.(strtotime($now)-strtotime($last_awake_timer)).'秒) --><BR>';
echo '运行的任务:<BR>';
foreach($config['tasks'] as $k=>$v){
	echo "&nbsp;&nbsp;{$k}:{$v}<BR>";
}
echo '运行日志:&nbsp;&nbsp;<a href="index.php">点击刷新</a><BR><textarea cols="100" rows="20">'.$info["log_context"].'</textarea>'.'<BR>';
echo '<a href="?method=clear_log">清空日志文件</a>';


$is_user_cancel = daily_timer_get_status($config,'is_user_cancel');
$is_alive = daily_timer_get_status($config,'is_alive');
//var_dump($is_user_cancel);
//var_dump($is_alive);
if($is_alive=='1' && ($is_user_cancel=='1' || $is_user_cancel==null)){
	echo '<BR><span style="color:red">启动异常,可能是由于主机不支持fsockopen函数,请点击<input type="button" value="强制启动" onClick="forcedRunTimer()" id="button_forcedRunTimer">，打开页面后5秒请手动关掉该页面，再来刷新本页面</span>'.'<BR>';
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	function forcedRunTimer(){
		var openw = window.open('run.php');
		openw.close();
		setTimeout(function(){
			openw.close();
			window.location='index.php';
		},2000);	
		document.getElementById("button_forcedRunTimer").value = "请耐心等待";
		document.getElementById("button_forcedRunTimer").disabled =true;
	}
//-->
</SCRIPT>
<center>Copyright @ 2009-2010 SprCore Develop Group. Mail:sprcore@163.com</center>
<center><script language="javascript" type="text/javascript" src="http://js.users.51.la/15204358.js"></script>
<noscript><a href="http://www.51.la/?15204358" target="_blank"><img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/15204358.asp" style="border:none" /></a></noscript></center>
</body>
</html>