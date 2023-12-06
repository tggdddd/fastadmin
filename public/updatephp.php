<?php
echo "开始更新";
echo `git fetch`;
echo `git reset --hard origin/master`;
echo `git pull`;
echo "更新完成";

