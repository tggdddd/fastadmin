<?php

namespace app\admin\controller\business;

use app\common\controller\Backend;
use think\Session;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */
class PublicBusiness extends Backend
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

    public function index()
    {
        $this->model->where('adminid', null);
        return parent::index();
    }


    public function apply($ids = null)
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (empty($ids)) {
            $this->error("错误的请求");
        }
        $row = $this->model->where('adminid', null)->find($ids);
        if (empty($row)) {
            $this->error("未找到该记录");
        }
        $loginId = Session::get('admin')['id'];
        $result = $row->update(["id" => $ids, "adminid" => $loginId]);
        if (empty($result)) {
            $this->error("操作失败，服务器异常");
        }
        $this->success("操作成功");
    }

    public function allocate($ids = null)
    {
        if (!$this->request->isPost()) {
            return $this->fetch();
        }
        $this->request->filter(['strip_tags', 'trim']);
        if (empty($ids)) {
            $this->error("错误的请求");
        }
        $row = $this->model->where('adminid', null)->find($ids);
        if (empty($row)) {
            $this->error("未找到该记录");
        }
        $assignId = $this->request->param("adminid");
        $result = $row->update(["id" => $ids, "adminid" => $assignId]);
        if (empty($result)) {
            $this->error("操作失败，服务器异常");
        }
        $this->success("操作成功");
    }

    public function selectpage()
    {
        return parent::selectpage();
    }
}
