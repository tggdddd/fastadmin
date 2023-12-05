<?php

namespace app\common\model\hotel;

use think\Model;

class Coupon extends Model
{
    // 表名
    protected $name = 'hotel_coupon';
    protected $append = [
        "thumb_text"
    ];
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function statusList()
    {
        return ['0' => '结束活动', '1' => '正在活动中'];
    }

    public function getThumbTextAttr($val, $data)
    {
        if (empty($data['thumb']) || !is_file(ROOT_PATH . 'public' . $data['thumb'])) {
            return cdnurl("/assets/img/avatar.png");
        }
        return cdnurl($data['thumb']);
    }
}
