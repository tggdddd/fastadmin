<?php

namespace app\common\model;

use think\Model;

class Admin extends Model
{
    // 表名
    protected $name = 'admin';
    protected $append = [
        "avatar_text"
    ];

    public function getAvatarTextAttr($val, $data)
    {
        if (empty($data['avatar']) || !is_file(ROOT_PATH . 'public' . $data['avatar'])) {
            return cdnurl("/assets/img/avatar.png");
        }
        return cdnurl($data['avatar']);
    }
}
