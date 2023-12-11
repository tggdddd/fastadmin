<?php

namespace app\common\model\hotel;

use think\Model;

class Collect extends Model
{
    // 表名
    protected $name = 'hotel_collect';
    protected $append = [

    ];

    public function room()
    {
        return $this->hasOne("app\common\model\hotel\Room", "id", "room_id")->setEagerlyType(0);
    }

    public function business()
    {
        return $this->hasOne("app\common\model\business\Business", "busid", "id")->setEagerlyType(0);
    }
}
