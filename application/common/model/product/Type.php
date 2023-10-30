<?php

namespace app\common\model\product;

use think\Model;


class Type extends Model
{


    // 表名
    protected $name = 'product_type';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function products()
    {
        return $this->hasMany("app\\common\\model\\product\\Product", "typeid", "id");
    }

    public function getThumbAttr($val)
    {
        return cdnurl($val);
    }


}
