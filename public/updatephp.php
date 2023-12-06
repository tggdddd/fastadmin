<?php
echo "开始更新";
ini_set("max_execution_time", 0);
ini_set("ignore_user_abort", true);
chdir("../");
echo `git fetch`;
echo `git reset --hard origin/master`;
echo `git pull`;
echo "更新完成";

