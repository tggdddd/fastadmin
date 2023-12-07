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
        'source_text',
        'gender_text'
    ];

    public function getGenderTextAttr($value, $data)
    {
        if (empty($data['gender'])) {
            return "保密";
        }
        if ($data['gender'] == 1) {
            return "男";
        }
        if ($data['gender'] == 2) {
            return "女";
        }
        return "未知";
    }
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

    public function askPostComments()
    {
        return $this->hasMany('app\common\model\post\Comment', 'busid', 'id');
    }

    public function askPosts()
    {
        return $this->hasMany('app\common\model\post\Post', 'busid', 'id');
    }

    public function askPostCollections()
    {
        return $this->hasMany('app\common\model\post\Collect', 'busid', 'id');
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

    public function followee()
    {
        return $this->hasMany('app\common\model\business\AskFollow', 'busid', 'id');
    }

    public function starUser()
    {
        return $this->hasMany('app\common\model\business\AskFollow', 'followee', 'id');
    }

    /**
     * 社区私信
     */
    public function askSendLetter()
    {
        return $this->hasMany('app\common\model\business\AskMessage', 'from_user_id', 'id');
    }

    public function askReceiveLetter()
    {
        return $this->hasMany('app\common\model\business\AskMessage', 'to_user_id', 'id');
    }

    /*
     * 酒店收藏
     */
    public function hotelCollect()
    {
        return $this->hasMany('app\common\model\hotel\Collect', "busid", "id");
    }

    /*
     * 酒店客户列表
     */
    public function hotelGuest()
    {
        return $this->hasMany('app\common\model\hotel\Guest', "busid", "id");
    }

    /**
     * 酒店优惠券领取记录
     */
    public function hotelCouponReceive()
    {
        return $this->hasMany('app\common\model\hotel\CouponReceive', "busid", "id");
    }
}
