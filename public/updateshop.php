<?php
function lock()
{
    file_exists("updateshop") && exit;
    file_put_contents("updateshop", "");
}

function unlock()
{
    @unlink("updateshop");
}

lock();
if (php_sapi_name() != "cli") {
    $script = __FILE__;
    `php {$script}`;
    exit;
}
ini_set("max_execution_time", 0);
ini_set("ignore_user_abort", true);
$path = "../../../shop.jackr.cn";
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
exec_command("git pull");
exec_command("npm i");
exec_command("npm run build");
echoi("更新完成\n");

unlock();