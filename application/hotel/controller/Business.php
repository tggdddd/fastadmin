<?php

namespace app\hotel\controller;

use app\common\controller\AskController;
use app\common\exception\ParamNotFoundException;
use app\common\library\Email;
use think\Db;

class Business extends AskController
{

    protected $noNeedLogin = ['login', 'bind', 'register'];
    protected $payModel = null;
    protected $postModel = null;
    protected $hotelCouponModel = null;
    protected $hotelCouponReceiveModel = null;
    protected $hotelGuestModel = null;
    protected $hotelOrderModel = null;
    protected $hotelRoomModel = null;

    protected $hotelCollectModel = null;
    protected $hotelOrderGuest = null;

    public function __construct()
    {
        parent::__construct();
        $this->payModel = model('pay.Pay');
        $this->postModel = model("common/post/Post");
        $this->hotelCouponModel = model("common/hotel/Coupon");
        $this->hotelCouponReceiveModel = model("common/hotel/CouponReceive");
        $this->hotelGuestModel = model("common/hotel/Guest");
        $this->hotelOrderModel = model("common/hotel/Order");
        $this->hotelRoomModel = model("common/hotel/Room");
        $this->hotelCollectModel = model("common/hotel/Collect");
        $this->hotelOrderGuest = model("common/hotel/OrderGuest");
    }

    /**
     * 修改个人信息
     */
    public function profile()
    {
        //可以一次性接收到全部数据
        $id = $this->request->param('id', 0, 'trim');
        $nickname = $this->request->param('nickname', '', 'trim');
        $mobile = $this->request->param('mobile', '', 'trim');
        $email = $this->request->param('email', '', 'trim');
        $gender = $this->request->param('gender', '0', 'trim');
        $code = $this->request->param('code', '', 'trim');
        $password = $this->request->param('password', '', 'trim');
        if ($this->user->id != $id) {
            $this->error("越权操作");
        }
        // 直接组装数据
        $data = [
            'id' => $id,
            'nickname' => $nickname,
            'mobile' => $mobile,
            'gender' => $gender,
        ];

        //如果密码不为空 修改密码
        if (!empty($password)) {
            //重新生成一份密码盐
            $salt = randstr();
            $data['salt'] = $salt;
            $data['password'] = md5($password . $salt);
        }
        //判断是否修改了邮箱 输入的邮箱 不等于 数据库存入的邮箱
        //如果邮箱改变，需要重新认证
        if ($email != $this->user->email) {
            $data['email'] = $email;
            $data['auth'] = '0';
        }

        //判断是否有地区数据
        if (!empty($code)) {
            //查询省市区的地区码出来
            $parent = model('Region')->where(['code' => $code])->value('parentpath');
            if (!empty($parent)) {
                $arr = explode(',', $parent);
                $data['province'] = isset($arr[0]) ? $arr[0] : null;
                $data['city'] = isset($arr[1]) ? $arr[1] : null;
                $data['district'] = isset($arr[2]) ? $arr[2] : null;
            }
        }
        //判断是否有图片上传
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $success = upload_simple($this->request->file('avatar'));
            //如果上传失败，就提醒
            if (!$success['success']) {
                $this->error($success['error']);
            }
            //如果上传成功
            $data['avatar'] = $success['path'];
        }
        //更新语句 如果是更新语句，需要给data提供一个主键id的值 这就是更新语句 使用验证器的场景
        $result = $this->business_model->validate('common/business/Business.profile')->isUpdate()->save($data);

