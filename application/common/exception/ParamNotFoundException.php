<?php

namespace app\common\exception;

use think\Exception;

class ParamNotFoundException extends Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}
