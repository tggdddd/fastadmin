<?php

namespace app\home\controller;

use think\Controller;

class Business extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function profile()
    {
        return $this->fetch();
    }
}
