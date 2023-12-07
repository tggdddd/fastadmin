echo $1;
cd $1
git fetch
git reset --hard origin/master
npm i
npm run build