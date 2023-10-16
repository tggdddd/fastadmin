<?php

namespace app\common\model\subject;

use think\Model;

class Comment extends Model
{
    protected $name = "subject_comment";

    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;

    function business()
    {
        return $this->belongsTo("app\common\model\business\Business", "busid", "id", [], "LEFT")->setEagerlyType(0);
    }
}
