<?php

namespace app\hotel\controller;

use app\common\controller\AskController;
use app\common\exception\ParamNotFoundException;
use think\Db;

class Index extends AskController
{

    protected $noNeedLogin = ['index', 'coupon_info', 'hotel_detail', 'index_list'];
    protected $payModel = null;
    protected $hotelCouponModel = null;
    protected $hotelCouponReceiveModel = null;
    protected $hotelGuestModel = null;
    protected $hotelOrderModel = null;
    protected $hotelRoomModel = null;

    protected $hotelCollectModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->payModel = model('pay.Pay');
        $this->hotelCouponModel = model("common/hotel/Coupon");
        $this->hotelCouponReceiveModel = model("common/hotel/CouponReceive");
        $this->hotelGuestModel = model("common/hotel/Guest");
        $this->hotelOrderModel = model("common/hotel/Order");
        $this->hotelRoomModel = model("common/hotel/Room");
        $this->hotelCollectModel = model("common/hotel/Collect");
    }

    /**
     * 首页数据
     */
    public function index($filter = "")
    {
//        轮播图
        $result["carousel"] = $this->hotelCouponModel->order("createtime desc")->limit(0, 4)->select();
//        房间信息
        $result["list"] = $this->getRoomList(0, $filter);
        $result['count'] = $this->hotelRoomModel
            ->whereLike("name", "%$filter%")
            ->order("id desc")->count();
        $this->success("", $result);
    }

    private function getRoomList($offset = 0, $filter = "")
    {
        $query = $this->hotelRoomModel
            ->whereLike("name", "%$filter%")
            ->order("id desc")
            ->limit($offset, 30);
        if (!empty($this->user)) {
            $query->with(["collect" => fn($q) => $q->where("busid", "=", $this->user->id)->count()]);
        }
        return $query->select();
    }

    /**
     * 首页分页数据
     */
    public function index_list($offset = 0, $filter = "")
    {
        $result["list"] = $this->getRoomList($offset, $filter);
        $result['count'] = $this->hotelRoomModel
            ->whereLike("name", "%$filter%")->order("id desc")->count();
        $this->success("", $result);
    }

    /**
     * 收藏房间
     */
    public function love($id)
    {
        if (empty($id)) {
            throw new ParamNotFoundException();
        }
        $record = $this->user->hotelCollect()->where("room_id", "=", $id)->find();
        if (empty($record)) {
            $this->user->hotelCollect()->save([
                "room_id" => $id
            ]);
            $this->success("已收藏", 1);
        }
        $record->delete();
        $this->success("已取消", 0);

    }

    /**
     * 获取优惠券信息
     */
    public function coupon_info($id)
    {
        if (empty($id)) {
            $this->error("优惠券不存在");
        }
        $result["coupon"] = $this->hotelCouponModel->find($id);
        if (!empty($this->user)) {
            ($result['coupon'])['receive'] = $this->hotelCouponReceiveModel
                    ->where("busid", "=", $this->user->id)
                    ->where("cid", "=", $id)
                    ->count() > 0;
        } else {
            ($result['coupon'])['receive'] = false;
        }
        $result['receive'] = $this->hotelCouponReceiveModel->with("business")
            ->where("cid", "=", $id)
            ->order("createtime desc")
            ->limit(0, 10)
            ->select();
        $this->success("", $result);
    }

    /**
     * 领取优惠券
     */
    public function coupon_pick($id)
    {
        if (empty($id)) {
            $this->error("优惠券不存在");
        }
        Db::startTrans();
        $coupon = $this->hotelCouponModel->find($id);
        if (empty($coupon)) {
            $this->error("优惠券不存在");
        }
        $record = $this->hotelCouponReceiveModel
                ->where("busid", "=", $this->user->id)
                ->where("cid", "=", $id)
                ->count() > 0;
        if ($record) {
            $this->error("您已领取该优惠券");
        }
        if ($coupon->total <= 0) {
            $this->error("优惠券已领完");
        }
        $record = $this->hotelCouponReceiveModel->save([
            "busid" => $this->user->id,
            "cid" => $id
        ]);
        if (empty($record)) {
            $this->error("服务器异常");
        }
        $result = $coupon->isUpdate()->save(["total" => $coupon->total - 1]);
        if (empty($result)) {
            $this->error("服务器异常");
        }
        Db::commit();
        $this->success("领取成功");
    }

    /**
     * 酒店详情
     */
    public function hotel_detail($id)
    {
        if (empty($id)) {
            throw new ParamNotFoundException();
        }
//        酒店信息
        $record = $this->hotelRoomModel->find($id);
        $used = $this->hotelOrderModel
            ->where("endtime", ">", time())
            ->where("roomid", "=", $id)
            ->count();
        $record->total = $record->total - $used;
        $result["detail"] = $record;
//      优惠券信息
        $result["coupon"] = [];
        if (!empty($this->user)) {
            $result["coupon"] = $this->user
                ->hotelCouponReceive()
                ->with("coupon")
                ->where("status", "0")
                ->select();
        }
//        评价信息
        $result["comment"] = [];
        $this->success("", $result);
    }
}
