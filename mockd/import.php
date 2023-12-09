<?php
require_once "autoEntity.php";
require_once "imgConvert.php";
$mysql = get_conn($host = "127.0.0.1", $port = "3306", $user = "root", $password = "root", $database = "course");


//insert into cs_product(name,content,thumbs,flag,stock,typeid,unitid,createtime,price);


$result = AutoCRUD::query_sql("select * from cs_product_unit");
echo AutoCRUD::error();
$unitMap = [];
foreach ($result as $item) {
    $unitMap[$item['name']] = $item["id"];
}
$files = scandir("temp2");
foreach ($files as $file) {
    if ($file == "." || $file == "..") {
        continue;
    }
    $file = "temp2" . DIRECTORY_SEPARATOR . $file;
    $data = file_get_contents($file);
    if (empty($data)) {
        continue;
    }
    $data = unserialize($data);
    $unit = $data['unit'];
    if (empty($unitMap[$unit])) {
        AutoCRUD::query_sql("insert into cs_product_unit(name) values ('$unit')");
        $unitMap[$unit] = mysqli_insert_id($mysql);
        echo $unit . " " . $unitMap[$unit];
    }
    $typeid = $unitMap[$unit];
    $data['content'] = matchAndReplace($data['content']);
    $data['thumbs'] = matchAndReplace($data['thumbs']);
    AutoCRUD::insert_sql("insert into cs_product(name,content,thumbs,flag,stock,unitid,createtime,price)
    VALUES (?,?,?,?,?,?,?,?)", $data['name'], $data['content'], $data['thumbs'], "1", $data['stock'], $typeid, time(), $data['price']);
    echo AutoCRUD::error();
}

