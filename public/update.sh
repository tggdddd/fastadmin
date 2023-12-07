echo $1;
cd $1

if [ -f "update.lock" ];then
  echo "文件存在"
  exit
  else
    touch "update.lock"
fi

git fetch
git reset --hard origin/master
npm i
npm run build

rm -f /data/filename