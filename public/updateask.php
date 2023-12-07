<?php
function lock()
{
    $lock = __FILE__ . "lock";
    if (file_exists($lock)) {
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
$current = __DIR__;
echoi("工作目录{$path}\n");
echoi("开始更新\n");
echoi(`cd $current&&sh update.php $path 2>&1`);
echoi("更新完成\n");
unlock();