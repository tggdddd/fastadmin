<?php

namespace app\common\model\business;

use think\Model;

class Collection extends Model
{
    // 表名
    protected $name = 'business_collection';

    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;

    // 追加属性
    protected $append = [
    ];


    public function business()
    {
        return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function category()
    {
    }

    public function product()
    {
        return $this->belongsTo('app\common\model\product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
