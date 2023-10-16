<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Loader;
use think\Request;

class Index extends Home
{
    protected $noNeedLogin = ["*"];

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->business_model = model("common/business/Business");
        $this->subject_model = model("common/subject/Subject");
        $this->chapter_model = model("common/subject/Chapter");
        $this->comment_model = model("common/subject/Comment");
        $this->orders_model = model("common/subject/Order");
        $this->record_model = model("common/business/Record");
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

    public function buy()
    {
        $pid = $this->request->param('subid', 0, 'trim');
        if ($this->request->isAjax()) {
            $loginInfo = $this->auth(false);
            if (empty($loginInfo)) {
                $this->error("未登录");
            }
            $subject = $this->subject_model->find($pid);
            if (empty($subject)) {
                $this->error("课程不存在");
            }
            if (!empty($subject->orders()->where("busid", $loginInfo['id'])->find())) {
                $this->error("您已购买该课程");
            }

            $price = $subject->getAttr("price");
            $subjectTile = $subject->getAttr("title");
            if ($price > $loginInfo->getAttr("money")) {
                $this->error("余额不足，请先充值");
                return;
            }
            $loginInfo->startTrans();
            $loginInfo->setAttr("money", $loginInfo->getAttr("money") - $price);
            $order = [
                "busid" => $loginInfo['id'],
                "subid" => $pid,
                "total" => $subject->getAttr("price"),
                "code" => build_code("SUB")
            ];
            $record = [
                "busid" => $loginInfo['id'],
                "total" => $subject->getAttr("price"),
                "content" => "购买了【{$subjectTile}】课程,花费了￥{$price}元"
            ];
            $res[] = $loginInfo->save();
            $res[] = $loginInfo->orders()->save($order);
            $res[] = $loginInfo->records()->save($record);
            foreach ($res as $result) {
                if (empty($result)) {
                    $this->error("操作失败，服务器繁忙");
                }
            }
            $loginInfo->commit();
            $this->assign("redirect", url("home/index/info", ['pid' => $pid]));
            $this->success("购买成功");
        }
        if (empty($pid)) {
            $this->error("非法访问");
        }
        $this->assign("redirect", url("home/index/info", ['pid' => $pid]));
        return $this->view->fetch();
    }

    public function comment_list()
    {
        $pid = $this->request->param("pid", "", "trim");
        if (empty($pid)) {
            $this->error("非法访问");
        }
        if ($this->request->isAjax()) {
            $limit = $this->request->param("limit", "10", "trim");
            $page = $this->request->param("page", "1", "trim");
            $count = $this->comment_model->where("subid", "=", $pid)->count();
            $list = $this->comment_model
                ->where("subid", "=", $pid)
                ->with(["business" => function ($query) {
                    $query->withField(["nickname", "avatar"]);
                }])
                ->order('createtime desc')
                ->page($page, $limit)
                ->select();
            if (empty($list)) {
                $this->error("没有更多数据");
            }
            $this->success("获取成功", null, ["count" => $count, "list" => $list]);
        }
        $this->assign("pid", $pid);
        return $this->fetch();
    }

    public function info()
    {
        if ($this->request->isAjax()) {
            $action = $this->request->param("action", "", "trim");
            switch ($action) {
                case "comment":
                default:
                    $this->error("未知操作");
            }
        }
        $id = $this->request->param("pid", "", "trim");
        $subject = $this->subject_model->with(["comments", "chapters"])->find($id);
        if (empty($id) || empty($subject)) {
            $this->error("该课程不存在", url("home/index/search"));
        }
        $login = $this->auth(false);
        $likeStatus = false;
        if ($login) {
            $likeStatus = in_array($login['id'], explode(",", $subject->getAttr("likes")));
            $order = $subject->orders()->where("busid", $login['id'])->find();
            $this->assign("shouldBuy", empty($order));
        }
        $this->assign("likeStatus", $likeStatus);
        $this->assign("subject", $subject);
        $this->assign("comments", $subject->comments()->limit(8)->select());
        $this->assign("moreComment", $subject->comments()->count() > 8);
        $this->assign("chapters", $subject->chapters);
        return $this->view->fetch();
    }

    public function like()
    {
        $pid = $this->request->param('pid', 0, 'trim');
        $status = $this->request->param('status', '', 'trim');
        if (!in_array($status, ['add', 'remove']) || empty($pid)) {
            $this->error("参数错误");
        }
        $loginInfo = $this->auth(false);
        if (empty($loginInfo)) {
            $this->error("未登录");
        }
        $subject = $this->subject_model->find($pid);
        if (empty($subject)) {
            $this->error("课程不存在");
        }
        $likes = empty($subject->getAttr("likes")) ? [] : explode(",", $subject->getAttr("likes"));
        if (in_array($loginInfo['id'], $likes)) {
            if ($status == 'add') {
                $this->error("已点赞", "", count($likes));
            }
            unset($likes[array_search($loginInfo['id'], $likes)]);
        } else {
            if ($status == 'remove') {
                $this->error("已取消", "", count($likes));
            }
            $likes[] = $loginInfo['id'];
        }
        $likes = array_unique($likes);
        $this->subject_model->isUpdate()->save([
            "id" => $pid,
            "likes" => implode(",", $likes)
        ]);
        $this->success($status == 'add' ? "点赞成功" : "取消点赞", null, count($likes));
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
