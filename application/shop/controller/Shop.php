<?php

namespace app\shop\controller;

use app\common\controller\ShopController;
use app\common\library\Email;
use think\Db;
use think\Exception;
use think\Loader;

/**
 * 商城接口
 */
class Shop extends ShopController
{
    protected $noNeedLogin = ['index', 'category', 'category_i', 'login', 'register'];
    protected $product_model;
    protected $product_type_model;

    function _initialize()
    {
        parent::_initialize();
        $this->product_model = \model("common/product/Product");
        $this->product_type_model = \model("common/product/Type");
    }

    /**
     * 首页
     *
     * @ApiTitle    (首页)
     * @ApiParams   (name="page", type="integer", description="页码")
     * @ApiParams   (name="limit", type="integer", description="大小")
     */
    public function index()
    {
        $page = $this->request->param("page", "1", "trim");
        $limit = $this->request->param("limit", "10", "trim");
        if ($page == 1) {
//        轮播
            $carousel = $this->product_model
                ->order("createtime", "desc")
                ->limit(5)
                ->where("status", '=', 1)
                ->select();
//        分类
            $category = $this->product_type_model
                ->order("weigh", "desc")
                ->limit(8)
                ->select();
            $result['carousel'] = $carousel;
            $result['category'] = $category;
        }
//        推荐
        $shops = $this->product_model
            ->order("createtime", "desc")
            ->where("status", '=', 1)
            ->page($page, $limit)
            ->select();
        $shopsCount = $this->product_model->count();
        $shopsMore = $shopsCount > $page * $limit;
        $result['shops'] = $shops;
        $result['shopsCount'] = $shopsCount;
        $result['shopsMore'] = $shopsMore;
        $this->success("请求成功", $result);
    }

    /**
     * 分类
     */
    public function category()
    {
        $recommend = [
            "id" => 0,
            "name" => "为你推荐",
            "products" => $this->product_model
                ->with(['unit', 'type'])
                ->where("status", '=', 1)
                ->order("createtime", "desc")
                ->limit(9)
                ->select()
        ];
        $category = $this->product_type_model
            ->with(['products' => function ($query) {
                $query->where("status", '=', 1)
                    ->order("createtime", "desc");
            }])
            ->order("weigh", "desc")
            ->select();
        array_unshift($category, $recommend);
        $this->success("获取成功", $category);
    }

    /**
     * 分类详情
     */
    public function category_i($id = 0, $page = 1, $limit = 10)
    {
        $shops = $this->product_model
            ->with(['unit', 'type'])
            ->where("status", '=', 1)
            ->order("createtime", "desc")
            ->page($page, $limit)
            ->select();
        $this->success($shops);
    }

    /**
     * 校验登录状态
     * @return void
     *
     */
    public function check_auth()
    {
        $this->auth(true);
        $this->success();
    }

    /**
     * 登录
     */
    public function login()
    {
        $mobile = $this->request->param("mobile");
        $password = $this->request->param("password");
        $data = $this->business_model->where("mobile", "=", $mobile)->find();
        empty($data) and $this->error("用户不存在");
        md5($password . $data['salt']) != $data['password'] and $this->error("密码错误");
        $token = randstr() . $data->getAttr('id');
        $this->token($token, $data);
        $this->success("登录成功", $token);
    }

    /**
     * 注册
     * @return string
     * @throws Exception
     */
    public function register()
    {
        $data = $this->request->param();
        $validate = Loader::validate("app\\common\\validate\\business\\Business");
        if (!$validate->scene("register")->check($data)) {
            $this->error($validate->getError());
        }
        $salt = randstr();
        $data = [
            'mobile' => $data['mobile'],
            'nickname' => $data['mobile'],
            'password' => md5($data['password'] . $salt),
            'salt' => $salt,
            'gender' => '0', //性别
            'deal' => '0', //成交状态
            'money' => '0', //余额
            'auth' => '0', //实名认证，
            'sourceid' => '1', //渠道来源
        ];
        $result = $this->business_model->save($data);
        if ($result) {
            $this->success('注册成功');
        }
        $this->error($this->business_model->getError());
    }

    /**
     * 登出
     */
    public function logout()
    {
        $this->invalid_token();
        $this->success("已登出");
    }

    /**
     * 修改资料
     */
    public function profile()
    {
        if ($this->request->isGet()) {
            $user = $this->user;
            unset($user['password']);
            unset($user['salt']);
            $this->success("获取成功", $user);
        }
        $data = $this->request->param();
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
        if ($data['email'] != $this->user['email']) {
            $data['auth'] = 0;
        }
        $result = $this->business_model->isUpdate()->allowField(true)->save($data);
        if ($result) {
            if (!empty($avatar)) {
                $path = "." . $this->user['avatar'];
                is_file($path) and @unlink($path);
            }
            $this->success("保存成功");
        }
        $this->error("更新失败 " . $this->business_model->getError());
    }

    /**
     * 认证邮箱
     */
    public function auth_email($code = null, $email = null)
    {
//        验证
        if (!empty($code)) {
            $email_r = $this->user['email'];
            if ($email_r != $email) {
                $this->error("邮箱与账号绑定不符");
            }
            $model = model("common/Ems");
            $record = $model->where(["email" => $email, "code" => $code, "event" => "auth"])->find();
            if (!$record) {
                $this->error("验证码错误");
            }
            Db::startTrans();
            $result = $this->business_model
                ->isUpdate()
                ->save([
                    "id" => $this->user['id'],
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
            Db::commit();
            $this->success("验证成功");
        }
//        发送
        $email = $this->user["email"];
        $code = randstr(5);
        $model = model("common/ems");
        $data = [
            'email' => $email,
            'code' => $code,
            'event' => "auth"
        ];
        Db::startTrans();
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
            Db::commit();
            $this->success("邮件发送成功");
        } else {
            $this->error($emailInstance->getError());
        }
    }
}
