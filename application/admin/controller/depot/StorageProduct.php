<?php

namespace app\admin\controller\depot;

use app\common\controller\Backend;

/**
 * 入库商品关联管理
 *
 * @icon fa fa-circle-o
 */
class StorageProduct extends Backend
{

    /**
     * StorageProduct模型对象
     * @var \app\common\model\depot\StorageProduct
     */
    protected $model = null;
    protected $storage_id = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\depot\StorageProduct;
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        $ids = $this->request->param("ids");
        if (empty($ids)) {
            $this->error("参数错误，未指定入库单");
        }
        $this->storage_id = $ids;
        $this->model->where("storageid", "=", $this->storage_id);
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
}
