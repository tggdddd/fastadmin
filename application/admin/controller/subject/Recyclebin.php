<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class Recyclebin extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\subject\Subject;
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            [$where, $sort, $order, $offset, $limit] = $this->buildparams();
            $list = $this->model
                ->onlyTrashed()
                ->where($where)
                ->with(["category" => function ($query) {
                    return $query->withField(["name"]);
                }])
                ->order($sort, $order)
                ->paginate($limit);
            $result = ['total' => $list->total(), 'rows' => $list->items()];
            return json($result);
        }
        return $this->fetch();
    }

    public function destroy($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post('ids');
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        Db::startTrans();
        $files = [];
        try {
            $list = $this->model->onlyTrashed()->select();
            foreach ($list as $item) {
                $temp = $item->delete(true);
                $count += $temp;
                if ($temp) {
                    $files[] = $item['thumbs'];
                }
            }
            Db::commit();
            foreach ($files as $v) {
                is_file("." . $v) && @unlink("." . $v);
            }
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

}