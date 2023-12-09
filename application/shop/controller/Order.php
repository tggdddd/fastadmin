<?php

namespace app\shop\controller;

use app\common\controller\ShopController;
use think\Db;

class Order extends ShopController
{
    protected $noNeedLogin = [];
    protected ?\app\common\model\product\Product $product_model = null;
    protected ?\app\common\model\product\Type $product_type_model = null;
    protected ?\app\common\model\business\Address $address_model = null;
    protected ?\app\common\model\business\Cart $cart_model = null;

    function _initialize()
    {
        parent::_initialize();
        $this->product_model = model("common/product/Product");
        $this->product_type_model = model("common/product/Type");
        $this->address_model = model("common/business/Address");
        $this->cart_model = model("common/business/Cart");
    }

    /**
     * 订单列表
     */
    public function order_list($type, $page = 1, $size = 10)
    {
        $records = $this->user->product_orders()
            ->with(['products' => fn($query) => $query->with(['product'])])
            ->order("createtime", "desc")
            ->page($page, $size);
        if (!in_array($type, ['0', '1', '2', '3', '4', '-1', '-2', '-3', '-4', '-5', '5'])) {
            $this->error("错误的类型参数");
        }
        if ($type != 5) {
            $records->where("status", "=", $type);
        }
        $records = $records->select();
        if ($records === false) {
            $this->error("服务器异常");
        }
        $this->success("", $records);
    }

    /**
     * 提交订单
     */
    public function submit_order($data)
    {
        Db::startTrans();
//        生成订单
        $orderRecord = $this->user->product_orders()->save([
            "code" => build_code("CS"),
            "businessaddrid" => $this->user->product_address()->where('status', '=', '1')->value("id"),
            "status" => \app\common\model\business\Order::UN_PAID,
            "adminid" => $this->user->adminid
        ]);
        if (empty($orderRecord)) {
            $this->error("服务器异常");
        }
        $totalPrice = 0;
//        查询购物车
        foreach ($data as $item) {
            $record = $this->cart_model->with(['product'])->find($item['id']);
//            请求异常 不存在的购物车
            if (empty($record)) {
                $this->error("服务器异常");
            }
//            商品未上架
            if ($record->product->status == 0) {
                $this->error($record->product->name . "未上架");
            }
//            库存不足
            if ($record->product->stock < $item['num']) {
                $this->error($record->product->name . "库存不足,目前库存为" . $record->product->stock);
            }
//          更新商品库存
            $record->product->isUpdate()->save(["stock" => $record->product->stock - $item['num']]);
//            保存订单商品
            $item_total_amount = bcmul($record->product->price, $item['num'], 2);
            $result = $orderRecord->products()->save([
                "proid" => $record->proid,
                "pronum" => $item['num'],
                "price" => $record->product->price,
                "total" => $item_total_amount
            ]);
            if (false === $result) {
                $this->error("服务器异常");
            }
            $totalPrice = bcadd($item_total_amount, $totalPrice, 2);
        }
//        更新订单总金额
        $result = $orderRecord->isUpdate()->save(["amount" => $totalPrice]);
        if (empty($result)) {
            $this->error("服务器异常");
        }
//        删除购物车
        $result = $this->user->cart()->delete(array_map(fn($t) => $t['id'], $data));
        if (false === $result) {
            $this->error("服务器异常");
        }
        Db::commit();
        $this->success("订单生成", $orderRecord->id);
    }

    /**
     * 获取订单详情
     * @param $id string 订单id
     */
    public function order_detail($id)
    {
        $model = model("app\common\model\business\Order");
        $record = $model
            ->with(['products' => fn($query) => $query->with(["product"]), 'address'])
            ->where(["order.busid" => $this->user->id])
            ->find($id);
        if (empty($record)) {
            $this->error($model->getError());
        }
        $this->success("", $record);
    }

    /**
     * 修改订单的收获地址
     */
    public function order_address_change($addressId, $orderId)
    {
        $record = $this->user->product_orders()->find($orderId);
        if (empty($record)) {
            $this->error("未找到该订单");
        }
        $result = $record->isUpdate()->save([
            "businessaddrid" => $addressId
        ]);
        $this->success();
    }

    /**
     * 取消订单
     */
    public function cancel_order($id)
    {
        $model = model("app\common\model\business\Order");
        $record = $model
            ->with(['products' => fn($query) => $query->with(["product"])])
            ->where(["busid" => $this->user->id])
            ->find($id);
        if (empty($record)) {
            $this->error("未找到该订单");
        }
        if ($record->status != 0) {
            $this->error("已支付订单不可取消");
        }
        Db::startTrans();
//      库存回归
        foreach ($record->products as $product) {
            $result = $product->product->isUpdate()->save(["stock" => $product->product->stock + $product->pronum]);
            if ($result === false) {
                $this->error("服务器异常");
            }
//            删除对应的商品订单
            $result = $product->delete();
            if ($result === false) {
                $this->error("服务器异常");
            }
        }
//      删除订单
        $result = $record->delete();
        if ($result === false) {
            $this->error("服务器异常");
        }
        Db::commit();
        $this->success("操作成功");
    }

