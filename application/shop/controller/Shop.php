<?php

namespace app\shop\controller;

use app\common\controller\ShopController;
use app\common\library\Email;
use think\Db;
use think\Exception;
use think\Loader;
use function model;

/**
 * 商城接口
 */
class Shop extends ShopController
{
    protected $noNeedLogin = ['index', 'category', 'category_list', 'shop_detail', 'login', 'register'];
    protected ?\app\common\model\product\Product $product_model = null;
    protected ?\app\common\model\product\Type $product_type_model = null;
    protected ?\app\common\model\business\Address $address_model = null;
    protected ?\app\common\model\business\Cart $cart_model = null;

    function _initialize()
    {
        parent::_initialize();
        $this->product_model = model("common/product/Product");
        $this->product_type_model = model("common/product/Type");
        $this->address_model = model("common/business/Address");
        $this->cart_model = model("common/business/Cart");
    }

    /**
     * 首页
     *
     * @ApiTitle    (首页)
     * @ApiParams   (name="page", type="integer", description="页码")
     * @ApiParams   (name="limit", type="integer", description="大小")
     */
    public function index()
    {
        $page = $this->request->param("page", "1", "trim");
        $limit = $this->request->param("limit", "10", "trim");
        if ($page == 1) {
//        轮播
            $carousel = $this->product_model
                ->order("createtime", "desc")
                ->limit(5)
                ->where("status", '=', 1)
                ->select();
//        分类
            $category = $this->product_type_model
                ->order("weigh", "desc")
                ->limit(8)
                ->select();
            $result['carousel'] = $carousel;
            $result['category'] = $category;
        }
//        推荐
        $shops = $this->product_model
            ->order("createtime", "desc")
            ->where("status", '=', 1)
            ->page($page, $limit)
            ->select();
        $shopsCount = $this->product_model->count();
        $shopsMore = $shopsCount > $page * $limit;
        $result['shops'] = $shops;
        $result['shopsCount'] = $shopsCount;
        $result['shopsMore'] = $shopsMore;
        $this->success("请求成功", $result);
    }

    /**
     * 商品详情
     */
    public function shop_detail($id)
    {
        $product = $this->product_model->find($id);
        if (empty($product)) {
            $this->error("商品不存在");
        }
        $product['sale'] = $product->orders()->sum("pronum");
        if ($this->user) {
            $product['star'] = !empty($product->stars()->where('busid', '=', $this->user->id)->count());
            $product['cart'] = $this->cart_model->where('busid', '=', $this->user->id)->sum("num");
        }
        $this->success("获取成功", $product);
    }

    /**
     * 更新购物车
     * 商品数量-是否选中
     * @param $data
     */
    public function cart_update($data)
    {
        $this->cart_model->saveAll($data);
        $this->success();
    }

    /**
     * 删除购物车
     * @param id string id
     */
    public function cart_delete($id)
    {
        $cart = model('common/business/Cart');
        $delete = $cart
            ->where('busid', '=', $this->user->id)
            ->where('id', '=', $id)
            ->delete();
        if ($delete) {
            $this->success();
        }
        $this->error($cart->getError());
    }

    /**
     * 加入购物车
     */
    public function cart_add($id, $num = 1)
    {
        $cart = model('common/business/Cart');
        $record = $cart->where('busid', '=', $this->user->id)
            ->where('proid', '=', $id)
            ->find();
        if ($record) {
            $num = $record->num + $num;
            if ($num < 0) {
                $num = 0;
            }
            $result = $record->save(['num' => $num], ['id' => $record->id]);
            if ($result) {
                $this->success("操作成功", $this->cart_model->where('busid', '=', $this->user->id)->sum("num"));
            }
            $this->error("操作失败");
        }
        if ($num < 0) {
            $this->error("操作失败");
        }
        $record = $cart->save([
            "busid" => $this->user->id,
            'proid' => $id,
            'num' => $num
        ]);
        if ($record) {
            $this->success("操作成功", $this->cart_model->where('busid', '=', $this->user->id)->sum("num"));
        }
        $this->error("操作失败");
    }

    /**
     * 购物车
     */
    public function cart()
    {

        $data = $this->user->cart()->with(['product'])->select();;
        $this->success("获取成功", $data);
    }

