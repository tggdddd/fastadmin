<?php
echo "开始更新";
chdir("../../../hotel.jackr.cn");
`git fetch`;
`git reset --hard origin/master`;
`git pull`;
`npm run build`;
echo "更新完成";
