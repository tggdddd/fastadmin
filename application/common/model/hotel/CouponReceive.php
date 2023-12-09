<?php

namespace app\common\model\hotel;

use think\Model;

class CouponReceive extends Model
{
    // 表名
    protected $name = 'hotel_coupon_receive';
    protected $append = [
        "status_text",
        'createtime_text'
    ];
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function getCreatetimeTextAttr($value, $data)
    {
        if (empty($data['createtime'])) {
            return "";
        }
        return date('Y-m-d H:i', $data['createtime']);
    }

    public function getStatusTextAttr($val, $data)
    {
        return ($this->statusList())[$data["status"]];
    }

    public function statusList()
    {
        return ['0' => '可使用', '1' => '已使用', '2' => '已过期', '3' => '未开始'];
    }

    public function coupon()
    {
        return $this->hasOne("app\common\model\hotel\Coupon", "id", "cid");
    }

    public function business()
    {
        return $this->hasOne("app\common\model\business\Business", "id", "busid");
    }
}
