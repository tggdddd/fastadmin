<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;

/**
 * 课程购买订单管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\subject\Order;

    }


}
