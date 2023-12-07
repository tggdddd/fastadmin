<?php
$curl = curl_init();
$url = "https://d.pcs.baidu.com/file/caad44785lb8143ac7eb6f9d77ae7fe0?fid=2720476425-250528-665770846171084&dstime=1701858951&rt=sh&sign=FDtAERVJouK-DCb740ccc5511e5e8fedcff06b081203-g0dHRDFcnM3fHH4VOVlCYmZuiXM%3D&expires=8h&chkv=1&chkbd=0&chkpc=&dp-logid=9033260844556895809&dp-callid=0&shareid=54732464961&r=822048433&resvsflag=1-12-0-1-1-1&vuk=2738147328&file_type=0";
// 设置请求地址
$url = "http://yq01-cm00.baidupcs.com/file/caad44785lb8143ac7eb6f9d77ae7fe0?bkt=en-07c9b0a504a37060d38c3321763fdc69704e60f3c9bfe1fa3a2b92953ef517ea7f658aceca6e2036&fid=2720476425-250528-665770846171084&time=1701859363&sign=FDTAXUbGERLQlBHSKfWaqiu-DCb740ccc5511e5e8fedcff06b081203-DHaFTj%2B6qM%2BppIzYvTttfTUqvDo%3D&to=234&size=3973123380&sta_dx=3973123380&sta_cs=59&sta_ft=zip&sta_ct=5&sta_mt=5&fm2=MH%2CYangquan%2CAnywhere%2C%2C%E5%B9%BF%E4%B8%9C%2Ccmnet&ctime=1698642580&mtime=1698642580&resv0=-1&resv1=0&resv2=rlim&resv3=5&resv4=3973123380&vuk=2738147328&iv=0&htype=&randtype=&tkbind_id=0&newver=1&newfm=1&secfm=1&flow_ver=3&pkey=en-7623a03b7df8e816f1220e3b741df17a7f011ba8861e7aae398c79e07f95a92b21169f02952b3495&sl=76480590&expires=8h&rt=sh&r=822048433&vbdid=-&fin=Adobe+Photoshop+2024+v25.0.zip&rtype=1&dp-logid=9033260844556895809&dp-callid=0.1&tsl=80&csl=80&fsl=-1&csign=nvQI33CPo8ptqTa4LEV2zgy4rWA%3D&so=1&ut=6&uter=0&serv=0&uc=0&ti=970a8ec65273ef12d96c4978ca9a30db7cca4a53efe12060&hflag=30&from_type=1&adg=n&reqlabel=250528_f_545b2cf56b6172dacb71c3f2d500e1a4_-1_df10236e70b54c3235ab2c0d4762c449&fpath=0%E8%87%AA%E5%B7%B1%E7%B4%A0%E6%9D%90%E6%95%B4%E5%90%88%2F%E8%BD%AF%E4%BB%B6%E4%B8%8B%E8%BD%BD&by=themis&resvsflag=1-12-0-1-1-1";
curl_setopt($curl, CURLOPT_URL, $url);

// 设置http某些配置
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

curl_setopt($curl, CURLOPT_HEADER, [
    "User-Agent" => "pan.baidu.com",
    "Cookie" => "PANPSC=; BAIDUID=3340B118C8B541B63CBDE372180C4034:FG=1; BDUSS=3lSU2UwQ2pvWW0tZm9VSkw3VktnMHNrWVlheGlBWVlKRWxvTjhhSENubzVDejVsSVFBQUFBJCQAAAAAAAAAAAEAAACeo98wwo3Nxc6vAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADl-FmU5fhZlc"
]);
// 判断传进来的参数是否不为空
if (!empty($data)) {
    // 设置该请求为POST
    curl_setopt($curl, CURLOPT_POST, 1);
    // 把参数带入请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
}

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
file_put_contents("Adobe Photoshop 2024 v25.0.zip", curl_exec($curl));
$error = curl_error($curl);
curl_close($curl);