<?php

namespace app\common\model\business;

use think\Model;

class Source extends Model
{
    protected $name = "business_source";
    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;
}
