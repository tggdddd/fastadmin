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
}
