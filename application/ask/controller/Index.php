<?php

namespace app\ask\controller;

use app\common\controller\AskController;

/**
 * 公共接口
 */
class Index extends AskController
{
    protected $noNeedLogin = ['*'];

    public function index()
    {
        return '没有首页';
    }

    public function test()
    {
        dump($this->token);
    }

    public function test_success()
    {
        $this->success();
    }

    public function test_error()
    {
        $this->error();
    }

    public function test_delay()
    {
        sleep(1);
        $this->success();
    }
}
