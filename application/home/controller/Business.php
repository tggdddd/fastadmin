<?php

namespace app\home\controller;

use app\common\controller\Home;
use app\common\library\Email;

/**
 * 用户端
 */
class Business extends Home
{
    /**
     * 首页内容
     *
     * @ApiTitle    (首页内容)
     * @ApiSummary  (首页内容)
     * @ApiMethod   (GET)
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取消费记录
     *
     * @ApiTitle    (获取消费记录)
     * @ApiSummary  (获取消费记录)
     * @ApiMethod   (POST)
     * @ApiParams   (name="limit", type="integer", required=false, description="每页大小")
     * @ApiParams   (name="page", type="integer", required=false, description="页码")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function record()
    {
        $loginInfo = $this->auth(false);
        if (empty($loginInfo)) {
            $this->error("未登录");
        }
        if ($this->request->isAjax()) {
            $limit = $this->request->param("limit", "10", "trim");
            $page = $this->request->param("page", "1", "trim");
            $count = $loginInfo->records()->count();
            $list = $loginInfo->records()->order('createtime desc')->page($page, $limit)->select();
            if (empty($list)) {
                $this->error("没有更多数据");
            }
            $this->success("获取成功", null, ["count" => $count, "list" => $list]);
        }
        return $this->view->fetch();
    }

    /**
     * 订单
     * @return string
     * @throws \think\Exception
     */
    public function order()
    {
        $loginInfo = $this->auth(false);
        if (empty($loginInfo)) {
            $this->error("未登录");
        }
        if ($this->request->isAjax()) {
            $limit = $this->request->param("limit", "10", "trim");
            $page = $this->request->param("page", "1", "trim");
            $count = $loginInfo->orders()->count();
            $list = $loginInfo->orders()->with("subject")->order('createtime desc')->page($page, $limit)->select();
            if (empty($list)) {
                $this->error("没有更多数据");
            }
            foreach ($list as $k => $v) {
                $v->setAttr("comment", $v->subject->comments()->where("busid", "=", $loginInfo["id"])->count());
            }
            $this->success("获取成功", null, ["count" => $count, "list" => $list]);
        }
        return $this->view->fetch();
    }

    /**
     * 个人信息
     * @return mixed
     */
    public function profile()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param('', "", "trim");
            $data['id'] = $this->view->business['id'];
            $validate = validate("app\\common\\validate\\business\\Business");
            if (!$validate->scene("profile")->check($data)) {
                $this->error($validate->getError());
            }
            $avatar = $this->request->file('avatar');
            if (!empty($avatar)) {
                $result = upload_simple($avatar);
                if (!$result['success']) {
                    $this->error($result['msg']);
                }
                $data['avatar'] = $result['path'];
            }
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $salt = randstr();
                $data['password'] = md5($data['password'] . $salt);
                $data['salt'] = $salt;
            }
            if (empty($data['city'])) {
                unset($data['city']);
            }
            if (empty($data['province'])) {
                unset($data['province']);
            }
            if (empty($data['district'])) {
                unset($data['district']);
            }
            if ($data['email'] != $this->view->business['email']) {
                $data['auth'] = 0;
            }
            $result = $this->business_model->isUpdate()->allowField(true)->save($data);
            if ($result) {
                if (!empty($avatar)) {
                    $path = "." . $this->view->business->avatar;
                    is_file($path) and @unlink($path);
                }
                cookie("business", ['mobile' => $data['mobile'], "id" => $data['id']]);
                $this->success("保存成功", url("business/index"));
            }
            $this->error("更新失败 " . $this->business_model->getError());
        }
        return $this->fetch();
    }

    /**
     * 充值
     * @return mixed
     */
    public function recharge()
    {
        $loginInfo = $this->auth(false);
        if (empty($loginInfo)) {
            $this->error("未登录");
        }
        if ($this->request->isPost()) {
            $money = $this->request->param('money', "", "trim");
            if (empty($money) || $money <= 0) {
                $this->error("错误的参数");
            }
            $host = trim(config("site.cdnurl"), "/");
            $param = [
                "name" => "充值",
                "third" => json_encode(["busid" => $loginInfo->id]),
                "originalprice" => $money,
                "paypage" => 1,
                "reurl" => $host . "/home/business/pay_result",
                "callbackurl" => $host . "/home/business/callback",
                "wxcode" => config("site.pay.wx"),
                "zfbcode" => config("site.pay.zfb")];
            $result = httpRequest($host . "/pay/index/create", $param, $error);
            if (!empty($error)) {
                $this->error($error);
            }
            if (empty($result) && empty($result["code"])) {
                $this->error(empty($result) ? "服务器异常" : $result["msg"]);
            }
            return $result;
        }
        return $this->fetch();
    }

    /**
     * 支付跳转
     */
    public function pay_result()
    {
        $this->success("支付成功", url("/home/business/recharge"));
    }

    /**
     * 支付回调
     */
    public function callback()
    {

    }
    /**
     * 留言
     * @return mixed
     */
    public function contact()
    {
        return $this->fetch();
    }

    /**
     * 验证邮箱
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function email()
    {
        if ($this->request->isAjax()) {
            $action = $this->request->param("action");
            if ($action == "auth") {
                empty(($email = $this->view->business['email'])) and $this->error("邮箱获取失败");
                $code = randstr(5);
                $model = model("common/ems");
                $data = [
                    'email' => $email,
                    'code' => $code,
                    'event' => $action
                ];
                $model->startTrans();
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
                if ($sendResult) {
                    $model->commit();
                    $this->success("邮件发送成功");
                } else {
                    $this->error($emailInstance->getError());
                }
            }
        }
        if ($this->request->isPost()) {
            $email = $this->view->business['email'];
            $code = $this->request->param("code", "", "trim");
            if (empty($email) || empty($code)) {
                $this->error("邮箱验证码不能为空");
            }
            $model = model("common/Ems");
            $record = $model->where(["email" => $email, "code" => $code, "event" => "auth"])->find();
            if (!$record) {
                $this->error("验证码错误");
            }
            $model->startTrans();
            $this->business_model->startTrans();
            $result = $this->business_model
                ->isUpdate()
                ->save([
                    "id" => $this->view->business['id'],
                    "auth" => 1
                ]);
            if (!$result) {
                $this->error("更新账号状态失败");
            }
            $delete = $model->where('id', "=", $record['id'])->delete();
            if (!$delete) {
                $this->error("删除验证码记录失败");
            }
            $this->business_model->commit();
            $model->commit();
            $this->success("验证成功", "home/business/index");
        }
        return $this->fetch();
    }

    /**
     * 评论
     * @return string
     * @throws \think\Exception
     */
    public function comment()
    {
        $loginInfo = $this->auth(false);
        if (empty($loginInfo)) {
            $this->error("未登录");
        }
        $pid = $this->request->param("pid", "", "trim");
        $order = $loginInfo->orders()->find($pid);
        if (empty($order)) {
            $this->error("订单不存在");
        }
        $subject = $order->subject;
        if (empty($subject)) {
            $this->error("未找到该课程");
        }
        if ($this->request->isPost()) {
            $content = $this->request->param("content", "", "trim");
            if (empty($content) || strlen($content) > 200) {
                $this->error("内容不能为空且不能超过200字");
            }
            $loginInfo->comment()->save([
                "subid" => $order->getAttr("subid"),
                "content" => $content
            ]);
            if (empty($subject)) {
                $this->error("评论失败，服务器繁忙");
            }
            $this->success("评论成功", "home/business/order");
        }
        $this->assign("subject", $subject);
        $this->assign("pid", $pid);
        return $this->view->fetch();
    }

}
