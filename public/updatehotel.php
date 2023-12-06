<?php
echo "开始更新";
ini_set("max_execution_time", 0);
ini_set("ignore_user_abort", true);
chdir("../../../hotel.jackr.cn");
echo `git fetch`;
echo `git reset --hard origin/master`;
echo `git pull`;
echo `npm i`;
echo `npm run build`;
echo "更新完成";

