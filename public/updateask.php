<?php
function lock()
{
    $lock = __FILE__ . "lock";
    if (file_exists($lock)) {
        echo "请勿多次请求";
        exit;
    }
    touch($lock);
}

function unlock()
{
    $lock = __FILE__ . "lock";
    @unlink($lock);
}
function echoi($str)
{
    echo php_sapi_name() == "cli" ? $str : str_replace("\n", "<br/>", $str);
}

lock();
ini_set("ignore_user_abort", true);
$path = realpath("../../../ask.jackr.cn");
$script = __DIR__ . "/update.sh";
echoi("开始更新\n");
$command = "sh {$script} {$path} 2>&1";
echoi("执行命令：$command\n");
echoi(`$command`);
echoi("更新完成\n");
unlock();