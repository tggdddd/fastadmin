<?php

namespace app\common\model\subject;

use think\Model;
use think\model\relation\BelongsTo;

class Subject extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;
    protected $deleteTime = "deletetime";

    protected $append = [
        'thumbs_text',
        'createtime_text',
        'likes_text'
    ];

    public function getLikesTextAttr($value, $data)
    {
        if (empty($data['likes'])) {
            return 0;
        }
        return count(explode(",", $data['likes']));
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        if (empty($data['createtime'])) {
            return "";
        }
        return date('Y-m-d H:i', $data['createtime']);
    }

    public function getThumbsTextAttr($value, $data)
    {
        if (empty($data['thumbs']) || !is_file(ROOT_PATH . 'public' . $data['thumbs'])) {
            return "/assets/home/images/video.jpg";
        }
        return $data['thumbs'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo('app\common\model\subject\Category', 'cateid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function chapters()
    {
        return $this->hasMany('app\common\model\subject\Chapter', 'subid', 'id', [], 'LEFT');
    }

    public function comments()
    {
        return $this->hasMany('app\common\model\subject\Comment', 'subid', 'id', [], 'LEFT');
    }

    public function orders()
    {
        return $this->hasMany('app\common\model\subject\Order', 'subid', 'id', [], 'LEFT');
    }
}
