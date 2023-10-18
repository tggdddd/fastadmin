<?php
$root = "/www/admin/jackr.cn_80/wwwroot/";
$projectName = "fastadmin";
$remote = "https://gitee.com/xiao-runjie/fastadmin.git";
chdir($root);
`rm -r $projectName`;
`git clone $remote`;