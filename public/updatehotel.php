<?php
ini_set("ignore_user_abort", false);
ini_set("max_execution_time", 120);
function detect($lock, $seek)
{
    if (file_exists($lock) && file_get_contents($lock) != $seek) {
        echo "请勿多次请求";
        exit;
    }
}

function lock($lock)
{
    $seek = rand(0, 99) . "lock";
    detect($lock, $seek);
    file_put_contents($lock, $seek);
    return $seek;
}

function unlock($lock)
{
    @unlink($lock);
}

function echoi($str)
{
    echo php_sapi_name() == "cli" ? $str : str_replace("\n", "<br/>", $str);
}

$lock = __FILE__ . "lock";
$seek = lock($lock);
for ($i = 0; $i < 7; $i++) {
    sleep(rand(3, 10));
    detect($lock, $seek);
}
$path = realpath("../../../hotel.jackr.cn");
$script = __DIR__ . "/update.sh";
echoi("开始更新\n");
$command = "sh {$script} {$path} 2>&1";
echoi("执行命令：$command\n");
echoi(`$command`);
echoi("更新完成\n");
unlock($lock);