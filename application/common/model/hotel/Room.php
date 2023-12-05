<?php

namespace app\common\model\hotel;

use think\Model;
use traits\model\SoftDelete;

class Room extends Model
{
    // 表名
    protected $name = 'hotel_room';

    use SoftDelete;

    protected $deleteTime = "deletetime";
    protected $append = [
        "thumb_text",
        "tags"
    ];

    public function getThumbTextAttr($val, $data)
    {
        if (empty($data['thumb']) || !is_file(ROOT_PATH . 'public' . $data['thumb'])) {
            return cdnurl("/assets/img/avatar.png");
        }
        return cdnurl($data['thumb']);
    }

    public function getTagsAttr($val, $data)
    {
        if (empty($data['flag'])) {
            return [];
        }
        $tags = $this->getRoomTags();
        return array_map(fn($v) => $tags[$v], explode(",", $data['flag']));
    }

    public function getRoomTags()
    {
        return config("site.hotel_tag");
    }

    public function collect()
    {
        return $this->hasOne("app\common\model\hotel\Collect", "room_id", "id");
    }
}
