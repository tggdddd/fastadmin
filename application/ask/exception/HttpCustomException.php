<?php

namespace app\ask\exception;

use app\common\exception\ParamNotFoundException;
use Exception;
use think\exception\Handle;
use think\exception\ValidateException;
use think\Request;
use think\Response;

class HttpCustomException extends Handle
{

    public function render(Exception $e)
    {
        $code = 0;
        //参数缺失
        if ($e instanceof ParamNotFoundException) {
            $msg = "参数" . $e->getMessage() . "不存在";
        } else
            // 参数验证错误
            if ($e instanceof ValidateException) {
                $msg = $e->getError();
            } else {
                $code = 500;
                $msg = '服务器内部错误';
            }
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => Request::instance()->server('REQUEST_TIME')
        ];
        $code = $code >= 1000 || $code < 200 ? 200 : $code;
        return Response::create($result, 'json', $code);
    }

}