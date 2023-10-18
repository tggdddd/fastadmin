<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;

/**
 * 课程章节管理
 *
 * @icon fa fa-circle-o
 */
class Chapter extends Backend
{
    protected $model = null;
    protected $modelSceneValidate = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\subject\Chapter;
    }

    public function index()
    {
        $subid = $this->request->param("ids", "", "trim");
        if (empty($subid)) {
            $this->error("错误的访问方式");
        }
        $this->model->where("subid", "=", $subid);
        return parent::index();
    }

    public function add()
    {
        $subid = $this->request->param("subid", "", "trim");
        $this->assign("subid", $subid);
        return parent::add();
    }

    public function edit($ids = null)
    {
        $subid = $this->request->param("subid", "", "trim");
        $this->assign("subid", $subid);
        return parent::edit($ids);
    }
}
