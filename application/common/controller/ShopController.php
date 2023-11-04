<?php

namespace app\common\controller;

use think\exception\HttpResponseException;
use think\Lang;
use think\Loader;
use think\Request;
use think\Response;

class ShopController
{

    /*token标识*/
    protected $token_key = "token";
    /*用户模型*/
    protected $business_model = null;
    /*登录用户*/
    protected \app\common\model\business\Business $user;
    /*token*/
    protected $token = null;
    /*免登录列表*/
    protected $noNeedLogin = [];

    function __construct(Request $request = null)
    {
        $this->request = is_null($request) ? Request::instance() : $request;
        $this->_initialize();
        //跨域请求检测
        check_cors_request();
        // 检测IP是否允许
        check_ip_allowed();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        $this->business_model = \model("common/business/Business");
        $this->auth(true);
        $this->loadlang($this->request->controller());
    }

    function _initialize()
    {

    }

    /**
     * token认证
     * @param $must bool 未登录是否返回异常
     * @return false|mixed
     */
    protected function auth($must = false)
    {
        //获取token
        $token = $this->request->param($this->token_key);
        $this->token = $token;
        if (empty($token)) {
            return $this->auth_reduce(false, $must);
        }
        //获取token数据
        $data = cache($token);
        if (empty($data)) {
            return $this->auth_reduce(false, $must);
        }
        //获取用户最新信息
        $user = $this->business_model->find($data['id']);
        if (empty($user)) {
            return $this->auth_reduce(false, $must);
        }
        //更新token
        $this->token($token, $user, 24 * 60 * 60 * 1000);
        return $this->auth_reduce($user, $must);
    }

    /**
     * token方法结果处理
     * @param $result mixed token校验结果
     * @param $must bool 必须登录
     * @return bool 是否已登录
     */
    protected function auth_reduce($result, $must)
    {
        if (!$result) {
            if (in_array($this->request->action(), $this->noNeedLogin) || in_array("*", $this->noNeedLogin)) {
                return false;
            }
            if ($must) {
                $this->error("未登录", "", 401);
            }
        }
        $this->user = $result;
        return true;
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        $name = Loader::parseName($name);
        $name = preg_match("/^([a-zA-Z0-9_\.\/]+)\$/i", $name) ? $name : 'index';
        $lang = $this->request->langset();
        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';
        $path = APP_PATH . $this->request->module() . '/lang/' . $lang . '/' . str_replace('.', '/', $name) . '.php';
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $lang . '/' . str_replace('.', '/', $name) . '.php');
    }
    /**
     * 返回数据（执行失败）
     * @param $msg string 提示信息
     * @param $data mixed 要返回的数据
     * @param $code int   错误码，默认为1  200-1000为HTTP错误码
     * @param $type string   输出类型
     * @param $header array 响应请求头
     */
    protected function error($msg = '', $data = '', $code = 0, $type = '', $header = [])
    {
        $this->result($data, $code, $msg, $type, $header);
    }

    /**
     * 返回数据
     * @param $data mixed 要返回的数据
     * @param $code int   错误码，默认为1  200-1000为HTTP错误码
     * @param $msg string 提示信息
     * @param $type string   输出类型
     * @param $header array 响应请求头
     */
    protected function result($data, $code = 0, $msg = '', $type = '', array $header = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        $code = $code >= 1000 || $code < 200 ? 200 : $code;
        $type = $type ?: ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : 'json');
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 缓存 获取 token
     * @param $token string token 为null则为清理缓存 此时 $data 为 token
     * @param $data null|mixed 缓存内容 为空则获取
     * @param $expired number 过期时间 0为永久
     * @return mixed
     */
    protected function token($token, $data = null, $expired = 0)
    {
        if ($token == null) {
            return cache(null, $data);
        }
        if (empty($data)) {
            return cache($token);
        }
        return cache($token, $data, $expired);
    }

    /**
     * token失效
     */
    protected function invalid_token()
    {
        $this->token(null, $this->token);
    }

    /**
     * 返回数据（执行成功）
     * @param $msg string 提示信息
     * @param $data mixed 要返回的数据
     * @param $code int   错误码，默认为1  200-1000为HTTP错误码
     * @param $type string   输出类型
     * @param $header array   响应请求头
     */
    protected function success($msg = '', $data = '', $code = 1, $type = '', $header = [])
    {
        $this->result($data, $code, $msg, $type, $header);
    }
}