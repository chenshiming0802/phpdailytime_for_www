/*
Daily timer - ÿ�����еĶ�ʱ�� 
���ߣ�������
˵����ÿ���賿��ʱ���У��������õ�Url�������config.php

[�汾˵��]
v1.0 ��������2012��12��8�մ���

[Usage]
1.����config.php<b></b>
2.��д����
require 'function.php';
daily_timer_run();
3.��ĿĿ¼���ö�дȨ��chmod

[����˵��]
http_path:daily_timer���ڵ�http����·��
tasks:���������嵥��keyΪ������������Ϊ���ģ�value���Ƕ�ʱ���ʵ�URL
sleep_seconds:����ʱ�䣨�룩��ͨ��Ϊ3600
status_ini_file:��ǰ��ʱ�����е�״̬�����ļ�

[status ini��ֵ˵��]
is_alive:��ʱ���Ƿ�������,ϵͳ����
is_user_cancel:�û��Ƿ��ֶ�ȡ������,����д��
user_cancel_timer:�û��ֶ�����ȡ�������ʱ��,����д��
last_awake_timer:��ʱ���ϴ�������ʱ��,ϵͳ����
last_execute_timer_begin:��ʱ���ϴ���������Ŀ�ʼʱ��,ϵͳ����
last_execute_timer_end:��ʱ���ϴ���������Ľ���ʱ��,ϵͳ����
last_execute_date:��ʱ���ϴ��������������,ϵͳ����

[file list]
[[Դ����]]
config.php:�û������ļ�
_config.php:ϵͳ�����ļ�
function.php:����API����
index.php:��ҳ,������ʱ��
run.php:��������,��Ҫ�첽���øù���
[[������]]
testunit.php:����unit����
test_task/task1.php:��������
[[������]]
logs.txt:��־�ļ�,�Զ�����
status.ini
*/