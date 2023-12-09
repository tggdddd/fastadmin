<?php
//http://v4.crmeb.net/adminapi/product/product/92
function httpRequest($url, $data = null, &$error = false)
{
    $curl = curl_init();
    // 设置请求地址
    curl_setopt($curl, CURLOPT_URL, $url);

    // 设置http某些配置
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

    // 判断传进来的参数是否不为空
    if (!empty($data)) {
        // 设置该请求为POST
        curl_setopt($curl, CURLOPT_POST, 0);
        // 把参数带入请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    // 设置该请求头，
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer eyJhbGciOiJIUzUxMiJ9.eyJsb2dpbl91c2VyX2tleSI6ImJjNDA4OWIwLWI2NDAtNDkzMi04YzhjLWVjN2ZjZDQ5YWYyNyJ9.-v3PinNYKEW2BIX3uiOoU3BrMwnFPeRpY1vFnYkB3X1SjMVthI5gsH4f3AY8XKPHXg5d8JbZuuF_9OEl_GjL9w"
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    return $output;
}


function getData($id)
{
    return httpRequest("http://v4.crmeb.net/adminapi/product/product/$id");
}

function getListId()
{
//    https://demo2.joolun.com/prod-api/goodsspu/page?current=1&size=20&descs=create_time

    $ids = explode(",", file_get_contents("tempid.text"));
    foreach ($ids as $id) {
        $data = json_decode(httpRequest("https://demo2.joolun.com/prod-api/goodsspu/$id"));
        $data = $data->data;
//商品
        $name = $data->name;
//单位
        $unit = "件";
//价格
        $price = $data->marketPrice;
        $oprice = $data->costPrice;
//图片
        $thumbs = implode(",", $data->picUrls);

        $stock = $data->stock;
//描述
        $content = $data->description;

        $result['name'] = $name;
        $result['unit'] = $unit;
        $result['price'] = $price;
        $result['oprice'] = $oprice;
        $result['thumbs'] = $thumbs;
        $result['stock'] = $stock;
        $result['content'] = $content;
        $serializeData = serialize($result);
        file_put_contents("temp2/$id", $serializeData);
    }
////商品
//    $name = $data->store_name;
////单位
//    $unit = $data->unit_name;
////价格
//    $price = $data->price;
//    $oprice = $data->ot_price;
////图片
//    $thumbs = implode(",", $data->slider_image);
//    $image = $data->image;
//    $stock = $data->stock;
////描述
//    $content = $data->description;
//
//    $result['name'] = $name;
//    $result['unit'] = $unit;
//    $result['price'] = $price;
//    $result['oprice'] = $oprice;
//    $result['thumbs'] = $thumbs;
//    $result['image'] = $image;
//    $result['stock'] = $stock;
//    $result['content'] = $content;
//    return serialize($result);
}


function saveData($id)
{
    $data = getData($id);
    $data = json_decode($data);
    if ($data->status == "400533") {
        return false;
    }
    $data = $data->data->productInfo;
//商品
    $name = $data->store_name;
//单位
    $unit = $data->unit_name;
//价格
    $price = $data->price;
    $oprice = $data->ot_price;
//图片
    $thumbs = implode(",", $data->slider_image);
    $image = $data->image;
    $stock = $data->stock;
//描述
    $content = $data->description;

    $result['name'] = $name;
    $result['unit'] = $unit;
    $result['price'] = $price;
    $result['oprice'] = $oprice;
    $result['thumbs'] = $thumbs;
    $result['image'] = $image;
    $result['stock'] = $stock;
    $result['content'] = $content;
    return serialize($result);
}

function main()
{
    @mkdir("temp");
    $i = 1;
    $count = 1;
    while ($count < 500) {
        $data = saveData($i++);
        if ($data == false) {
            echo $i . "无法获取\n";
            continue;
        }
        file_put_contents("temp/$i", saveData($i));
    }
}

getListId();