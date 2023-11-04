<?php

namespace app\common\model\business;

use think\Model;
use traits\model\SoftDelete;

class Address extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'business_address';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'address_detail'
    ];


    public function getAddressDetailAttr($val, $data)
    {
        $code = $data['district'] ?? $data['city'];
        if (empty($code)) {
            $code = $data['province'];
        }
        $record = model('common/Region')->where('code', '=', $code)->find();
        if (empty($record)) {
            return $data['address'];
        }
        return $record->province . $record->city . $record->district . $data['address'];
    }

    public function business()
    {
        return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
