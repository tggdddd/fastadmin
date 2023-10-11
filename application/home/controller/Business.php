<?php

namespace app\home\controller;

use app\common\controller\Home;
use app\common\library\Email;

class Business extends Home
{
    public function index()
    {
        return $this->fetch();
    }

    public function profile()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param('', "", "trim");
            $data['id'] = $this->view->business['id'];
            $validate = validate("Business");
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
            $result = $this->business_model->isUpdate()->allowField(true)->save($data);
            if ($result) {
                if (!empty($avatar)) {
                    $path = "." . $this->view->business->avatar;
                    is_file($path) and @unlink($path);
                }
                cookie("business", ['mobile' => $data['mobile'], "id" => $data['id']]);
                $this->success("保存成功", url("business/profile"));
            }
            $this->error($this->business_model->getError());
        }
        return $this->fetch();
    }

    public function email()
    {
        if ($this->request->isAjax()) {
            $action = $this->request->param("action");
            if ($action == "auth") {
                empty(($email = $this->view->business['email'])) and $this->error("邮箱获取失败");
                $code = randstr(5);
                $model = model("common/ems");
                $data = [
                    'email' => $email,
                    'code' => $code,
                    'event' => $action
                ];
                $model->startTrans();
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
                if($sendResult){
                    $model->commit();
                    $this->success("邮件发送成功");
                }else{
                    $this->error($emailInstance->getError());
                }
            }
        }
        if($this->request->isPost()){
            $email = $this->view->business['email'];
            $code = $this->request->param("code","","trim");
            if(empty($email) || empty($code)){
                $this->error("邮箱验证码不能为空");
            }
            $model = model("common/Ems");
            $record = $model->where(["email" => $email, "code" => $code, "event" => "auth"])->find();
            if(!$record){
                $this->error("验证码错误");
            }
            $model->startTrans();
            $this->business_model->startTrans();
            $result = $this->business_model
                ->isUpdate()
                ->save([
                    "id"=>$this->view->business['id'],
                    "auth"=>1
                ]);
            if(!$result){
                $this->error("更新账号状态失败");
            }
            $delete = $model->where('id',"=",$record['id'])->delete();
            if(!$delete){
                $this->error("删除验证码记录失败");
            }
            $this->business_model->commit();
            $model->commit();
            $this->success("验证成功","home/business/index");
        }
        return $this->fetch();
    }
}
