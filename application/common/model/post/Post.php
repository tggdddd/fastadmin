<?php

namespace app\common\model\post;

use think\Model;
use traits\model\SoftDelete;

class Post extends Model
{

    use SoftDelete;

    // 表名
    protected $name = 'post';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text',
        'createtime_text',
        'comment_text',
        'collect_text',
        'cate_text'
    ];

    public function getCateTextAttr($val, $data)
    {
        if (empty($data["cateid"])) {
            return "";
        }
        $name = \model("common/post/Cate")->where(["id" => $data["cateid"]])->value("name");
        return $name;
    }

    // 帖子状态数据

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->statuslist();
        return isset($list[$value]) ? $list[$value] : '';
    }

    // 帖子状态的获取器

    public function statuslist()
    {
        return [
            '0' => __('未解决'),
            '1' => __('已解决'),
        ];
    }

    //时间戳

    public function getCreatetimeTextAttr($value, $data)
    {
        $createtime = $data['createtime'];

        if (empty($createtime)) {
            return '';
        }

        return date("Y-m-d H:i", $createtime);
    }

    public function getCommentTextAttr($value, $data)
    {
        $postid = $data['id'];

        $count = model('Post.Comment')->where(['postid' => $postid])->group('busid')->count();

        return $count ? $count : 0;
    }

    public function getCollectTextAttr($value, $data)
    {
        $postid = $data['id'];

        $count = model('Post.Collect')->where(['postid' => $postid])->count();

        return $count ? $count : 0;
    }

    public function getStatusList()
    {
        return ['1' => __('SOLVED'), '0' => __('UNSOLVED')];
    }

    public function business()
    {
        return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function acceptor()
    {
        return $this->belongsTo('app\common\model\business\Business', 'accept', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function cate()
    {
        return $this->belongsTo('app\common\model\post\Cate', 'cateid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
