<?php

namespace app\common\model\business;

use think\Model;

class Business extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;
}
