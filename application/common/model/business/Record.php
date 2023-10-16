<?php

namespace app\common\model\business;

use think\Model;

class Record extends Model
{
    protected $name = "business_record";
    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;

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

}