    /**
     * 支付
     */
    public function pay_order($id)
    {
        $model = model("app\common\model\business\Order");
        $record = $model
            ->with(['products' => fn($query) => $query->with(["product"])])
            ->where(["busid" => $this->user->id])
            ->find($id);
        if (empty($record)) {
            $this->error("未找到该订单");
        }
        if ($record->status != \app\common\model\business\Order::UN_PAID) {
            $this->error("非待支付订单");
        }
        $money = bcsub($this->user->money, $record->amount);
        if ($money < 0) {
            $this->error("账号余额不足，请先充值");
        }
        Db::startTrans();
        $result = $this->user->isUpdate()->save([
            "money" => $money
        ]);
        if ($result === false) {
            $this->error("服务器异常");
        }
        $result = $record->isUpdate()->save([
            "status" => \app\common\model\business\Order::PAID
        ]);
        if ($result === false) {
            $this->error("服务器异常");
        }
        //        添加消费记录
        $this->user->records()->save([
            "total" => $record->amount,
            "content" => "购买了" . trim(array_reduce(
                    array_map(fn($pr) => "【" . $pr->product->name . "】", $record->products),
                    fn($a, $b) => $a . "," . $b), ",")
        ]);
        if ($result === false) {
            $this->error("服务器异常");
        }
        Db::commit();
        $this->success("支付成功");
    }

    /**
     * 申请退款
     */
    public function refund_order($id)
    {
        $model = model("app\common\model\business\Order");
        $record = $model
            ->with(['products' => fn($query) => $query->with(["product"])])
            ->where(["busid" => $this->user->id])
            ->find($id);
        if (empty($record)) {
            $this->error("未找到该订单");
        }
        if (!in_array($record->status, [1, 2, 3, 4])) {
            $this->error("非可退款订单");
        }
//        未发货 直接退款
        if ($record->status == \app\common\model\business\Order::PAID) {
            $money = bcadd($this->user->money, $record->amount);
            Db::startTrans();
//            金钱回退
            $result = $this->user->isUpdate()->save([
                "money" => $money
            ]);
            if ($result === false) {
                $this->error("服务器异常");
            }
//            库存加回
            foreach ($record->products as $product) {
                $result = $product->product->isUpdate()->save(["stock" => $product->product->stock + $product->pronum]);
                if ($result === false) {
                    $this->error("服务器异常");
                }
            }
//            修改订单状态为仅退款
            $result = $record->isUpdate()->save([
                "status" => \app\common\model\business\Order::ONLY_REFUND
            ]);
            if ($result === false) {
                $this->error("服务器异常");
            }
            Db::commit();
            $this->success("退款成功");
        }
        $this->error("已发货订单暂不支持退款");
    }

    /**
     * 查询你物流
     */
    public function query_express($id)
    {
        $model = model("app\common\model\business\Order");
        $record = $model
            ->with(['products' => fn($query) => $query->with(["product"])])
            ->where(["busid" => $this->user->id])
            ->find($id);
        if (empty($record)) {
            $this->error("未找到该订单");
        }
        if (empty($record->expressid)) {
            $this->error("暂无物流信息");
        }
        $result = query_express($record->expresscode, true);
        if ($result === false) {
            $this->error("物流信息查询失败");
        }
        $this->success("", $result);
    }