    /**
     * 收藏商品
     * @param $id int 商品id
     * @param $star bool 收藏
     */
    public function shop_star($id, $star)
    {
        $collection = model('common/business/Collection');
        $record = $collection->where('busid', '=', $this->user->id)
            ->where('proid', '=', $id)
            ->find();
        if ($star) {
            if (empty($record)) {
                $record = $collection->save([
                    "busid" => $this->user->id,
                    'proid' => $id
                ]);
            }
            if (empty($record)) {
                $this->error("收藏失败");
            }
            $this->success("收藏成功");
        }
        $record->delete();
        $this->success("取消收藏");
    }

    /**
     * 分类
     */
    public function category($id = null, $search = null, $flag = 0, $order = 0, $sort = 0, $page = 1, $size = 10)
    {
//        分类页面
        if ($id == null) {
            $recommend = [
                "id" => 0,
                "name" => "为你推荐",
                "products" => $this->product_model
                    ->where("status", '=', 1)
                    ->order("createtime", "desc")
                    ->limit(9)
                    ->select()
            ];
            $category = $this->product_type_model
//                ->with(['products' => function ($query) {
//                    $query
//                        ->where("status", '=', 1)
//                        ->order("createtime", "desc")
//                        ->limit(9);
//                }])
                ->order("weigh", "desc")
                ->select();
            for ($i = 0; $i < count($category); $i++) {
                $category[$i]->setAttr('products', $this->product_model
                    ->where("status", '=', 1)
                    ->where("typeid", '=', $category[$i]->id)
                    ->order("createtime", "desc")
                    ->limit(9)->select()
                );
            }
            array_unshift($category, $recommend);
            $this->success("获取成功", $category);
        }
//        分类详情
        if (!empty($search)) {
            $this->product_model->where("name", 'like', "%" . $search . "%");
        }
        if (!empty($id)) {
            $this->product_model->where("typeid", '=', $id);
        }
        if (!empty($flag)) {
            $this->product_model->where("flag", '=', $flag);
        }
        if ($order == 0) {
            $order = "createtime";
        } else if ($order == 1) {
            $order = "price";
        } else if ($order == 2) {
            $order = "stock";
        }
        if ($sort == 0) {
            $sort = "desc";
        } else if ($sort == 1) {
            $sort = "asc";
        }
        $this->success("获取成功", $this->product_model
            ->where("status", '=', 1)
            ->order($order, $sort)
            ->page($page, $size)
            ->select());
    }

    /**
     * 分类列表
     */
    public function category_list()
    {
        $this->success("", $this->product_model->type()->field([
            "id" => "value",
            "name" => "text"
        ])->select());
    }

    /**
     * 校验登录状态
     * @return void
     *
     */
    public function check_auth()
    {
        $this->auth(true);
        $this->success();
    }

    /**
     * 登录
     */
    public function login()
    {
        $mobile = $this->request->param("mobile");
        $password = $this->request->param("password");
        $data = $this->business_model->where("mobile", "=", $mobile)->find();
        empty($data) and $this->error("用户不存在");
        md5($password . $data['salt']) != $data['password'] and $this->error("密码错误");
        $token = randstr() . $data->getAttr('id');
        $this->token($token, $data);
        $this->success("登录成功", $token);
    }

    /**
     * 注册
     * @return string
     * @throws Exception
     */
    public function register()
    {
        $data = $this->request->param();
        $validate = Loader::validate("app\\common\\validate\\business\\Business");
        if (!$validate->scene("register")->check($data)) {
            $this->error($validate->getError());
        }
        $salt = randstr();
        $data = [
            'mobile' => $data['mobile'],
            'nickname' => $data['mobile'],
            'password' => md5($data['password'] . $salt),
            'salt' => $salt,
            'gender' => '0', //性别
            'deal' => '0', //成交状态
            'money' => '0', //余额
            'auth' => '0', //实名认证，
            'sourceid' => '1', //渠道来源
        ];
        $result = $this->business_model->save($data);
        if ($result) {
            $this->success('注册成功');
        }
        $this->error($this->business_model->getError());
    }

    /**
     * 登出
     */
    public function logout()
    {
        $this->invalid_token();
        $this->success("已登出");
    }

