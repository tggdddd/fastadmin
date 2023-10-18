<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class Info extends Backend
{
    protected $model = null;
    protected $modelSceneValidate = true;
    protected $orderModel = null;
    protected $commentModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/subject/Subject');
        $this->orderModel = model("common/subject/Order");
        $this->commentModel = model("common/subject/Comment");
    }

    public function index($ids = null)
    {
        $row = $this->model->find($ids);
        if (empty($row)) {
            $this->error("未找到该课程");
        }
        return $this->fetch();
    }

    public function comment($ids = null)
    {
        $row = $this->model->find($ids);
        if (empty($row)) {
            $this->error("未找到该课程");
        }
        $this->model = $this->commentModel;
        $this->request->filter(['strip_tags', 'trim']);
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $total = $this->model
            ->with(['business'])
            ->where($where)
            ->where('subid', $ids)
            ->count();
        $list = $this->model
            ->with(['business'])
            ->where($where)
            ->where('subid', $ids)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        return ['total' => $total, 'rows' => $list];
    }

    public function order($ids = null)
    {
        $row = $this->model->find($ids);
        if (empty($row)) {
            $this->error("未找到该课程");
        }
        $this->model = $this->orderModel;
        $this->request->filter(['strip_tags', 'trim']);
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $total = $this->model
            ->with(['business'])
            ->where($where)
            ->where('subid', $ids)
            ->count();
        $list = $this->model
            ->with(['business'])
            ->where($where)
            ->where('subid', $ids)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        return ['total' => $total, 'rows' => $list];
    }

    public function comment_add()
    {
        if (false === $this->request->isPost()) {
            $this->assign("subid", $this->request->param("subid"));
            return $this->view->fetch();
        }
        $this->model = $this->commentModel;
        $this->request->filter(['strip_tags', 'trim']);
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        if ($params['createtime']) {
            $params['createtime'] = strtotime($params['createtime']);
        }
        $result = false;
        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
        return parent::add();
    }

    public function comment_edit($ids = null)
    {
        $this->model = $this->commentModel;
        return parent::edit($ids);
    }

    public function comment_del($ids = null)
    {
        $this->model = $this->commentModel;
        return parent::del($ids);
    }

    public function comment_multi($ids = null)
    {
        $this->model = $this->commentModel;
        return parent::multi($ids);
    }
}
