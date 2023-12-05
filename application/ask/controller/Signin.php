<?php

namespace app\ask\controller;

use app\common\controller\AskController;
use think\Db;

class Signin extends AskController
{
    protected $model = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('Signin');
    }

    public function index()
    {
//          $busid = $this->request->param('busid', 0, 'trim');
        $busid = $this->user->id;
        $date = $this->request->param('date', date("Y-m"), 'trim');
        //开始时间和结束时间
        $start = date("Y-m-01", strtotime($date));
        $end = date("Y-m-t", strtotime($date));
        $list = $this->model
            ->where(['busid' => $busid])
            ->whereTime('createtime', 'between', [$start, $end])
            ->order('createtime', 'asc')
            ->select();
        if (!$list) {
            $this->error('本月暂无签到记录');
            exit;
        }
        $this->success('成功查询签到记录', $list);
    }

    public function add()
    {

//            $busid = $this->request->param('busid', 0, 'trim');
        $busid = $this->user->id;
        //查询一下今天是否有签到过

        //今天开始和结束
        $start = strtotime(date("Y-m-d") . "00:00:00");
        $end = strtotime(date("Y-m-d") . "23:59:59");
        $check = $this->model->where(['busid' => $busid])->whereTime('createtime', 'today')->find();
        if ($check) {
            $this->error('您今天已签到');
        }
        //签到
        Db::startTrans();
        //插入签到表
        $SignStatus = $this->model->save(['busid' => $busid]);
        if ($SignStatus === FALSE) {
            $this->error($this->model->getError());
        }
        //用户积分
        $point = $this->user->point;
        $point = intval($point) >= 0 ? intval($point) : 0;
        $point++;
        // 组装数据
        $BusData = [
            'id' => $busid,
            'point' => $point
        ];
        //更新用户积分
        $BusStatus = $this->user->isUpdate()->save($BusData);
        if ($BusStatus === FALSE) {
            Db::rollback();
            $this->error($this->user->getError());
        }
        Db::commit();
        $this->success('签到成功');
    }
}
