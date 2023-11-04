<?php

namespace app\common\model\business;

use think\Model;
use traits\model\SoftDelete;


class Order extends Model
{
    const UN_PAID = 0;
    const PAID = 1;
    const SHIPPED = 2;
    const RECEIVED = 3;
    const FINISH = 4;
    const ONLY_REFUND = -1;
    const REFUND_RETURN = -2;
    const PENDING_RETURN = -3;
    const RETURN_SUCCESSFUL = -4;
    const RETURN_FAILED = -5;

    protected $name = 'order';
    protected $append = [
        "status_text"
    ];

    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;
    protected $deleteTime = "deletetime";
    use SoftDelete;

    public function getStatusTextAttr($val, $data)
    {
        if (!isset($data['status'])) {
            return "";
        }
        $status = $this->getStatusList();
        return $status[$data['status']];
    }

    public function getStatusList()
    {
        //todo 多语言
        return [
            self::UN_PAID => __("未支付"),
            self::PAID => __("已支付"),
            self::SHIPPED => __("已发货"),
            self::RECEIVED => __("已收货"),
            self::FINISH => __("已完成"),
            self::ONLY_REFUND => __("仅退款"),
            self::REFUND_RETURN => __("退货退款"),
            self::PENDING_RETURN => __("待退货"),
            self::RETURN_SUCCESSFUL => __("退货成功"),
            self::RETURN_FAILED => __("退货失败")
        ];
    }

    public function business()
    {
        return $this->belongsTo("app\common\model\business\Business", "busid", "id", [], "LEFT")->setEagerlyType(0);
    }

    public function address()
    {
        return $this->belongsTo("app\common\model\business\Address", "businessaddrid", "id", [], "LEFT")->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo("app\common\model\Admin", "adminid", "id", [], "LEFT")->setEagerlyType(0);
    }

    public function checkman()
    {
        return $this->belongsTo("app\common\model\Admin", "checkmanid", "id", [], "LEFT")->setEagerlyType(0);
    }

    public function shipman()
    {
        return $this->belongsTo("app\common\model\Admin", "shipmanid", "id", [], "LEFT")->setEagerlyType(0);
    }

    public function products()
    {
        return $this->hasMany("app\common\model\product\Order", "orderid", "id");
    }
}
