<?php

namespace app\common\model\subject;

use think\Model;

class Chapter extends Model
{
    protected $name = "subject_chapter";
    protected $autoWriteTimestamp = 'integer';
    protected $createTime = 'createtime';
    protected $updateTime = false;

    function subject()
    {
        return $this->belongsTo("\app\common\model\subject\Subject", "subid", "id", [], 'LEFT')->setEagerlyType(0);
    }
}
