path="../public/"
target="/www/admin/jackr.cn_80/wwwroot"
rm -r "${target}/*"
mv ${path}/* "${target}/"
