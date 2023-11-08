<?php

use think\Env;

return array(
    'name' => '我的网站',
    'beian' => '',
    'cdnurl' => Env::get("site.cdnurl", "http://course.cc"),
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
            'default' => 'Default',
            'page' => 'Page',
            'article' => 'Article',
            'test' => 'Test',
        ),
    'configgroup' =>
        array(
            'basic' => 'Basic',
            'email' => 'Email',
            'dictionary' => 'Dictionary',
            'user' => 'User',
            'example' => 'Example',
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
            'category1' => 'Category1',
            'category2' => 'Category2',
            'custom' => 'Custom',
        ),
    'pay.wx' => '/uploads/20231108/dbb433805f2e8c0bb202c7758cfefff8.png',
    'pay.zfb' => '/uploads/20231108/71a747b783a2304bc740eadfcf154a40.jpg',
);
