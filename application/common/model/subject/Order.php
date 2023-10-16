<?php

namespace app\common\model\subject;

use think\Model;

class Order extends Model
{
    protected $name = "subject_order";
    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;
    protected $deleteTime = true;
    protected $append = [
        'createtime_text',
    ];

    public function getCreatetimeTextAttr($value, $data)
    {
        if (empty($data['createtime'])) {
            return "";
        }
        return date('Y-m-d H:i', $data['createtime']);
    }

    function business()
    {
        return $this->belongsTo("app\common\model\business\Business", "busid", "id", [], "LEFT")->setEagerlyType(0);
    }

    public function subject()
    {
        return $this->belongsTo('app\common\model\subject\Subject', 'subid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