        if ($result === FALSE) {
            $this->error($this->business_model->getError());
        }
        //判断是否有旧图片，如果有就删除
        if (isset($data['avatar'])) {
            is_file("." . $this->user->avatar) && @unlink("." . $this->user->avatar);
        }
        $business = $this->business_model->find($id);
        unset($business['password']);
        unset($business['salt']);
        $this->success('更新资料成功', $business);
    }

    /**
     * 登录
     */
    public function login($password, $mobile)
    {
        if (empty($password) || empty($mobile)) {
            throw new ParamNotFoundException;
        }
        $business = $this->business_model->where(['mobile' => $mobile])->find();
        //如果找得到就说明绑定过， 如果找不到就说明账号不存在，就注册插入
        if ($business) {
            //验证密码是否正确
            $salt = $business['salt'];
            $password = md5($password . $salt);

            if ($password != $business['password']) {
                $this->error('密码错误');
            } else {
                unset($business['salt']);
                unset($business['password']);
                $token = randstr() . $business->id;
                $this->token($token, $business);
                $business->setAttr("token", $token);
                $this->success('登录成功', $business);
            }
        } else {
            //数据插入
            if (empty($password)) {
                $this->error('密码不能为空');
            }
            //生成一个密码盐
            $salt = randstr();
            //加密密码
            $password = md5($password . $salt);
            //组装数据
            $data = [
                'mobile' => $mobile,
                'nickname' => $mobile,
                'password' => $password,
                'salt' => $salt,
                'gender' => '0', //性别
                'deal' => '0', //成交状态
                'money' => '0', //余额
                'auth' => '0', //实名认证
            ];
            //查询出云课堂的渠道来源的ID信息 数据库查询
            $data['sourceid'] = model('common/business/Source')->where(['name' => ['LIKE', "%问答社区%"]])->value('id');
            //执行插入 返回自增的条数
            $result = $this->business_model->validate('common/business/Business')->save($data);

            if ($result === FALSE) {
                //失败
                $this->error($this->business_model->getError());
            } else {
                //查询出当前插入的数据记录
                $business = $this->business_model->find($this->business_model->id);
                unset($business['salt']);
                unset($business['password']);
                $token = randstr() . $business->id;
                $this->token($token, $business);
                $business->setAttr("token", $token);
                //注册
                $this->success('注册成功', $business);
            }
        }
    }

    /**
     * 发送邮箱验证二维码
     */
    public function email_code($email)
    {
        if (empty($email)) {
            throw new ParamNotFoundException("邮箱");
        }
        $id = model("common/business/Business")->where(["email" => $email])->value("id");
        if (!empty($id) && !empty($this->user->email) && $email != $this->user->email) {
            $this->error("邮箱已绑定");
        }
        $code = randstr(5);
        $model = model("common/ems");
        $data = [
            'email' => $email,
            'code' => $code,
            'event' => "auth"
        ];
        $result = $model->save($data);
        if (!$result) {
            $this->error("验证码生成失败");
        }
        $name = config("site.name");
        $subject = "【{$name}】邮箱验证";
        $content = "验证码：{$code}，如非本人操作，请忽略此邮件。";
        $emailInstance = new Email();
        $sendResult = $emailInstance->to($email)
            ->subject($subject)
            ->message($content)
            ->send();
        if (empty($sendResult)) {
            $this->error($emailInstance->getError());
        }
        $this->success("邮件发送成功");
    }

    /**
     * 邮箱验证
     */
    public function email_valid($email, $code)
    {
        if (empty($email)) {
            throw new ParamNotFoundException("邮箱");
        }
        if (empty($code)) {
            throw new ParamNotFoundException("验证码");
        }
        $model = model("common/Ems");
        $record = $model->where(["email" => $email, "code" => $code, "event" => "auth"])->find();
        if (!$record) {
            $this->error("验证码错误");
        }
        Db::startTrans();
        $result = $this->user->isUpdate()->save(["auth" => 1]);
        if (empty($result)) {
            $this->error("更新账号状态失败");
        }
        $delete = $model->where('id', "=", $record['id'])->delete();
        if (!$delete) {
            $this->error("删除验证码记录失败");
        }
        Db::commit();
        $this->success("验证成功",);
    }

    /**
     * 客户信息列表
     */
    public function guest_list($page = 1)
    {
        $result["list"] = $this->user->hotelGuest()->page($page, 30)->select();
        $result["count"] = $this->user->hotelGuest()->count();
        $result["hasMore"] = $page * 30 < $result["count"];
        $result["page"] = $page + 1;
        $this->success("", $result);
    }

    /**
     * 客户信息列表
     */
    public function guest_add_update($nickname, $mobile, $sex)
    {
        if (empty($nickname) || empty($mobile) || empty($sex)) {
            throw new ParamNotFoundException();
        }
        $id = $this->request->param("id");
        if (empty($id)) {
            //添加
            $result = $this->user->hotelGuest()->save([
                "nickname" => $nickname,
                "mobile" => $mobile,
                "sex" => $sex,
            ]);
            if (empty($result)) {
                $this->error("服务器异常");
            }
            $this->success("添加成功");
        }
        $record = $this->user->hotelGuest()->find($id);
        if (empty($record)) {
            $this->error("不存在的记录");
        }
        $result = $result->isUpdate()->save([
            "nickname" => $nickname,
            "mobile" => $mobile,
            "sex" => $sex,
        ]);
        if (empty($result)) {
            $this->error("服务器异常");
        }
        $this->success("修改成功");
    }

    /**
     * 客户删除
     */
    public function guest_del($id)
    {
        if (empty($id)) {
            throw new ParamNotFoundException();
        }
        $record = $this->user->hotelGuest()->find($id);
        if (empty($record)) {
            $this->error("记录不存在");
        }
        $result = $record->delete();
        if (empty($result)) {
            $this->error("服务器异常");
        }
        $this->success("已删除");
    }

    /**
     * 客户收藏列表
     */
    public function collect_list($page = 1)
    {
        $list = $this->user->hotelCollect()->with(["room"])->page($page, 30)->select();
        $result["list"] = array_map(
            function ($q) {
                $r = $q["room"];
                $r["collect"] = 1;
                return $r;
            }, $list);
        $result["count"] = $this->user->hotelGuest()->count();
        $result["hasMore"] = $page * 30 < $result["count"];
        $result["page"] = $page + 1;
        $this->success("", $result);
    }

    /**
     * 领取的优惠券列表
     */
    public function coupon_list($page = 1, $status = "-1")
    {
        $where = [];
        if (!empty($status) && $status != -1) {
            $where['status'] = $status;
        }
        $result["list"] = $this->user->hotelCouponReceive()
            ->with("coupon")
            ->where($where)
            ->order("createtime desc")
            ->page($page, 30)
            ->select();
        $result["count"] = $this->user->hotelCouponReceive()
            ->where($where)
            ->count();
        $result["hasMore"] = $page * 30 < $result["count"];
        $result["page"] = $page + 1;
        $this->success("", $result);
    }

    /**
     * 酒店订单提交
     */
    public function submit_order($id, $enterTime, $leaveTime, $guest, $couponReceiveId = null)
    {
        if (empty($id) || empty($enterTime) || empty($leaveTime) || empty($guest)) {
            throw new ParamNotFoundException();
        }
        $hotel = $this->hotelRoomModel->find($id);
        if (empty($hotel)) {
            $this->error("房间不存在");
        }
        if ($enterTime - $leaveTime > -84600) {
            $this->error("预定的住房时间不正常");
        }
        if (count($guest) < 1) {
            $this->error("住客信息未添加");
        }
        $used = $this->hotelOrderModel
            ->where("endtime", ">", $enterTime)
            ->where("roomid", "=", $id)
            ->count();
        if ($hotel->total <= $used) {
            $this->error("该房型已全部预约");
        }
        Db::startTrans();
        $rate = 1;
        if (!empty($couponReceiveId)) {
            $receive = $this->user->hotelCouponReceive()->with("coupon")->find($couponReceiveId);
            if (empty($receive)) {
                $this->error("优惠券不合格");
            }
            if ($receive->status != 0) {
                $this->error("优惠券已使用");
            }
            $result = $receive->isUpdate()->save([
                "status" => 1
            ]);
            $rate = $receive->coupon->rate;
            if ($result === false) {
                $this->error("服务器异常1");
            }
        }
        $day = round(($leaveTime - $enterTime) / 84600);
        $oprice = bcmul($hotel->price, $day);
        $price = bcmul($oprice, $rate);
        $money = bcsub($this->user->money, $price);
        if (bcsub($this->user->money, $price) < 0) {
            $this->error("余额不足，请先充值");
        }
        $result = $this->user->isUpdate()->save([
            "money" => $money
        ]);
        if ($result === false) {
            $this->error('服务器异常3');
        }

        $result = $this->hotelOrderModel->save([
            "busid" => $this->user->id,
            "roomid" => $hotel->id,
            "orgin_price" => $oprice,
            "price" => $price,
            "starttime" => $enterTime,
            "endtime" => $leaveTime,
            "status" => 1,
            "coupon_receive_id" => $couponReceiveId
        ]);
        if ($result === false) {
            $this->error("服务器异常2");
        }
        $orderId = $this->hotelOrderModel->id;
        $this->hotelOrderGuest->allowField(true)->saveAll(
            array_map(function ($guestid) use ($orderId) {
                $guestRecord = $this->hotelGuestModel->find($guestid);;
                $rrr["busid"] = $guestRecord->busid;
                $rrr["nickname"] = $guestRecord->nickname;
                $rrr["mobile"] = $guestRecord->mobile;
                $rrr["sex"] = $guestRecord->sex;
                $rrr["orderid"] = $orderId;
                return $rrr;
            }, $guest)
        );
        Db::commit();
        $this->success("订单支付成功", $orderId);

//        $result = $this->hotelOrderModel->save([
//            "busid" => $this->user->id,
//            "roomid" => $hotel->id,
//            "orgin_price" => $oprice,
//            "price" => $price,
//            "starttime" => $enterTime,
//            "endtime" => $leaveTime,
//            "status" => 0,
//            "coupon_receive_id" => $couponReceiveId
//        ]);
//        if ($result === false) {
//            $this->error("服务器异常2");
//        }
//        Db::commit();
//        $this->success("订单创建成功", $this->hotelOrderModel->id);

    }

    /**
     * 酒店订单信息
     */
    public function order_detail($id)
    {
        if (empty($id)) {
            throw new ParamNotFoundException();
        }
        $order = $this->user->hotelOrders()->with(["room", "coupon_receive" => fn($q) => $q->with(["coupon"])])->find($id);
        if (empty($order)) {
            $this->error("订单不存在");
        }
        $order->code = substr(md5($id), 2);
        $this->success("", $order);
    }

    /**
     * 酒店订单信息列表
     */
    public function order_list($page = 1, $status = null)
    {
        $where = [];
        if (!empty($status)) {
            $where['status'] = $status;
        }
        $result["list"] = $this->user
            ->hotelOrders()
            ->with(["room", "guests",
                "coupon_receive" => fn($q) => $q->with(["coupon"])])
            ->where($where)
            ->order("createtime desc")
            ->page($page, 30)
            ->select();
        $result["count"] = $this->user
            ->hotelOrders()
            ->where($where)
            ->count();
        $result["hasMore"] = $page * 30 < $result["count"];
        $result["page"] = $page + 1;
        $this->success("", $result);
    }


    /**
     * 充值
     * @return mixed
     */
    public function recharge()
    {
        $money = $this->request->param('money', "", "trim");
        $payType = $this->request->param('payType', "0", "trim");
        if ($payType == 'wx') {
            $payType = "0";
        }
        if ($payType == 'zfb') {
            $payType = "1";
        }
        if (empty($money) || $money <= 0) {
            $this->error("请输入充值金额");
        }
        $host = trim(config("site.cdnurl"), "/");
        $param = [
            "name" => "充值",
            "third" => json_encode(["busid" => $this->user->id]),
            "originalprice" => $money,
            "paypage" => 0,
            'paytype' => $payType,
            "reurl" => $host . "/business/pay_result",
            "callbackurl" => $host . "/business/callback",
            "wxcode" => $host . config("site.pay.wx"),
            "zfbcode" => $host . config("site.pay.zfb")
        ];
        $result = httpRequest($host . "/pay/index/create", $param, $error);
        if (!empty($error)) {
            $this->error($error);
        }
        if (empty($result) && empty($result["code"])) {
            $this->error(empty($result) ? "服务器异常" : $result["msg"]);
        }
        $result = json_decode($result);
        $this->success("支付订单创建成功", $result->data);
    }

    /**检测订单状态*/
    public function status($payid)
    {
        $host = trim(config("site.cdnurl"), "/");
        $response = httpRequest($host . "/pay/index/status", ["payid" => $payid], $error);
        if (!empty($error)) {
            $this->error($error);
        }
        $result = json_decode($response);
        if ($result->code) {
            $this->success("支付成功", 1);
        }
        if ($result->msg == "订单未支付") {
            $this->success("", 0);
        }
        $this->error($result->msg);
        return;
    }

    /**
     * 支付跳转
     */
    public function pay_result()
    {
        $this->success("支付成功", url("/"));
    }

    /**
     * 支付回调
     */
    public function callback()
    {
        $id = $this->request->param("id");
        $record = model("common/pay/Pay")->find($id);
        if (empty($record)) {
            return "1";
        }
        $busid = $record->third->buisid;
        $paytime = $record->paytime;
        $total = $record->price;
        $recModel = model("common/business/Record");
        $record = $recModel
            ->where("createtime", '=', $paytime)
            ->where("busid", '=', $busid)
            ->find();
        if (!empty($record)) {
            return "2";
        }
        Db::startTrans();
        $record = $recModel->save([
            "busid" => $busid,
            "createtime" => $paytime,
            "content" => "充值金额",
            "total" => $total
        ]);
        if (empty($record)) {
            return "3";
        }
        $business = $this->business_model()
            ->where("busid", "=", $busid)->find();
        $money = bcadd($business->money, $total);
        $result = $business->isUpdate()->save([
            "money" => $money
        ]);
        if (empty($result)) {
            return "4";
        }
        Db::commit();
        return "充值完成";
    }

    /**
     * 获取订单数量
     */

    public function orderNumbers()
    {
        $total = $this->hotelOrderModel
            ->where("busid", "=", $this->user->id)
            ->count();
        $unCommentTotal = $this->hotelOrderModel
            ->where("busid", "=", $this->user->id)
            ->where("status", "<", 4)
            ->where("status", ">", 0)
            ->count();
        $unpaid = $this->hotelOrderModel
            ->where("busid", "=", $this->user->id)
            ->where("status", "=", 0)
            ->count();
        $uncomment = $this->hotelOrderModel
            ->where("busid", "=", $this->user->id)
            ->where("status", "=", 4)
            ->count();

        $result["unPaid"] = $unpaid;
        $result["unComment"] = $unCommentTotal;
        $result["paid"] = $total - $unpaid;
        $this->success("", $result);
    }
}
