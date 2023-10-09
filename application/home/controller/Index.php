<?php
namespace app\home\controller;

use app\common\model\business\Source;
use think\Controller;
use think\Loader;

class Index extends Controller
{

    public function index()
    {
        return $this->view->fetch();
    }
    public function login()
    {
        return $this->view->fetch();
    }
    public function register()
    {
        if($this->request->isPost()){
            $data =  $this->request->param();
            $validate = Loader::validate("Source")->scene("register");
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
        }
        return $this->view->fetch();
    }
}
