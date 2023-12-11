<?php
require_once "autoEntity.php";
$mysql = get_conn($host = "127.0.0.1", $port = "3306", $user = "root", $password = "root", $database = "course");
function randstr($len = 10, $special = false)
{
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );

    if ($special) {
        $chars = array_merge($chars, array(
            "!", "@", "#", "$", "?", "|", "{", "/", ":", ";",
            "%", "^", "&", "*", "(", ")", "-", "_", "[", "]",
            "}", "<", ">", "~", "+", "=", ",", "."
        ));
    }

    $charsLen = count($chars) - 1;
    shuffle($chars);//打乱数组顺序
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        $str .= $chars[mt_rand(0, $charsLen)];//随机取出一位
    }
    return $str;
}

function upload($data, $date = "20231207")
{
    $md5 = md5(randstr(32));
    $path = "D:\\project\php\\fastadmin\\public\\uploads\\$date";
    if (!file_exists($path)) {
        mkdir($path, "0777", true);
    }
    $file = "D:\\project\php\\fastadmin\\public\\uploads\\$date\\$md5.jpg";
    file_put_contents($file, $data);
    return str_replace("\\", "/", "\\uploads\\$date\\$md5.jpg");
}

function matchAndReplace($str)
{
    preg_match_all("/\"(\\/uploads\\/2023.*?\\.jpg)/i", $str, $match, PREG_PATTERN_ORDER);
//preg_match_all("/(\\/\\/im g.alicdn.com.*?\\.(?:png|jpg|jpeg))/i", $str, $match,PREG_PATTERN_ORDER);
    if (!empty($match[1]) && count($match[1]) > 0) {
        $len = count($match[1]);
        for ($i = 0; $i < $len; $i++) {
            $src = $match[1][$i];
//            $data = file_get_contents($src);
            $path = "https://jackr.cn$src";
//            $path = substr($src, 1) . "/";
            $str = str_replace($src, $path, $str);
        }
    }
    return $str;
}

function query()
{
    $result = AutoCRUD::query_sql("select id,content,thumbs from cs_product");
    foreach ($result as $record) {
        $id = $record["id"];
        $content = $record["content"];
        $thumbs = $record["thumbs"];
        $content = matchAndReplace($content);
        $thumbs = matchAndReplace($thumbs);
        AutoCRUD::update_sql("update cs_product set content = ?,thumbs = ? where id = ?", $content, $thumbs, $id);
    }
}

//query();

function avatar()
{
    $avatar = [
        "https://c-ssl.dtstatic.com/uploads/blog/202201/23/20220123222213_2899a.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202201/23/20220123222213_2899a.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202206/12/20220612164733_72d8b.thumb.400_0.jpg",
        "https://c-ssl.dtstatic.com/uploads/blog/202206/26/20220626195023_f21bc.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202206/26/20220626195023_f21bc.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202208/01/20220801204307_c959a.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202208/01/20220801204308_56ea1.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202208/01/20220801204308_50c7f.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202209/19/20220919130240_8d753.thumb.400_0.jpeg",
        "https://c-ssl.dtstatic.com/uploads/blog/202205/29/20220529075055_668bd.thumb.400_0.jpg",
        "https://c-ssl.dtstatic.com/uploads/blog/202209/19/20220919130240_ed75b.thumb.400_0.jpeg",
        "https://d-ssl.dtstatic.com/uploads/blog/202206/22/20220622180647_b6b80.thumb.400_0.jpg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202206/22/20220622180647_9f65e.thumb.400_0.jpg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202206/22/20220622180647_14eea.thumb.400_0.jpg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202205/20/20220520210603_b10c4.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202208/01/20220801165631_b18ea.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202208/01/20220801165631_4ecaa.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202208/01/20220801165633_6491d.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202208/01/20220801165634_2b11f.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202205/17/20220517141403_c70dc.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202205/20/20220520210602_7c3ba.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202205/20/20220520210605_a9673.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202204/06/20220406192222_fb121.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202203/25/20220325232426_17909.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202209/19/20220919130241_d4baa.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202207/27/20220727105139_1d08e.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202205/17/20220517092313_d707a.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202205/29/20220529075055_e7e70.thumb.400_0.jpg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202204/06/20220406192225_16008.thumb.400_0.jpeg_webp",
        "https://d-ssl.dtstatic.com/uploads/blog/202203/07/20220307084824_51277.thumb.400_0.jpeg_webp",
    ];
    $id = 9;
    $len = count($avatar);
    $i = 0;
    while ($id <= 43) {
        $img = upload(file_get_contents($avatar[$i]));
        AutoCRUD::update_sql("update cs_business set avatar = ? where id = ?", $img, $id);
        $i = floor(++$i % $len);
        $id++;
    }
}

//hotel();
function c()
{
    $content = file_get_contents("hotel.txt");
    $list = explode("\r\n", $content);
    foreach ($list as $url) {
        $url = "https:" . $url;
        echo $url . PHP_EOL;
        $data = file_get_contents($url);
        upload($data, "20231210");
    }
}

function hotelGetUploadPath($name)
{
    return "/uploads/20231210/" . $name;
}

function getSavePath(&$id, $list)
{
    while ($list[$id] == "." || $list[$id] == ".." || $list[$id] == "...") {
        $id = ++$id % count($list);
    }
    $url = hotelGetUploadPath($list[$id]);
    $id = ++$id % count($list);
    return $url;
}

function hotel()
{
    $path = "D:\\project\php\\fastadmin\\public\\uploads\\20231210";
    $list = scandir($path);
    $id = 50;
    $i = 0;
    while ($id <= 79) {
        $img = getSavePath($i, $list);
        AutoCRUD::update_sql("update cs_hotel_room set thumb = ? where id = ?", $img, $id);
        $id++;
    }
}

function update()
{
    $result = AutoCRUD::query_sql("select id,content from cs_product");
    foreach ($result as $record) {
        $id = $record["id"];
        $content = $record["content"];
        $content = matchAndReplace($content);
        AutoCRUD::update_sql("update cs_product set content = ? where id = ?", $content, $id);
    }
}

update();