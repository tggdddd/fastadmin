<?php

namespace app\common\model\hotel;

use think\Model;

class Order extends Model
{
    // 表名
    protected $name = 'admin';

    public function getStatusTextAttr($val, $data)
    {
        return ($this->statusList())[$data["status"]];
    }

    public function statusList()
    {
        return [
            '0' => '未支付',
            '1' => '已支付',
            '2' => '已入住',
            '3' => '已退房',
            '4' => '已评价',
            '-1' => '申请退款',
            '-2' => '审核通过',
            '-3' => '审核不通过'
        ];
    }

    public function coupon()
    {
//        TODO 待测试
        return $this->hasManyThrough("app/common/model/hotel/CouponReceive", "app/common/model/hotel/Coupon", "id", "cid", "coupon_receive_id");
    }
}
