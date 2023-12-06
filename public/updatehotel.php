<?php
ini_set("max_execution_time", 0);
ini_set("ignore_user_abort", true);
chdir("../../../hotel.jackr.cn");
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
    $errorOutput = stream_get_contents($process);
    $exit_code = pclose($process);
    echo $eof;
    if ($exit_code == 0) {
        return true;
    }
    echo "{$command}执行失败{$eof}退出代码:{$exit_code}" . $eof;
    echo "错误信息：{$errorOutput}" . $eof;
    exit;
}

echo "开始更新" . $eof;
exec_command("git fetch");
exec_command("git reset --hard origin/master");
exec_command("npm i");
exec_command("npm run build");
echo "更新完成" . $eof;

