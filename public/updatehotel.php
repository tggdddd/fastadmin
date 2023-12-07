<?php
function echoi($str)
{
    echo php_sapi_name() == "cli" ? $str : str_replace("\n", "<br/>", $str);
}
$path = realpath("../../../hotel.jackr.cn");
$script = __DIR__ . "/update.sh";
echoi("开始更新\n");
$command = "sh {$script} {$path} > update.log";
echoi("执行命令：$command\n");
`$command`;
echoi("更新完成\n");