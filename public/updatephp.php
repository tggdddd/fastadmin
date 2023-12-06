<?php

ini_set("max_execution_time", 0);
ini_set("ignore_user_abort", true);
chdir("../");
$eof = php_sapi_name() == "cli" ? "\n" : "<br/>";
function exec_command($command)
{
    global $eof;
    echo "执行命令:$command" . $eof;
    $process = popen($command, "r");
    while (!feof($process)) {
        $output = fread($process, 1024);
        echo $output;
    }
    $exit_code = pclose($process);
    echo $eof;
    if ($exit_code == 0) {
        return true;
    }
    $errorOutput = stream_get_contents($process);
    echo "{$command}执行失败{$eof}退出代码:{$exit_code}" . $eof;
    echo "错误信息：{$errorOutput}" . $eof;
    exit;
}

echo "开始更新" . $eof;
exec_command("git fetch");
exec_command("git reset --hard origin/master");
echo "更新完成" . $eof;