    /**
     * 修改资料
     */
    public function profile()
    {
        if ($this->request->isGet()) {
            $user = $this->user->toArray();
            unset($user['password']);
            unset($user['salt']);
            $this->success("获取成功", $user);
        }
        $data = $this->request->param();
        $validate = validate("app\\common\\validate\\business\\Business");
        if (!$validate->scene("profile")->check($data)) {
            $this->error($validate->getError());
        }
        $avatar = $this->request->file('avatar');
        if (!empty($avatar)) {
            $result = upload_simple($avatar);
            if (!$result['success']) {
                $this->error($result['msg']);
            }
            $data['avatar'] = $result['path'];
        }
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $salt = randstr();
            $data['password'] = md5($data['password'] . $salt);
            $data['salt'] = $salt;
        }
        if (empty($data['city'])) {
            unset($data['city']);
        }
        if (empty($data['province'])) {
            unset($data['province']);
        }
        if (empty($data['district'])) {
            unset($data['district']);
        }
        if ($data['email'] != $this->user->email) {
            $data['auth'] = 0;
        }
        $result = $this->business_model->isUpdate()->allowField(true)->save($data);
        if ($result) {
            if (!empty($avatar)) {
                $path = "." . $this->user->avatar;
                is_file($path) and @unlink($path);
            }
            $this->success("保存成功");
        }
        $this->error("更新失败 " . $this->business_model->getError());
    }

    /**
     * 认证邮箱
     */
    public function auth_email($code = null, $email = null)
    {
//        验证
        if (!empty($code)) {
            $email_r = $this->user->email;
            if ($email_r != $email) {
                $this->error("邮箱与账号绑定不符");
            }
            $model = model("common/Ems");
            $record = $model->where(["email" => $email, "code" => $code, "event" => "auth"])->find();
            if (!$record) {
                $this->error("验证码错误");
            }
            Db::startTrans();
            $result = $this->business_model
                ->isUpdate()
                ->save([
                    "id" => $this->user->id,
                    "auth" => 1
                ]);
            if (!$result) {
                $this->error("更新账号状态失败");
            }
            $delete = $model->where('id', "=", $record['id'])->delete();
            if (!$delete) {
                $this->error("删除验证码记录失败");
            }
            $this->business_model->commit();
            Db::commit();
            $this->success("验证成功");
        }
//        发送
        $email = $this->user->email;
        $code = randstr(5);
        $model = model("common/ems");
        $data = [
            'email' => $email,
            'code' => $code,
            'event' => "auth"
        ];
        Db::startTrans();
        $result = $model->save($data);
        if (!$result) {
            $this->error("验证码生成失败");
        }
        $name = config("site.name");
        $subject = "【{$name}】邮箱验证";
        $content = "验证码：{$code}，如非本人操作，请忽略此邮件。";
        $emailInstance = new Email();
        $sendResult = $emailInstance->to($email)
            ->subject($subject)
            ->message($content)
            ->send();
        if ($sendResult) {
            Db::commit();
            $this->success("邮件发送成功");
        } else {
            $this->error($emailInstance->getError());
        }
    }

    /**
     * 收货地址
     */
    public function address($action = null, $id = null)
    {
        switch ($action) {
            case "save":
                $data = $this->request->param();
                $area = $data['area'];
                if (empty($area)) {
                    $this->error("请选择地区");
                }
                $record = model("common/Region")->where("code", "=", $area)->find();
                if (empty($record)) {
                    $this->error("地区码错误");
                }
                $codes = explode(",", $record['parentpath']);
                $data['province'] = $codes[0];
                $data['city'] = $codes[1];
                $data['district'] = $codes[2];
                $data['busid'] = $this->user->id;
                $model = $this->address_model;
                Db::startTrans();
                if ($data['status'] == 1) {
                    $result = $model
                        ->where('busid', '=', $this->user->id)
                        ->update([
                            "status" => 0
                        ]);
                    if ($result === false) {
                        $this->error($model->getError());
                    }
                }
                if (!empty($data['id'])) {
                    $model->isUpdate();
                }
                $result = $model->allowField(true)->save($data);
                if ($result === false) {
                    $this->error($model->getError());
                }
                Db::commit();
                $this->success("操作成功", $data);
                break;
            case "detail":
                $data = $this->address_model
                    ->where('busid', '=', $this->user->id)
                    ->find($id);
                if (empty($data)) {
                    $this->error("该记录不存在");
                }
                $this->success("获取成功", $data);
                break;
            case "delete":
                $result = $this->address_model
                    ->where('busid', '=', $this->user->id)
                    ->delete($id);
                if ($result) {
                    $this->success();
                }
                $this->error("删除失败，未找到该记录");
                break;
            default:
                $data = $this->address_model
                    ->where('busid', '=', $this->user->id)
                    ->select();
                $this->success("获取成功", $data);
        }
    }
}
