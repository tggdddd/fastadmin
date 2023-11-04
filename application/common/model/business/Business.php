<?php

namespace app\common\model\business;

use think\Model;
use traits\model\SoftDelete;

class Business extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;
    use SoftDelete;

    protected $deleteTime = "deletetime";
    protected $append = [
        'avatar_text',
        'province_text',
        'city_text',
        'district_text',
        'source_text'
    ];
    public function getAvatarTextAttr($value,$data){

        if (empty($data['avatar']) || !is_file(ROOT_PATH . 'public' . $data['avatar'])) {
            return cdnurl("/assets/img/avatar.png");
        }
        return cdnurl($data['avatar']);
    }
    public function getProvinceTextAttr($value,$data){
        if(!empty($data['province'])){
            return \model('common/Region')->where("code","=",trim($data['province']))->value('name');
        }
        return "";
    }
    public function getCityTextAttr($value,$data){
        if(!empty($data['city'])){
            return \model('common/Region')->where("code","=",trim($data['city']))->value('name');
        }
        return "";
    }
    public function getDistrictTextAttr($value,$data){
        if(!empty($data['district'])){
            return \model('common/Region')->where("code","=",trim($data['district']))->value('name');
        }
        return "";
    }


    public function getSourceTextAttr($val, $data)
    {
        if (!empty($data['sourceid'])) {
            return \model("common/business/Source")->find($data['sourceid'])->value("name");
        }
        return "未知";
    }
    public function orders()
    {
        return $this->hasMany('app\common\model\subject\Order', 'busid', 'id');
    }

    public function comment()
    {
        return $this->hasMany('app\common\model\subject\Comment', 'busid', 'id');
    }

    public function records()
    {
        return $this->hasMany('app\common\model\business\Record', 'busid', 'id');
    }

    public function cart()
    {
        return $this->hasMany('app\common\model\business\Cart', 'busid', 'id');
    }

    public function product_orders()
    {
        return $this->hasMany('app\common\model\business\Order', 'busid', 'id');
    }

    public function product_address()
    {
        return $this->hasMany('app\common\model\business\Address', 'busid', 'id');
    }
}
