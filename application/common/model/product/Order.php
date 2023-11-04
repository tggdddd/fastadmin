<?php

namespace app\common\model\product;

use think\Model;


class Order extends Model
{

    protected $name = 'order_product';
    protected $append = [

    ];

    public function product()
    {
        return $this->belongsTo("app\\common\\model\\product\\Product", "proid", "id");
    }
}
