<?php

namespace app\common\model\post;

use think\Model;


class Collect extends Model
{
    // 表名
    protected $name = 'post_collect';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function business()
    {
        return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function post()
    {
        return $this->belongsTo('app\common\model\post\Post', 'postid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
