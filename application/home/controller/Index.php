<?php

namespace app\home\controller;

use think\Controller;
use think\Loader;
use think\Request;

class Index extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model("common/business/Business");
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function login()
    {
        if ($this->request->isPost()) {
            $mobile = $this->request->param("mobile");
            $password = $this->request->param("password");
            $data = $this->model->where("mobile", "=", $mobile)->find();
            empty($data) and $this->error("用户不存在");
            md5($password . $data['salt']) != $data['password'] and $this->error("密码错误");
            $this->success("登录成功", url("home/index/index"));
        }
        return $this->view->fetch();
    }

    public function register()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = Loader::validate("Business");
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
                'auth' => '0', //实名认证
            ];
            //查询出云课堂的渠道来源的ID信息
            $data['sourceid'] = model('common/Business/Source')->where(['name' => ['LIKE', "%云课堂%"]])->value('id');
            $result = $this->model->save($data);
            if ($result) {
                $this->success('注册成功，请登录', '/home/index/login');
            }
            $this->error($this->model->getError());
        }
        return $this->view->fetch();
    }
}