    /**
     * 申请退款退款
     */
    public function return_submit($id)
    {
        $products = $this->request->param("products");
        $files = $this->request->file("files");
        $reason = $this->request->param("reason");
        if (empty($products)) {
            $this->error("参数错误");
        }
        $products = explode(",", $products);
        if (count($products) == 0 || (count($products) & 1) === 1) {
            $this->error("参数错误");
        }
        $model = model("app\common\model\business\Order");
        $order = $model
            ->with(['products' => fn($query) => $query->with(["product"])])
            ->where(["busid" => $this->user->id])
            ->find($id);
        if (empty($order)) {
            $this->error("未找到该订单");
        }
        if (!in_array($order->status, [2, 3, 4])) {
            $this->error("非可退货订单");
        }
        Db::startTrans();
//        创建退货单
        $back_model = model("common/depot/Back");
        $result = $back_model->save([
            "code" => build_code("CS"),
            "ordercode" => $order->code,
            "busid" => $this->user->id,
            "status" => 0,
            "adminid" => $this->user->adminid
        ]);
        $back_id = $back_model->getLastInsID();
//        amount
        if (empty($result)) {
            $this->error("服务器异常");
        }
        $totalPrice = 0;
//        查询订单商品
        for ($i = 0; $i < count($products); $i += 2) {
            $product[] = $products[$i];
            $product[] = $products[$i + 1];
            $flag = true;
//            需要为订单内商品
            foreach ($order->products as $item) {
                if ($item->id == $product[0]) {
//                    数量不能多于已购数量
                    if ($item->pronum < $product[1]) {
                        $this->error("错误的参数");
                    }
//                   保存退货订单商品
                    $item_total_amount = bcmul($item->product->price, $product[1], 2);
                    $result = $back_model->backProducts()->save([
                        "backid" => $back_id,
                        "proid" => $item->product->id,
                        "nums" => $product[1],
                        "price" => $item->product->price,
                        "total" => $item_total_amount
                    ]);
                    if (false === $result) {
                        $this->error("服务器异常");
                    }
                    $totalPrice = bcadd($item_total_amount, $totalPrice, 2);
                    $flag = false;
                    break;
                }
            }
            $flag and $this->error("错误的参数");
        }
//        更新退货订单总金额
        if (!empty($files) && count($files)) {
            foreach ($files as $file) {
                $result = upload_simple($file);
                if (empty($result)) {
                    $this->error("服务器异常");
                }
                $uploadFiles[] = $result['path'];
            }
        }
        $thumbs = "";
        if (isset($uploadFiles)) {
            $thumbs = trim(implode(",", $uploadFiles), ",");
        }
        $result = $back_model->isUpdate()->save([
            "id" => $back_id,
            "amount" => $totalPrice,
            "thumbs" => $thumbs
        ]);
        if (empty($result)) {
            $this->error("服务器异常");
        }
// 更新订单状态
        $result = $order->isUpdate()->save([
            "refundreason" => $reason,
            "status" => \app\common\model\business\Order::REFUND_RETURN
        ]);
        if (false === $result) {
            $this->error("服务器异常");
        }
        Db::commit();
        $this->success("申请已提交");
    }

    /**
     * 查询退货订单
     */
    public function return_order($code)
    {
        $model = model("app\common\model\depot\Back");
        $record = $model
            ->alias("back")
            ->with(['back_products' => fn($query) => $query->with(["product"])])
            ->where(["back.busid" => $this->user->id])
            ->where(["back.ordercode" => $code])
            ->find();
        $this->success("", $record);
    }

    /**
     * 提交退货物流单
     */
    public function return_express($id, $code)
    {
        if (empty($id) || empty($code)) {
            $this->error("参数错误");
        }
        $record = model("app\common\model\depot\Back")
            ->where("busid", "=", $this->user->id)
            ->find($id);
        if (empty($record)) {
            $this->error("服务器异常");
        }
        $result = $record->isUpdate()->save([
            "expresscode" => $code
        ]);
        if ($result === false) {
            $this->error("服务器异常");
        }
        $this->success("已提交");
    }

    /**
     * 确认收货
     */
    public function confirm_receive($id)
    {
        if (empty($id)) {
            $this->error("参数错误");
        }
        $model = model("app\common\model\business\Order");
        $order = $model->where(["busid" => $this->user->id])->find($id);
        if (empty($order)) {
            $this->error("未找到该订单");
        }
        if (!in_array($order->status, [2, 1, -3, -5])) {
            $this->error("订单状态异常");
        }
//        修改订单状态
        $result = $order->isUpdate()->save([
            "status" => 3
        ]);
        if ($result === false) {
            $this->error("服务器异常");
        }
        $this->success("操作成功");
    }

    /**
     * 评价订单
     */
    public function comment_order($id)
    {
        if (empty($id)) {
            $this->error("参数错误");
        }
        $model = model("app\common\model\business\Order");
        $order = $model->where(["busid" => $this->user->id])->find($id);
        if (empty($order)) {
            $this->error("未找到该订单");
        }
        if ($order->status != 3) {
            $this->error("订单状态异常");
        }
//        修改订单状态
        $result = $order->isUpdate()->save([
            "status" => 4
        ]);
        //todo 保存评价
        if ($result === false) {
            $this->error("服务器异常");
        }
        $this->success("评价成功");
    }

    /**
     * 消费记录
     */
    public function consume_record()
    {
        $size = $this->request->param("size", "10", "trim");
        $page = $this->request->param("page", "1", "trim");
        $count = $this->user->records()->count();
        $list = $this->user->records()->order('createtime desc')->page($page, $size)->select();
        if (empty($list)) {
            $this->error("没有更多数据");
        }
        $this->success("获取成功", ["more" => $page * $size < $count, "list" => $list]);
    }
}