<?php
echo "开始更新";
chdir("../../../shop.jackr.cn");
echo `git fetch`;
echo `git reset --hard origin/master`;
echo `git pull`;
echo `npm run build`;
echo "更新完成";

