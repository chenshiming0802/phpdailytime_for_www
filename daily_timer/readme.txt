/*
Daily timer - 每天运行的定时器 
作者：陈市明
说明：每天凌晨定时运行，访问配置的Url，具体见config.php

[版本说明]
v1.0 陈市明于2012年12月8日创建

[Usage]
1.配置config.php<b></b>
2.编写代码
require 'function.php';
daily_timer_run();
3.项目目录设置读写权限chmod

[配置说明]
http_path:daily_timer所在的http访问路径
tasks:任务配置清单，key为任务名，不能为中文，value则是定时访问的URL
sleep_seconds:休眠时间（秒），通常为3600
status_ini_file:当前定时器运行的状态数据文件

[status ini键值说明]
is_alive:定时器是否运行中,系统生成
is_user_cancel:用户是否手动取消任务,配置写入
user_cancel_timer:用户手动触发取消任务的时间,配置写入
last_awake_timer:定时器上次醒来的时间,系统生成
last_execute_timer_begin:定时器上次运行任务的开始时间,系统生成
last_execute_timer_end:定时器上次运行任务的结束时间,系统生成
last_execute_date:定时器上次运行任务的日期,系统生成

[file list]
[[源码类]]
config.php:用户配置文件
_config.php:系统配置文件
function.php:基础API方法
index.php:首页,启动定时器
run.php:启动任务,需要异步调用该功能
[[测试类]]
testunit.php:测试unit代码
test_task/task1.php:测试任务
[[生成类]]
logs.txt:日志文件,自动生成
status.ini
*/