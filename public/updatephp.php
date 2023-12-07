<?php

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

