<?php

namespace app\common\model\depot;

use think\Model;


class BackProduct extends Model
{


    // 表名
    protected $name = 'depot_back_product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        "proid_text"
    ];

    public function product()
    {
        return $this->belongsTo("app\common\model\product\Product", "proid", "id");
    }

    public function getProidTextAttr($val, $data)
    {
        if (empty($data['proid'])) {
            return "";
        }
        $record = model("common/product/Product")
            ->where("id", "=", $data['proid'])->value("name");
        if (empty($record)) {
            return "";
        }
        return $record;
    }

}
