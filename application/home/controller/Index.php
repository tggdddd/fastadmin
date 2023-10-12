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
        $this->business_model = model("common/business/Business");
        $this->subject_model = model("common/subject/Subject");
    }

    public function index()
    {
//        轮播图
        $carousel = $this->subject_model->order("createtime desc")->limit(5)->select();
//        最近课程列表
        $list = $this->subject_model->order("id desc")->limit(8)->select();
        $this->assign([
            'carousel' => $carousel,
            'list' => $list
        ]);
        return $this->view->fetch();
    }

    public function search()
    {
        if ($this->request->isAjax()) {
            $search = $this->request->param("search", "", "trim");
            $limit = $this->request->param("limit", "10", "trim");
            $page = $this->request->param("page", "1", "trim");
            $where = [];
            !empty($search) and $where['title'] = ["like", "%$search%"];
            $count = $this->subject_model->where($where)->count();
            $list = $this->subject_model->where($where)->with("category")->order('createtime desc')->page($page, $limit)->select();
            if (empty($list)) {
                $this->error("没有更多数据");
            }
            $this->success("获取成功", null, ["count" => $count, "list" => $list]);
        }
        return $this->view->fetch();
    }

    public function login()
    {
        if ($this->request->isPost()) {
            $mobile = $this->request->param("mobile");
            $password = $this->request->param("password");
            $data = $this->business_model->where("mobile", "=", $mobile)->find();
            empty($data) and $this->error("用户不存在");
            md5($password . $data['salt']) != $data['password'] and $this->error("密码错误");
            cookie("business", ['mobile' => $mobile, "id" => $data['id']]);
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
            $result = $this->business_model->save($data);
            if ($result) {
                $this->success('注册成功，请登录', '/home/index/login');
            }
            $this->error($this->business_model->getError());
        }
        return $this->view->fetch();
    }
}
