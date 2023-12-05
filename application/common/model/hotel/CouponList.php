<?php

namespace app\common\model\hotel;

use think\Model;

class CouponList extends Model
{
    // 表名
    protected $name = 'hotel_coupon_list';
    protected $append = [];

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function getStatusTextAttr($val, $data)
    {
        return ($this->statusList())[$data["status"]];
    }

    public function statusList()
    {
        return ['0' => '可使用', '1' => '已过期', '2' => '已使用', '3' => '未开始'];
    }

    public function coupon()
    {
        return $this->hasMany("app/common/model/hotel/Coupon", "cid", "id");
    }

    public function business()
    {
        return $this->hasMany("app/common/model/business/Business", "busid", "id");
    }
}
