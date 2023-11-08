<?php

namespace addons\wechat\controller;

use addons\wechat\library\Config;
use addons\wechat\model\WechatAutoreply;
use addons\wechat\model\WechatCaptcha;
use addons\wechat\model\WechatContext;
use addons\wechat\model\WechatResponse;
use addons\wechat\model\WechatConfig;

use EasyWeChat\Factory;
use addons\wechat\library\Wechat as WechatService;
use addons\wechat\library\Config as ConfigService;
use think\Log;

/**
 * 微信接口
 */
class Index extends \think\addons\Controller
{

    public $app = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->app = Factory::officialAccount(Config::load());
    }

    /**
     *
     */
    public function index()
    {
        $this->error("当前插件暂无前台页面");
    }

    /**
     * 微信API对接接口
     */
    public function api()
    {
        $this->app->server->push(function ($message) {
            $wechatService = new WechatService;

            $matches = null;
            $openid = $message['FromUserName'];
            $to_openid = $message['ToUserName'];

            $unknownMessage = WechatConfig::getValue('default.unknown.message');
            $unknownMessage = $unknownMessage ? $unknownMessage : "";

            switch ($message['MsgType']) {
                case 'event': //事件消息
                    $event = $message['Event'];
                    $eventkey = $message['EventKey'] ? $message['EventKey'] : $message['Event'];
                    //验证码消息
                    if (in_array($event, ['subscribe', 'SCAN']) && preg_match("/^captcha_([a-zA-Z0-9]+)_([0-9\.]+)/", $eventkey, $matches)) {
                        return WechatCaptcha::send($openid, $matches[1], $matches[2]);
                    }
                    switch ($event) {
                        case 'subscribe'://添加关注
                            $subscribeMessage = WechatConfig::getValue('default.subscribe.message');
                            $subscribeMessage = $subscribeMessage ?: "欢迎关注我们!";
                            return $subscribeMessage;
                        case 'unsubscribe'://取消关注
                            return '';
                        case 'LOCATION'://获取地理位置
                            return '';
                        case 'VIEW': //跳转链接,eventkey为链接
                            return '';
                        case 'SCAN': //扫码
                            return '';
                        default:
                            break;
                    }

                    $wechatResponse = WechatResponse::where(["eventkey" => $eventkey, 'status' => 'normal'])->find();
                    if ($wechatResponse) {
                        $responseContent = (array)json_decode($wechatResponse['content'], true);
                        $wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();
                        $data = ['eventkey' => $eventkey, 'command' => '', 'refreshtime' => time(), 'openid' => $openid];
                        if ($wechatContext) {
                            $wechatContext->save($data);
                        } else {
                            $wechatContext = WechatContext::create($data, true);
                        }
                        $result = $wechatService->response($this, $openid, '', $responseContent, $wechatContext);
                        if ($result) {
                            return $result;
                        }
                    }
                    return $unknownMessage;
                case 'text': //文字消息
                case 'image': //图片消息
                case 'voice': //语音消息
                case 'video': //视频消息
                case 'location': //坐标消息
                case 'link': //链接消息
                default: //其它消息
                    //自动回复处理
                    if ($message['MsgType'] == 'text') {
                        $autoreply = null;
                        $autoreplyList = WechatAutoreply::where('status', 'normal')->cache(true)->order('weigh DESC,id DESC')->select();
                        foreach ($autoreplyList as $index => $item) {
                            //完全匹配和正则匹配
                            if ($item['text'] == $message['Content'] || (in_array(mb_substr($item['text'], 0, 1), ['#', '~', '/']) && preg_match($item['text'], $message['Content'], $matches))) {
                                $autoreply = $item;
                                break;
                            }
                        }

                        if ($autoreply) {
                            $wechatResponse = WechatResponse::where(["eventkey" => $autoreply['eventkey'], 'status' => 'normal'])->find();
                            if ($wechatResponse) {
                                $responseContent = (array)json_decode($wechatResponse['content'], true);
                                $wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();
                                $result = $wechatService->response($this, $openid, $message['Content'], $responseContent, $wechatContext, $matches);
                                if ($result) {
                                    return $result;
                                }
                            }
                        }
                    }
                    return $unknownMessage;
            }
            return ""; //SUCCESS
        });

        $response = $this->app->server->serve();
        // 将响应输出
        $response->send();
        return;
    }

    /**
     * 登录回调
     */
    public function callback()
    {

    }

    /**
     * 支付回调
     */
    public function notify()
    {
        Log::record(file_get_contents('php://input'), "notify");
        $response = $this->app->handlePaidNotify(function ($message, $fail) {
            // 你的逻辑
            return true;
            // 或者错误消息
            $fail('Order not exists.');
        });

        $response->send();
        return;
    }

}
