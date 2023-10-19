<?php

namespace app\common\model\business;

use think\Model;


class Receive extends Model
{


    // 表名
    const APPLY = "apply";

    // 自动写入时间戳字段
    const ASSIGN = "allot";

    // 定义时间戳字段名
    const RECOVERY = "recovery";
    const REJECT = "reject";
    protected $name = 'business_receive';

    // 追加属性
    protected $autoWriteTimestamp = false;
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;
    protected $append = [
        'applytime_text',
        'status_text'
    ];

    public function record($fromId, $businessId, $status)
    {
        return $this->save([
            "applytime" => time(),
            "applyid" => $fromId,
            "status" => $status,
            "busid" => $businessId
        ]);
    }

    public function getApplytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['applytime']) ? $data['applytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusList()
    {
        return ['apply' => __('Apply'), 'allot' => __('Allot'), 'recovery' => __('Recovery'), 'reject' => __('Reject')];
    }

    public function business()
    {
        return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'applyid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    protected function setApplytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }
}
