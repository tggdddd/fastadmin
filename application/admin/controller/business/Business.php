<?php

namespace app\admin\controller\business;

use app\common\controller\Backend;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */
class Business extends Backend
{

    /**
     * Business模型对象
     * @var \app\common\model\business\Business
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\business\Business;
        $this->view->assign("genderList", ["0" => __("Secret"), "1" => __("Male"), "2" => __("Female")]);
        $this->view->assign("dealList", ["0" => __("No"), "1" => __("Yes")]);
        $this->view->assign("authList", ["0" => __("No"), "1" => __("Yes")]);
    }

    public function selectpage()
    {
        return parent::selectpage();
    }
}
