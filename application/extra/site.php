<?php

return array(
    'name' => '我的网站',
    'beian' => '',
    'cdnurl' => \think\Env::get("site.cdnurl", "http://course.cc"),
    'version' => '1.0.1',
    'timezone' => 'Asia/Shanghai',
    'forbiddenip' => '',
    'languages' =>
        array(
            'backend' => 'zh-cn',
            'frontend' => 'zh-cn',
        ),
    'fixedpage' => 'dashboard',
    'categorytype' =>
        array(
            'default' => '默认',
            'page' => '单页',
            'article' => '文章',
            'test' => 'Test',
        ),
    'configgroup' =>
        array(
            'basic' => '基础配置',
            'email' => '邮件配置',
            'dictionary' => '字典配置',
            'user' => '会员配置',
            'example' => '示例分组',
        ),
    'mail_type' => '1',
    'mail_smtp_host' => 'smtp.10086.cn',
    'mail_smtp_port' => '25',
    'mail_smtp_user' => '15014586591@139.com',
    'mail_smtp_pass' => '963ca01e0fe006e6ca00',
    'mail_verify_type' => '0',
    'mail_from' => '15014586591@139.com',
    'attachmentcategory' =>
        array(
            'category1' => '分类一',
            'category2' => '分类二',
            'custom' => '自定义',
        ),
    'pay.wx' => '/uploads/20231108/dbb433805f2e8c0bb202c7758cfefff8.png',
    'pay.zfb' => '/uploads/20231108/71a747b783a2304bc740eadfcf154a40.jpg',
    'hotel_tag' =>
        array(
            0 => '推广优惠',
            1 => '月租惠选',
            2 => '满减优惠',
            3 => '节假日优惠',
        ),
);
