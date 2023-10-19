<?php

namespace app\admin\controller\business;

use app\common\controller\Backend;
use app\common\model\business\Receive;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */
class SelfBusiness extends Backend
{
    protected $dataLimitField = "adminid";
    protected $dataLimit = 'personal';
    protected $modelValidate = true;
    protected $modelSceneValidate = true;
    /**
     * Business模型对象
     * @var \app\common\model\business\Business
     */
    protected $model = null;
    protected $receiveModel = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->receiveModel = new Receive;
        $this->model = new \app\common\model\business\Business;
        $this->view->assign("genderList", ["0" => __("Secret"), "1" => __("Male"), "2" => __("Female")]);
        $this->view->assign("dealList", ["0" => __("No"), "1" => __("Yes")]);
        $this->view->assign("authList", ["0" => __("No"), "1" => __("Yes")]);
        $this->view->assign("sourceList", model("common/business/Source")->select());
    }

    public function index()
    {
        $this->model->where("adminid", "not null");
        return parent::index();
    }

    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $salt = randstr();
            $params["salt"] = $salt;
            $params['password'] = md5($params['password'] . $salt);
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
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $row->validateFailException()->validate(
                    [
                        'mobile' => ['require', 'number', "unique:business,mobile,$ids", 'regex:/(^1[3|4|5|7|8][0-9]{9}$)/'],
                        'email' => ['email', "unique:business,mobile,$ids"],
                        'nickname' => ['require'],
                        'gender' => ['in:0,1,2'],
                    ]);
            }
            if (empty($params['password'])) {
                unset($params['password']);
            } else {
                $salt = randstr();
                $params["salt"] = $salt;
                $params['password'] = md5($params['password'] . $salt);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function reclaim($ids = null)
    {
        if (empty($ids)) {
            $this->error("错误的请求");
        }
        $row = $this->model->find($ids);
        if (empty($row)) {
            $this->error("未找到该记录");
        }
        Db::startTrans();
        $result = $this->model->update([
            "id" => $ids,
            "adminid" => null
        ]);
        if (empty($result) || empty($this->receiveModel->record($this->auth->id, $ids, Receive::RECOVERY))) {
            $this->error("操作异常");
        }
        Db::commit();
        $this->success("操作成功");
    }

    public function detail($ids = null)
    {
        $this->assign("row", $this->model->find($ids));
        return $this->fetch();
    }

    public function business_detail($ids = null)
    {


    }

    public function business_record($ids = null)
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = model("common/business/Record")
            ->where($where)
            ->where("busid", '=', $ids)
//            ->with(['business', 'subject'])
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function selectpage()
    {
        return parent::selectpage();
    }

    public function subject_order($ids = null)
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = model("common/subject/Order")
            ->where($where)
            ->where("busid", '=', $ids)
            ->with(['business', 'subject'])
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
}
