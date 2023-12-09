<?php

namespace app\common\model\hotel;

use think\Model;

class Coupon extends Model
{
    // 表名
    protected $name = 'hotel_coupon';
    protected $append = [
        "thumb_text",
        'createtime_text',
        'endtime_text',
        "status_text",
    ];
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function statusList()
    {
        return ['0' => '活动中', '1' => '已结束'];
    }

    public function getStatusTextAttr($val, $data)
    {
        return ($this->statusList())[$data["status"]];
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        if (empty($data['createtime'])) {
            return "";
        }
        return date('Y-m-d H:i', $data['createtime']);
    }

    public function getEndtimeTextAttr($value, $data)
    {
        if (empty($data['endtime'])) {
            return "";
        }
        return date('Y-m-d H:i', $data['createtime']);
    }
    public function getThumbTextAttr($val, $data)
    {
        if (empty($data['thumb']) || !is_file(ROOT_PATH . 'public' . $data['thumb'])) {
            return cdnurl("/assets/img/avatar.png");
        }
        return cdnurl($data['thumb']);
    }

    public function receives()
    {
        return $this->hasMany("app\common\model\hotel\CouponReceive", "coupon_id", "id");
    }
}
