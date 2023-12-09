<?php

namespace app\common\model\hotel;

use think\Model;

class Order extends Model
{
    // 表名
    protected $name = 'hotel_order';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $append = [
        'createtime_text',
        'endtime_text',
        'starttime_text',
    ];
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

    public function getStarttimeTextAttr($value, $data)
    {
        if (empty($data['starttime'])) {
            return "";
        }
        return date('Y-m-d H:i', $data['starttime']);
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

    public function couponReceive()
    {
        return $this->hasOne("app\common\model\hotel\CouponReceive", "id", "coupon_receive_id");
    }

    public function room()
    {
        return $this->hasOne("app\common\model\hotel\Room", "id", "roomid");
    }

    public function business()
    {
        return $this->hasOne("app\common\model\business\Business", "id", "busid");
    }

    public function guests()
    {
        return $this->hasMany("app\common\model\hotel\OrderGuest", "orderid", "id");
    }
}
