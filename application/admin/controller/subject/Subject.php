<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;
use think\Db;
use think\exception\ValidateException;

/**
 * 课程管理
 *
 * @icon fa fa-circle-o
 */
class Subject extends Backend
{

    /**
     * Subject模型对象
     * @var \app\common\model\subject\Subject
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\subject\Subject();
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->with(["category" => function ($query) {
                return $query->withField(["name"]);
            }])
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function add()
    {
        if (false === $this->request->isPost()) {
            $cate_list = $this->model->category()->column('id,name');
            $this->assign("cate_select", build_select("row[cateid]", $cate_list, [], ['class' => 'selectpicker', 'required' => '']));
            return $this->view->fetch();
        }
        parent::add();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            $cate_list = $this->model->category()->column('id,name');
            $this->assign("cate_select", build_select("row[cateid]", $cate_list, $row['cateid'], ['class' => 'selectpicker', 'required' => '']));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
//        $oldFile = $row['thumbs'];
        try {
            $row->validateFailException()->validate("\\app\\common\\validate\\subject\\Subject");
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
//        if($params['thumbs'] != $oldFile)
//        {
//            //不相等就说明有换图片了，就删除掉旧图片
//            is_file(".".$oldFile) && @unlink(".".$oldFile);
//        }
        $this->success();
    }


}
