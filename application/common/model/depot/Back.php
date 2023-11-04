<?php

namespace app\common\model\depot;

use think\Model;
use traits\model\SoftDelete;

class Back extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'depot_back';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];

    public function getThumbsAttr($val)
    {
        if (empty($val)) {
            return [];
        }
        $val = explode(",", $val);
        $val = array_filter($val, fn($e) => !empty($e));
        return array_map(fn($e) => cdnurl($e), $val);
    }

    public function backProducts()
    {
        return $this->hasMany("app\common\model\depot\BackProduct", 'backid', 'id');
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusList()
    {
        //todo 多语言
        return ["0" => "未审核",
            "1" => "已审核，未收货",
            "2" => "已收货，未入库",
            "3" => "已入库，生成入库单记录",
            "-1" => "审核不通过"];
    }

    public function admin()
    {
        return $this->belongsTo('app\common\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function reviewer()
    {
        return $this->belongsTo('app\common\model\Admin', 'reviewerid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function stromanid()
    {
        return $this->belongsTo('app\common\model\Admin', 'stromanid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function storageid()
    {
        return $this->belongsTo('Storage', 'storageid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
