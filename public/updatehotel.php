<?php
echo "开始更新";
chdir("../../../hotel.jackr.cn");
echo `git fetch`;
echo `git reset --hard origin/master`;
echo `git pull`;
echo `npm i`;
echo `npm run build`;
echo "更新完成";

