<?php

ini_set("max_execution_time", 0);
ini_set("ignore_user_abort", true);
chdir("../");
function exec_command($command)
{
    $process = popen($command, "r");
    while (!feof($process)) {
        $output = fread($process, 1024);
        echo $output;
    }
    $exit_code = pclose($process);
    if ($exit_code == 0) {
        return true;
    }
    echo "{$command}执行失败，退出代码:{$exit_code}";
    exit;
}

echo "开始更新";
exec_command("git fetch");
exec_command("git reset --hard origin/master");
echo "更新完成";

