<?php
$data = <<<EOL
a:21:{s:5:"SHELL";s:9:"/bin/bash";s:3:"PWD";s:38:"/www/wwwroot/jackr.cn/fastadmin/public";s:7:"LOGNAME";s:4:"root";s:16:"XDG_SESSION_TYPE";s:3:"tty";s:10:"MOTD_SHOWN";s:3:"pam";s:4:"HOME";s:5:"/root";s:4:"LANG";s:11:"en_US.UTF-8";s:14:"SSH_CONNECTION";s:28:"127.0.0.1 33502 127.0.0.1 22";s:17:"XDG_SESSION_CLASS";s:4:"user";s:4:"TERM";s:5:"xterm";s:4:"USER";s:4:"root";s:5:"SHLVL";s:1:"1";s:14:"XDG_SESSION_ID";s:2:"26";s:15:"XDG_RUNTIME_DIR";s:11:"/run/user/0";s:10:"SSH_CLIENT";s:18:"127.0.0.1 33502 22";s:13:"XDG_DATA_DIRS";s:50:"/usr/local/share:/usr/share:/var/lib/snapd/desktop";s:4:"PATH";s:130:"/www/server/nodejs/v20.10.0/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games:/snap/bin";s:24:"DBUS_SESSION_BUS_ADDRESS";s:25:"unix:path=/run/user/0/bus";s:7:"SSH_TTY";s:10:"/dev/pts/0";s:6:"OLDPWD";s:5:"/root";s:1:"_";s:12:"/usr/bin/php";}
EOL;
$env = unserialize($data);
foreach ($env as $k => $v) {
    putenv($k . "=" . $v);
}

ini_set("max_execution_time", 0);
ini_set("ignore_user_abort", true);
$path = "../";
chdir($path);
$path = getcwd();
function echoi($str)
{
    echo php_sapi_name() == "cli" ? $str : str_replace("\n", "<br/>", $str);
}
function exec_command($command)
{
    echoi("执行命令:$command\n");
    echoi(`$command 2>&1`);
}


echoi("开始更新\n");
echoi("执行用户" . `whoami` . "\n");
echoi("当前工作目录：" . getcwd() . "\n");
echoi("环境变量：" . var_dump(getenv()) . "\n");
exec_command("git fetch");
exec_command("git reset --hard origin/master");
echoi("更新完成\n");

