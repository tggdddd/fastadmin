<?php

namespace app\common\model\business;

class AskMessage extends \think\Model
{
    // 表名
    protected $name = 'business_ask_message';

    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;

    // 追加属性
    protected $append = [
    ];

    public function fromUser()
    {
        return $this->belongsTo('app\common\model\business\Business', 'from_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function toUser()
    {
        return $this->belongsTo('app\common\model\business\Business', 'to_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}