<?php

namespace app\ask\controller;

use app\common\controller\AskController;
use app\common\exception\ParamNotFoundException;
use app\common\library\Email;
use think\Config;
use think\Db;

class Business extends AskController
{

    protected $noNeedLogin = ['login', 'bind', 'web', 'userInfo', 'user_info_collection', 'user_info_answer', 'user_info_post', 'user_info', 'star', 'follow'];
    protected $payModel = null;
    protected $postModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->payModel = model('pay.Pay');
        $this->postModel = model("common/post/Post");
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
     * web注册登录
     */
    public function web($password, $mobile)
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
     * 微信登录
     * @param $code string 验证码
     */
    public function login($code)
    {
        //发送请求给微信端
        $wxauth = $this->code2Session($code);
        if (empty($wxauth) || empty($wxauth['openid'])) {
            $this->error('授权失败');
        }
        $openid = $wxauth['openid'];
        // 根据openid查找是否存在用户
        $business = $this->business_model->where(['openid' => $openid])->find();
        if ($business) {
            //授权成功
            unset($business['salt']);
            unset($business['password']);
            $token = randstr() . $business->id;
            $this->token($token, $business);
            $business->setAttr("token", $token);
            $this->success('授权登录成功', [
                "info" => $business
            ]);
        }
        $this->success('授权成功，请绑定账号', [
            "info" => $business,
            "redirect" => "/pages/business/login",
            'action' => 'bind',
            'openid' => $openid
        ]);
    }

    /**
     * 调用微信官方获取用户信息
     * @param $code string
     * @return false|array
     */
    protected function code2Session($code)
    {
        if ($code) {
            $appid = Config::get("wechatApp.appid");
            $appSecret = Config::get("wechatApp.appSecret");
            // 微信官方提供的接口，获取唯一的opendid
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$appSecret&js_code=$code&grant_type=authorization_code";
            $result = httpRequest($url, null, $error);
            if ($error) {
                return false;
            }
            return json_decode($result, true);
        }
        return false;
    }

