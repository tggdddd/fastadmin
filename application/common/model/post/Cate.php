<?php

namespace app\common\model\post;

use think\Model;


class Cate extends Model
{

    // 表名
    protected $name = 'post_cate';

    // 追加属性
    protected $append = [

    ];

    public function posts()
    {
        return $this->hasMany("common\model\post\Post", "cateid", "id");
    }

}
