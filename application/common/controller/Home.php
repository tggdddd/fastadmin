<?php

namespace app\common\controller;

use think\Controller;
use think\Request;

class Home extends Controller{

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [];
    protected $business_model;
    protected $beforeActionList = [
        "menu_index"
    ];

    protected function menu_index()
    {
        $this->assign("menu_index", $this->request->param("menu_index", "0"));
    }
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->business_model = model("common/business/Business");
        $methodName = $this->request->action();
        if(in_array($methodName,$this->noNeedLogin)||in_array("*",$this->noNeedLogin)){
            return;
        }
        $this->auth();
    }

    protected function auth($redirect = true)
    {
        $cookie = cookie("business");
        $id = trim($cookie['id']??0);
        $mobile = trim($cookie['mobile']??"");
        $business = $this->business_model->where(['id' => $id, "mobile"=>$mobile])->find();
        if(empty($business)){
            cookie("business",null);
            if($redirect){
                $this->error("未登录","home/index/login");
            }
            return null;
        }
        if($redirect){
            $this->assign("business",$business);
        }
        return $business;
    }

}