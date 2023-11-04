<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Db;

/**
 * 订单管理
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Product模型对象
     * @var \app\common\model\business\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\business\Order;
        $this->request->filter(["trim"]);
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with(['products' => fn($query) => $query->with(['product'])])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            $result = array(
                "total" => $list->total(),
                "rows" => $list->items(),
                "statusList" => $this->model->getStatusList());
            return json($result);
        }
        $this->assign('statusList', $this->model->getStatusList());
        return $this->view->fetch();
    }

    /**
     * 发货
     */
    public function send_product($ids = null)
    {
        $order = $this->model->find($ids);
        if (empty($ids) || empty($order)) {
            $this->error("未找到该订单");
        }
        if (!$this->request->isAjax()) {
            return $this->fetch();
        }
        $params = $this->request->param("row/a");
        if (empty($params["expressid"])) {
            $this->error("参数错误");
        }
        $result = $order->isUpdate()->save([
            "expressid" => $params["expressid"],
            "expresscode" => $params["expresscode"],
            "status" => \app\common\model\business\Order::SHIPPED,
            "shipmanid" => $this->auth->id
        ]);
        if ($result === false) {
            $this->error($order->getError());
        }
        $this->success("发货成功");
    }

    /**
     *审核退货
     */
    public function return_purchase($ids = null)
    {
        $order = model("common/business/Order")->find($ids);
        if (empty($ids) || empty($order)) {
            $this->error("未找到该订单");
        }
        $back = model("common/depot/Back")->with(["back_products"])->where(["ordercode" => $order->code])->find();
        if (empty($back)) {
            $this->error("未找到该退货订单");
        }
        if (!$this->request->isAjax()) {
            $this->assign("data", $back);
            $this->assign("reason", $order->refundreason);
            return $this->fetch();
        }
        $reason = $this->request->param("reason", "", "trim");
        $action = $this->request->param("action", "", "trim");
        if (empty($reason)) {
            $this->error("错误的参数，请输入原因");
        }
        Db::startTrans();
        //同意
        if ($action == 1) {
            $result = $order->isUpdate()->save([
                "status" => \app\common\model\business\Order::PENDING_RETURN,
                "examinereason" => $reason,
                "checkmanid" => $this->auth->id
            ]);
            $result2 = $back->isUpdate()->save([
                "reviewerid" => $this->auth->id,
                "status" => 1
            ]);
        } else
            //拒绝
            if ($action == 2) {
                $result = $order->isUpdate()->save([
                    "status" => \app\common\model\business\Order::RETURN_FAILED,
                    "examinereason" => $reason,
                    "checkmanid" => $this->auth->id
                ]);
                $result2 = $back->isUpdate()->save([
                    "reviewerid" => $this->auth->id,
                    "status" => -1
                ]);
            } else {
                $this->error("错误的参数，请审核是否同意退货");
            }


        if ($result === false || $result2 === false) {
            $this->error($order->getError());
        }
        Db::commit();
        $this->success("已审核");
    }

    /**
     * 接收退货
     */
    public function receive_purchase($ids = null)
    {
        $order = model("common/business/Order")->find($ids);
        if (empty($ids) || empty($order)) {
            $this->error("未找到该订单");
        }
        $back = model("common/depot/Back")->with(["back_products"])->where(["ordercode" => $order->code])->find();
        if (empty($back)) {
            $this->error("未找到该退货订单");
        }
        if (!$this->request->isAjax()) {
            $this->assign("data", $back);
            $this->assign("reason", $order->refundreason);
            return $this->fetch();
        }
        $action = $this->request->param("action", "", "trim");
        Db::startTrans();
        //同意
        if ($action == 1) {
//            退款 修改订单状态
            $result = $order->isUpdate()->save([
                "status" => \app\common\model\business\Order::RETURN_SUCCESSFUL
            ]);
            $user = model("common/business/Business")->find($back->busid);
            if (empty($user)) {
                $this->error("未找到该用户");
            }
            $result3 = $user->isUpdate()->save([
                "money" => bcadd($user->money, $back->amount, 2)
            ]);
//            修改退货单状态
            $result2 = $back->isUpdate()->save(["status" => 2]);
        } else
            //拒绝  修改订单状态 修改退货单状态
            if ($action == 2) {
                $result = $order->isUpdate()->save([
                    "status" => \app\common\model\business\Order::RETURN_FAILED
                ]);
                $result2 = $back->isUpdate()->save(["status" => -1]);
                $result3 = true;
            } else {
                $this->error("请确认是否同意退款");
            }
        if ($result === false || $result2 === false || $result3 === false) {
            $this->error("服务器异常");
        }
        Db::commit();
        $this->success("操作成功");
    }
}
