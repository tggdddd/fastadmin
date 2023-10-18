<?php

namespace app\admin\validate\business;

use think\Validate;

class Business extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add' => [],
        'edit' => [],
    ];

}
