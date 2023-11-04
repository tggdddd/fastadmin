<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;

/**
 * 商品管理
 *
 * @icon fa fa-circle-o
 */
class Product extends Backend
{

    /**
     * Product模型对象
     * @var \app\common\model\product\Product
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\product\Product;
        $this->view->assign("flagList", $this->model->getFlagList());
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
            $product = $this->model->where($where);
            $ids = $this->request->param("filterid", "");
            if (!empty($ids)) {
                $product->where("id", "IN", $ids);
            }
            $list = $product
                ->order($sort, $order)
                ->paginate($limit);
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function selectpage()
    {
        return parent::selectpage();
    }
}
