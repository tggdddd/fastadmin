<?php

namespace app\common\model\product;

use think\Model;
use traits\model\SoftDelete;

class Product extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'flag_text',
        'status_text'
    ];

    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $list = $this->getFlagList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getFlagList()
    {
        return ['1' => __('NEW PRODUCT'), '2' => __('HOT PRODUCT'), '3' => __('RECOMMEND')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusList()
    {
        return ['0' => __('REMOVED FROM SHELVES'), '1' => __('ON THE SHELVES')];
    }

    public function type()
    {
        return $this->belongsTo('Type', 'typeid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function unit()
    {
        return $this->belongsTo('Unit', 'unitid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
