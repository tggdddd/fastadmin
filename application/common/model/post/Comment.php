<?php

namespace app\common\model\post;

use think\Model;
use traits\model\SoftDelete;

class Comment extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'post_comment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'createtime_text',
        'like_count',
        'like_list',
        'comment_count'
    ];

    public function getCommentCountAttr($value, $data)
    {
        $id = $data['id'];
        $count = $this->where(['pid' => $id])->count();
        return $count ? $count : 0;
    }

    public function getLikeListAttr($value, $data)
    {
        $like = $data['like'];
        if (empty($like)) {
            return [];
        }
        //把字符串转换数组
        return explode(',', $like);
    }

    //点赞数量
    public function getLikeCountAttr($value, $data)
    {
        $like = $data['like'];
        if (empty($like)) {
            return 0;
        }
        //把字符串转换数组
        $arr = explode(',', $like);
        //返回数组的长度
        return count($arr);
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

    //递归查询子集的方法
    public function sublist($pid, $data = [])
    {
        //查找子集
        $son = $this->with(['business'])
            ->where(['pid' => $pid])
            ->order(['pid asc', 'id asc'])
            ->select();
        if (empty($son)) {
            return [];
        }

        //循环递归
        foreach ($son as &$item) {
            $item['chidren'] = $this->sublist($item['id']);
        }
        return $son;
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusList()
    {
        return ['0' => __('NOT ADOPTED'), '1' => __('ADOPTED')];
    }

    public function business()
    {
        return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function post()
    {
        return $this->belongsTo('app\common\model\post\Post', 'postid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
