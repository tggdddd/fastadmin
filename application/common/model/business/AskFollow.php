<?php

namespace app\common\model\business;

use think\Model;

class AskFollow extends Model
{
    // 表名
    protected $name = 'business_ask_follow';

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

    public function followee()
    {
        return $this->belongsTo('app\common\model\business\Business', 'followee', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