    /**
     * 绑定微信账号
     */
    public function bind($openid, $password, $mobile)
    {
        if (empty($openid) || empty($password) || empty($mobile)) {
            throw new ParamNotFoundException();
        }
        $business = $this->business_model->where(['mobile' => $mobile])->find();
        //如果找得到就说明绑定过， 如果找不到就说明账号不存在，就注册插入
        if ($business) {
            if (!empty($business['openid'])) {
                $this->error('该用户已绑定，无法重复绑定');
            }
            if (md5($password . $business->salt) !== $business->password) {
                $this->error('密码错误');
            }
            $result = $business->isUpdate()->save([
                'openid' => $openid
            ]);
            if ($result === FALSE) {
                $this->error('绑定账号失败');
            }
            $token = randstr() . $business->id;
            $this->token($token, $business);
            unset($business['salt']);
            unset($business['password']);
            $business->setAttr("token", $token);
            $this->success('绑定账号成功', $business);
        }
        //数据插入
        if (empty($password)) {
            $this->error('密码不能为空');
        }
        //生成一个密码盐
        $salt = randstr();
        $password = md5($password . $salt);
        //组装数据
        $data = [
            'openid' => $openid,
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
        $data['sourceid'] = model('common/Business/Source')
            ->where(['name' => ['LIKE', "%问答社区%"]])->value('id');
        //执行插入 返回自增的条数
        $result = $this->business_model->validate('common/business/Business')->save($data);
        if ($result === FALSE) {
            $this->error($this->business_model->getError());
        }
        //查询出当前插入的数据记录
        $business = $this->business_model->find($this->business_model->id);
        //注册
        unset($business['salt']);
        unset($business['password']);
        $token = randstr() . $business->id;
        $this->token($token, $business);
        $business->setAttr("token", $token);
        $this->success('注册成功', $business);
    }

    /**
     * 解绑微信
     */
    public function unlink()
    {
        $result = $this->user->isUpdate()->save([
            "openid" => null
        ]);
        if (empty($result)) {
            $this->error("解绑错误");
        }
        $this->success("解绑成功");
    }


    /**
     * 主页信息
     */
    public function user_info($busid, $searchValue = "")
    {
//        提问列表  回答列表
        if (empty($busid)) {
            throw new ParamNotFoundException();
        }
//       个人信息  提问数量  回答数量  收藏数量
        $business = $this->business_model->find($busid);
        if (empty($business)) {
            $this->error("用户不存在");
        }
        $userArray = $business->toArray();
        unset($userArray['salt']);
        unset($userArray['password']);
        $result['user'] = $userArray;
//        是否已关注
        if (empty($this->user)) {
            $result['follow'] = false;
        } else {
            $result['follow'] = $this->user->starUser()->where("busid", '=', $business->id)->count() > 0;
        }
        //收藏列表
        $result['ask'] = [
            'count' => $business->askPosts()->where("title|content", "like", "%$searchValue%")->count(),
            'list' => $business->askPosts()->where("title|content", "like", "%$searchValue%")
                ->with(['cate', 'business'])
                ->order(["createtime" => "desc"])
                ->limit(20)
                ->select(),
            'offset' => 20
        ];
        $result['answer'] = [
            'count' => $business->askPostComments()
                ->where(function ($q) use ($searchValue) {
                    $q->where("content", "like", "%$searchValue%")
                ->whereOr("postid", "in", $this->postModel->where("title|content", "like", "%$searchValue%")
                    ->whereOr("busid", "in",
                        $this->business_model
                            ->whereLike("nickname", "%$searchValue%")
                            ->column("id"))->column("id"));
                })
                ->count(),
            'list' => $business->askPostComments()->where(function ($q) use ($searchValue) {
                $q->where("content", "like", "%$searchValue%")
                    ->whereOr("postid", "in", $this->postModel->where("title|content", "like", "%$searchValue%")
                        ->whereOr("busid", "in",
                            $this->business_model
                                ->whereLike("nickname", "%$searchValue%")
                                ->column("id"))->column("id"));
            })
                ->order(["createtime" => "desc"])
                ->limit(20)->select(),
            'offset' => 20
        ];
        $result['collection'] = [
            'count' => $business->askPostCollections()
                ->where("postid", "in", $this->postModel->where("title|content", "like", "%$searchValue%")
                    ->whereOr("busid", "in",
                        $this->business_model
                            ->whereLike("nickname", "%$searchValue%")
                            ->column("id"))->column("id"))
                ->count(),
            'list' => $business
                ->askPostCollections()->where("postid", "in", $this->postModel
                    ->where("title|content", "like", "%$searchValue%")
                    ->whereOr("busid", "in",
                        $this->business_model
                            ->whereLike("nickname", "%$searchValue%")
                            ->column("id"))->column("id"))
                ->order(["createtime" => "desc"])
                ->limit(20)
                ->select(),
            'offset' => 20
        ];
        $post = model("common/post/Post");
        $collectionList = [];
        foreach ($result['collection']['list'] as $item) {
            $collectionList[] = $post->with(['cate', 'business'])
                ->find($item->postid);
        }
        $result['collection']['list'] = $collectionList;
        $this->success("操作成功", $result);
    }

    /** 主页信息 帖子数据 */
    public function user_info_post($busid, $offset = 0, $searchValue = "")
    {
        if (empty($busid)) {
            throw new ParamNotFoundException();
        }
        $model = model("common/post/Post");
        $result["list"] = $model
            ->with(['cate', 'business'])
            ->where("busid", "=", $busid)
            ->where("title|content", "like", "%$searchValue%")
            ->order(["createtime" => "desc"])
            ->limit($offset, 20)
            ->select();
        $result["count"] = $model->where("busid", "=", $busid)
            ->where("title|content", "like", "%$searchValue%")->count();
        $result["offset"] = $offset + 20;
        $this->success("", $result);
    }

    /** 主页信息 评论数据 */
    public function user_info_answer($busid, $offset = 0, $searchValue = "")
    {
        if (empty($busid)) {
            throw new ParamNotFoundException();
        }
        $model = model("common/post/Comment");
        $result["list"] = $model
            ->where("busid", "=", $busid)
            ->where(function ($q) use ($searchValue) {
                $q->where("content", "like", "%$searchValue%")
                    ->whereOr("postid", "in", $this->postModel->where("title|content", "like", "%$searchValue%")
                        ->whereOr("busid", "in",
                            $this->business_model
                                ->whereLike("nickname", "%$searchValue%")
                                ->column("id"))->column("id"));
            })
            ->order(["createtime" => "desc"])
            ->limit($offset, 20)->select();
        $result["count"] = $model->where("busid", "=", $busid)
            ->where(function ($q) use ($searchValue) {
                $q->where("content", "like", "%$searchValue%")
                    ->whereOr("postid", "in", $this->postModel->where("title|content", "like", "%$searchValue%")
                        ->whereOr("busid", "in",
                            $this->business_model
                                ->whereLike("nickname", "%$searchValue%")
                                ->column("id"))->column("id"));
            })
            ->count();
        $result["offset"] = $offset + 20;
        $this->success("", $result);
    }

    /** 主页信息 收藏数据 */
    public function user_info_collection($busid, $offset = 0, $searchValue = "")
    {
        if (empty($busid)) {
            throw new ParamNotFoundException();
        }
        $model = model("common/post/Collect");
        $post = model("common/post/Post");
        $count = $model->where("busid", "=", $busid)
            ->where("postid", "in", $this->postModel->where("title|content", "like", "%$searchValue%")
                ->whereOr("busid", "in",
                    $this->business_model
                        ->whereLike("nickname", "%$searchValue%")
                        ->column("id"))->column("id"))
            ->count();

        $list = $model->where("busid", "=", $busid)
            ->where("postid", "in", $this->postModel->where("title|content", "like", "%$searchValue%")
                ->whereOr("busid", "in",
                    $this->business_model
                        ->whereLike("nickname", "%$searchValue%")
                        ->column("id"))->column("id"))
            ->order(["createtime" => "desc"])
            ->limit($offset, 20)->select();
        $result['collection'] = [
            'count' => $count,
            'list' => $list,
            'offset' => 20
        ];
        $collectionList = [];
        foreach ($result['collection']['list'] as $item) {
            $collectionList[] = $post->with(['cate', 'business'])->find($item->postid);
        }
        $result['list'] = $collectionList;
        $result["count"] = $count;
        $result["offset"] = $offset + 20;
        $this->success("", $result);
    }

    /**
     * 帖子删除
     */
    public function post_del($postid)
    {
        $delete = $this->user->askPosts()->delete($postid);
        if (empty($delete)) {
            $this->error("帖子不存在");
        }
        $this->success("删除成功");
    }

    /**
     * 评论删除
     */
    public function comment_del()
    {
        $id = $this->request->param("comid");
        if (empty($id)) {
            $id = $this->request->param("id");
        }
        if (empty($id)) {
            $this->error("参数错误");
        }

        $delete = $this->user->askPostComments()->delete($id);
        if (empty($delete)) {
            $this->error("评论不存在");
        }
        $this->success("已删除");
    }

    /**
     * 收藏删除
     */
    public function collect_del($postid)
    {
        $delete = $this->user->askPostComments()->delete($postid);
        if (empty($delete)) {
            $this->error("未收藏该帖子");
        }
        $this->success("已取消");
    }

    /**
     * 发送私信
     */
    public function send_letter($busid, $content)
    {
        if (empty($busid) || empty($content)) {
            throw new ParamNotFoundException("内容");
        }
        $result = $this->user->askSendLetter()->save([
            "to_user_id" => $busid,
            "content" => $content
        ]);
        if (empty($result)) {
            $this->error("服务器异常");
        }
        $this->success("已送达");
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
     * 粉丝列表
     */
    public function follow($offset = 0, $busid, $searchValue = "")
    {
        $model = model("common/business/AskFollow");
        $forcus = $model->where("busid", "=", $busid)->column("followee");
        $list = $this->business_model->where("id", "in", $forcus)
            ->whereLike("nickname", "%$searchValue%")
            ->limit($offset, 30)
            ->field("id,mobile,nickname,avatar,gender,sourceid,province,city,district,adminid,createtime,email")
            ->select();
        $count = $this->business_model->where("id", "in", $forcus)->count();
        $this->success("", [
            "list" => $list,
            "count" => $count,
            "offset" => $offset + 30
        ]);
    }

    /**
     * 关注列表
     */
    public function star($offset = 0, $busid, $searchValue = "")
    {
        $model = model("common/business/AskFollow");
        $forcus = $model->where("followee", "=", $busid)->column("busid");
        $list = $this->business_model->where("id", "in", $forcus)
            ->whereLike("nickname", "%$searchValue%")
            ->limit($offset, 30)
            ->field("id,mobile,nickname,avatar,gender,sourceid,province,city,district,adminid,createtime,email")
            ->select();
        $count = $this->business_model->where("id", "in", $forcus)->count();
        $this->success("", [
            "list" => $list,
            "count" => $count,
            "offset" => $offset + 30
        ]);
    }

    /**
     * 私信列表
     */
    public function letter_list($offset = 0, $searchValue = "")
    {
        $list = $this->user
            ->askReceiveLetter()
            ->with("from_user")
            ->where(fn($q) => $q->whereOr("content", "like", "%$searchValue%")
                ->whereOr("from_user_id", "in", $this->business_model
                    ->whereLike("nickname", "%$searchValue%")
                    ->column("id")))
            ->order(["status" => "asc", "createtime" => "desc"])
            ->limit($offset, 30)
            ->select();


        $count = $this->user
            ->askReceiveLetter()
            ->with("from_user")
            ->where(fn($q) => $q->whereOr("content", "like", "%$searchValue%")
                ->whereOr("from_user_id", "in", $this->business_model
                    ->whereLike("nickname", "%$searchValue%")
                    ->column("id")))
            ->count();
        $this->success("", [
            "list" => $list,
            "count" => $count,
            "offset" => $offset + 30
        ]);
    }

    /**
     * 删除私信
     */
    public function letter_del($id)
    {
        $result = $this->user->askReceiveLetter()->delete($id);
        $this->success("删除成功", !empty($result));
    }

    /**
     * 私信已读
     */
    public function letter_remark($id)
    {
        $result = $this->user->askReceiveLetter()->find($id);
        if (empty($result)) {
            $this->error("不存在的私信");
        }
        $result->status = 1;
        $result = $result->isUpdate()->save();
        $this->success("操作成功", !empty($result));
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

    /**
     * 检测订单状态
     */
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
}
