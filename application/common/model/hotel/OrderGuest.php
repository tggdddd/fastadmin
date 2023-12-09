<?php

namespace app\common\model\hotel;

use think\Model;

class OrderGuest extends Model
{
    // 表名
    protected $name = 'hotel_order_guest';
    protected $append = [
        "sex_text"
    ];

    public function getSexTextAttr($val, $data)
    {
        $list = $this->sexList();
        return $list[$data['sex']];
    }

    public function sexList()
    {
        return [
            "0" => "保密",
            "1" => "男",
            "2" => "女"
        ];
    }

    public function getStatusTextAttr($val, $data)
    {
        return ($this->sexList())[$data["sex"]];
    }

    public function business()
    {
        return $this->hasMany("app/common/model/business/Business", "busid", "id");
    }
}
