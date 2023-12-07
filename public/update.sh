echo $1;
cd $1
git fetch 2>&1
git reset --hard origin/master 2>&1
npm i 2>&1
npm run build 2>&1