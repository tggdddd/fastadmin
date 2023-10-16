<?php

namespace app\common\model\business;

use think\Model;

class Business extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = "createtime";
    protected $updateTime = false;
    protected $append = [
        'avatar_text',
        'province_text',
        'city_text',
        'district_text'
    ];
    public function getAvatarTextAttr($value,$data){
        if (empty($data['avatar']) || !is_file(ROOT_PATH . 'public' . $data['avatar'])) {
            return "/assets/img/avatar.png";
        }
        return $data['avatar'];
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
}
