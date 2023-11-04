<?php

namespace app\admin\controller\depot;

use app\common\controller\Backend;
use think\Db;

/**
 * 入库管理
 *
 * @icon fa fa-circle-o
 */
class Storage extends Backend
{

    /**
     * Storage模型对象
     * @var \app\common\model\depot\Storage
     */
    protected $model = null;
    protected ?\app\common\model\depot\Storage $storage_model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("\app\common\model\depot\Storage");
        $this->storage_model = $this->model;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['admin', 'reviewer', 'supplier'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {


            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }

        $name = $this->request->param("name", "", "trim");
        $result = $this->storage_model->supplier()->where("id", $name)->count();
        if (empty($result)) {
            $this->error("供应商不存在");
        }
        $remark = $this->request->param("remark", "", "trim");
        $type = $this->request->param("type", "", "trim");
        if (!in_array($type, array_keys($this->storage_model->getTypeList()))) {
            $this->error("入库类型错误");
        }
//        id num price
        $products = $this->request->param("products/a");
        if (!($products && count($products) > 0)) {
            $this->error("商品列表不能为空");
        }
        foreach ($products as $product) {
            if (!(is_numeric($product['num']) && $product['num'] > 0)) {
                $this->error("商品数量必须为大于1的数字");
            }
            if (!is_numeric($product['price'])) {
                $this->error("商品价格必须为数字");
            }
            $result = model("common/product/Product")->where("id", $product['id'])->count();
            if (empty($result)) {
                $this->error("商品不存在");
            }
        }
        Db::startTrans();
//        保存入库单
        $record = $this->storage_model->save([
            "code" => build_code("CS_"),
            "supplierid" => $name,
            "type" => $type,
            "remark" => $remark,
        ]);
        if ($record === false) {
            $this->error($this->storage_model->getError());
        }
        $id = $this->storage_model->getLastInsID();
        $totalAmount = 0;

//        保存入库单的商品表
        foreach ($products as $product) {
            $total = bcmul($product['num'], $product['price']);
            $totalAmount = bcadd($totalAmount, $total);
            $result = $this->storage_model->storageProducts()->save([
                "storageid" => $id,
                "proid" => $product['id'],
                "nums" => $product['num'],
                "price" => $product['price'],
                "total" => $total
            ]);
            if ($result === false) {
                $this->error($this->storage_model->storageProducts()->getError());
            }
        }
        $result = $this->storage_model->isUpdate()->save([
            "amount" => $totalAmount,
            "id" => $id
        ]);
        if ($result === false) {
            $this->error($record->storageProducts()->getError());
        }
        Db::commit();
        $this->success("保存成功");
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->storage_model->with(["storage_products" => fn($query) => $query->with(["product"])])->find($ids);
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
        $name = $this->request->param("name", "", "trim");
        $result = $this->storage_model->supplier()->where("id", $name)->count();
        if (empty($result)) {
            $this->error("供应商不存在");
        }
        $remark = $this->request->param("remark", "", "trim");
        $type = $this->request->param("type", "", "trim");
        if (!in_array($type, array_keys($this->storage_model->getTypeList()))) {
            $this->error("入库类型错误");
        }
//        id num price
        $products = $this->request->param("products/a");
        if (!($products && count($products) > 0)) {
            $this->error("商品列表不能为空");
        }
        foreach ($products as $product) {
            if (!(is_numeric($product['num']) && $product['num'] > 0)) {
                $this->error("商品数量必须为大于1的数字");
            }
            if (!is_numeric($product['price'])) {
                $this->error("商品价格必须为数字");
            }
            $result = model("common/product/Product")->where("id", $product['id'])->count();
            if (empty($result)) {
                $this->error("商品不存在");
            }
        }
        Db::startTrans();
//        删除全部入库商品
        $result = $this->storage_model->storageProducts()->where("storageid", "=", $ids)->delete();
        if ($result == false) {
            $this->error("服务器异常" . $result);
        }
        //  入库单重新赋值
        $record = $this->storage_model->isUpdate()->save([
            "code" => build_code("CS_"),
            "supplierid" => $name,
            "type" => $type,
            "remark" => $remark,
            "id" => $ids
        ]);
        if ($record === false) {
            $this->error($this->storage_model->getError());
        }
        $id = $ids;
        $totalAmount = 0;

//        保存入库单的商品表
        foreach ($products as $product) {
            $total = bcmul($product['num'], $product['price']);
            $totalAmount = bcadd($totalAmount, $total);
            $result = $this->storage_model->storageProducts()->save([
                "storageid" => $id,
                "proid" => $product['id'],
                "nums" => $product['num'],
                "price" => $product['price'],
                "total" => $total
            ]);
            if ($result === false) {
                $this->error($this->storage_model->storageProducts()->getError());
            }
        }
        $result = $this->storage_model->isUpdate()->save([
            "amount" => $totalAmount,
            "id" => $id
        ]);
        if ($result === false) {
            $this->error($record->storageProducts()->getError());
        }
        Db::commit();
        $this->success();
    }

    /**
     * 商品选择
     */
    public function product_select()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = model('common/product/Product')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            $result = array("total" => $list->total(), "rows" => $list->items());
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 审批通过
     */
    public function approval($ids)
    {
        $record = $this->model->find($ids);
        if (empty($record)) {
            $this->error("未找到该订单");
        }
        if ($record->status != 0) {
            $this->error("该订单不可审批");
        }
        $result = $record->isUpdate()->save([
            "status" => 2,
            "reviewerid" => $this->auth->id
        ]);
        if ($result === false) {
            $this->error($record->getError());
        }
        $this->success("审批通过");
    }

    /**
     * 审批不通过
     */
    public function reject($ids)
    {
        $record = $this->model->find($ids);
        if (empty($record)) {
            $this->error("未找到该订单");
        }
        if ($record->status != 0) {
            $this->error("该订单不可审批");
        }
        $result = $record->isUpdate()->save([
            "status" => 1,
            "reviewerid" => $this->auth->id
        ]);
        if ($result === false) {
            $this->error($record->getError());
        }
        $this->success("审批不通过");
    }

    /**
     * 入库
     */
    public function stock($ids)
    {
        $record = $this->model->find($ids);
        if (empty($record)) {
            $this->error("未找到该记录");
        }
        if ($record->status != 2) {
            $this->error("非待入库状态");
        }
        Db::startTrans();
        $result = $record->isUpdate()->save([
            "status" => 3,
            "adminid" => $this->auth->id
        ]);
        if ($result === false) {
            $this->error($record->getError());
        }
        $products = $record->storageProducts()->with(["product"])->select();
        if ($products === false) {
            $this->error($record->storageProducts()->getError());
        }
        foreach ($products as $product) {
            $stock = $product->product->stock;
            $result = $product->product->isUpdate()->save([
                "stock" => bcadd($stock, $product->nums)
            ]);
            if ($result === false) {
                $this->error($product->product->getError());
            }
        }
        Db::commit();
        $this->success("成功入库");
    }

    /**
     * 详情
     */
    public function detail($ids)
    {
        $result = $this->storage_model->with(["supplier", "admin", "reviewer", "storage_products" => fn($q) => $q->with(["product"])])->find($ids);
        if ($result === false) {
            $this->error($this->storage_model->getError());
        }
        $this->assign("row", $result);
        return $this->view->fetch();
    }
}
