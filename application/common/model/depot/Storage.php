<?php

namespace app\common\model\depot;

use think\Model;
use traits\model\SoftDelete;

class Storage extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'depot_storage';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'status_text'
    ];

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getTypeList()
    {
        return ['1' => __('DIRECT SALES INTO WAREHOUSE'), '2' => __('RETURN-TO-WAREHOUSE')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusList()
    {
        return ['0' => __('PENDING'), '1' => __('APPROVAL FAILED'), '2' => __('TO BE STOCKED'), '3' => __('WAREHOUSING COMPLETED')];
    }

    public function admin()
    {
        return $this->belongsTo('app\common\model\Admin', 'reviewerid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function reviewer()
    {
        return $this->belongsTo('app\common\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function supplier()
    {
        return $this->belongsTo('Supplier', 'supplierid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
