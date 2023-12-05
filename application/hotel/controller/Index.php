<?php

namespace app\hotel\controller;

use app\common\controller\AskController;
use app\common\exception\ParamNotFoundException;

class Index extends AskController
{

    protected $noNeedLogin = ['index'];
    protected $payModel = null;
    protected $hotelCouponModel = null;
    protected $hotelCouponListModel = null;
    protected $hotelGuestModel = null;
    protected $hotelOrderModel = null;
    protected $hotelRoomModel = null;

    protected $hotelCollectModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->payModel = model('pay.Pay');
        $this->hotelCouponModel = model("common/hotel/Coupon");
        $this->hotelCouponListModel = model("common/hotel/CouponList");
        $this->hotelGuestModel = model("common/hotel/Guest");
        $this->hotelOrderModel = model("common/hotel/Order");
        $this->hotelRoomModel = model("common/hotel/Room");
        $this->hotelCollectModel = model("common/hotel/Collect");
    }

    /**
     * 首页数据
     */
    public function index()
    {
//        轮播图
        $result["carousel"] = $this->hotelRoomModel->order("total desc")->limit(0, 4)->select();
//        房间信息
        $result["list"] = $this->getRoomList();
        $result['count'] = $this->hotelRoomModel->order("id desc")->count();
        $this->success("", $result);
    }

    private function getRoomList($offset = 0)
    {
        $query = $this->hotelRoomModel
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
    public function index_list($offset = 0)
    {
        $result["list"] = $this->getRoomList($offset);
        $result['count'] = $this->hotelRoomModel->order("id desc")->count();
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
}
