<?php

namespace app\common\model\depot;

use think\Model;


class Supplier extends Model
{


    // 表名
    protected $name = 'depot_supplier';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'province_text',
        'city_text',
        'district_text',
    ];


    public function getProvinceTextAttr($value, $data)
    {
        if (!empty($data['province'])) {
            return \model('common/Region')->where("code", "=", trim($data['province']))->value('name');
        }
        return "";
    }

    public function getCityTextAttr($value, $data)
    {
        if (!empty($data['city'])) {
            return \model('common/Region')->where("code", "=", trim($data['city']))->value('name');
        }
        return "";
    }

    public function getDistrictTextAttr($value, $data)
    {
        if (!empty($data['district'])) {
            return \model('common/Region')->where("code", "=", trim($data['district']))->value('name');
        }
        return "";
    }


}
