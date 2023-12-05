<?php

namespace app\ask\controller;

use app\common\controller\AskController;
use app\common\exception\ParamNotFoundException;
use think\Db;

class Post extends AskController
{
    protected $post_model = null;
    protected $noNeedLogin = ["cate", "index", "info"];
    protected $CollectModel = null;
    protected $CateModel = null;
    protected $CommentModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->post_model = model('common/post/Post');
        $this->CollectModel = model('common/post/Collect');
        $this->CateModel = model('common/post/Cate');
        $this->CommentModel = model('common/post/Comment');
    }

    /**
     * 编辑帖子的详情获取
     */
    public function editi($postid)
    {
        if (empty($postid)) {
            throw new ParamNotFoundException();
        }
        $post = $this->post_model->find($postid);
        if (empty($post) || $post->busid != $this->user->id) {
            $this->error("帖子不存在");
        }
        $this->success("获取成功", $post);
    }

    /**
     * 点赞
     */
    public function like()
    {
        $postid = $this->request->param('postid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');
        $comid = $this->request->param('comid', 0, 'trim');
        $comment = $this->CommentModel->find($comid);
        if (!$comment) {
            $this->error('评论不存在');
        }
        $data = [
            'id' => $comid,
        ];
        //判断点过赞还是没点过赞
        $like_list = $comment->like_list;
        //判断元素是否在数组内
        if (in_array($busid, $like_list)) {
            //如果有找到就说明点个赞，就是要取消点赞
            $index = array_search($busid, $like_list);
            unset($like_list[$index]);
        } else {
            $like_list[] = $busid;
        }
        if (empty($like_list)) {
            $data['like'] = NULL;
        } else {
            $data['like'] = implode(',', $like_list);
        }
        //更新
        $result = $this->CommentModel->isUpdate()->save($data);
        if ($result === FALSE) {
            $this->error('点赞失败');
        } else {
            $this->success('点赞成功');
        }
    }

    /**
     * 编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $title = $this->request->param('title', '', 'trim');
            $content = $this->request->param('content', '', 'trim');
            $point = $this->request->param('point', 0, 'trim');
            $cateid = $this->request->param('cateid', 0, 'trim');
            $id = $this->request->param('id', 0, 'trim');
            //确保帖子是存在
            $post = $this->user->askPosts()->where(['id' => $id])->find();
            if (false === $post) {
                $this->error('帖子不存在');
            }
            //查看用户积分是否充足
            $UpdatePoint = bcsub($this->user->point, $point);
            if ($UpdatePoint < 0) {
                $this->error('积分不足，请先充值');
            }
            //帖子表、用户表、消费记录表
            Db::startTrans();
            //编辑帖子
            $PostData = [
                'id' => $id,
                'title' => $title,
                'content' => $content,
                'cateid' => $cateid,
                'status' => '0',
            ];
            //积分
            if ($point > 0) {
                $PostData['point'] = bcadd($post['point'], $point);

                //更新用户积分
                $BusinessStatus = $this->user->isUpdate()->save([
                    'point' => $UpdatePoint
                ]);
                if (empty($BusinessStatus)) {
                    Db::rollback();
                    $this->error("服务器异常2");
                }
            }
            $PostStatus = $this->post_model->isUpdate()->save($PostData);
            if (empty($PostStatus)) {
                $this->error("帖子未改动，无需修改");
            }

            Db::commit();
            $this->success('编辑帖子成功');

        }
    }

    /**
     * 收藏
     */
    public function collect()
    {
        $postid = $this->request->param('postid', 0, 'trim');
        //查询这个是否有收藏过
        $collect = $this->user->askPostCollections()->where(['postid' => $postid])->find();
        if ($collect) {
            //有收藏过 要取消收藏 删除记录
            $result = $collect->delete();
            if ($result === FALSE) {
                $this->error('取消失败');
            } else {
                $this->success('已取消');
            }
        } else {
            //没收藏过  要新增插入
            $result = $this->user->askPostCollections()->save(['postid' => $postid]);
            if ($result === FALSE) {
                $this->error('收藏失败');
            } else {
                $this->success('已收藏');
            }
        }
    }

    /**
     * 获取首页帖子
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'trim');
        $cateid = $this->request->param('cateid', 0, 'trim');
        $keywords = $this->request->param('keywords', '', 'trim');
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $where = [];
        if ($cateid) {
            $where['cateid'] = $cateid;
        }
        if (!empty($keywords)) {
            $where['title'] = ['like', "%$keywords%"];
        }

        $list = $this->post_model
            ->with(['cate', 'business'])
            ->where($where)
            ->order('id', 'desc')
            ->limit($offset, $limit)->select();
        if ($list) {
            $this->success('返回帖子数据', $list);
        }
        $this->error('暂无更多数据');
    }

    /**
     * 创建帖子
     */
    public function add()
    {
        $title = $this->request->param('title', '', 'trim');
        $content = $this->request->param('content', '', 'trim');
        $point = $this->request->param('point', 0, 'trim');
        $cateid = $this->request->param('cateid', 0, 'trim');

        //查看用户积分是否充足
        $UpdatePoint = bcsub($this->user->point, $point);
        if ($UpdatePoint < 0) {
            $this->error('积分不足，请先充值');
        }
        //帖子表、用户表、消费记录表
        Db::startTrans();
        //插入帖子
        $PostData = [
            'title' => $title,
            'content' => $content,
            'point' => $point,
            'busid' => $this->user->id,
            'cateid' => $cateid,
            'status' => '0',
        ];
        $PostStatus = $this->post_model->save($PostData);
        if ($PostStatus === FALSE) {
            $this->error($this->post_model->getError());
        }
        //更新用户积分
        $BusinessData = [
            'id' => $this->user->id,
            'point' => $UpdatePoint
        ];
        $BusinessStatus = $this->business_model->isUpdate()->save($BusinessData);
        if ($BusinessStatus === FALSE) {
            $this->error($this->business_model->getError());
        }
        if ($PostStatus === FALSE || $BusinessStatus === FALSE) {
            $this->error('发帖失败');
        } else {
            Db::commit();
            $this->success('发帖成功', [
                'postid' => $this->post_model->id,
                'redirect' => '/pages/post/info']);
        }
    }

    /**
     * 查询帖子分类
     */
    public function cate()
    {
        $list = model('common/post/Cate')->order('weigh', 'asc')->select();
        if ($list) {
            $this->success('帖子分类', $list);
        } else {
            $this->error('无帖子分类');
        }
    }

    /**
     * 帖子详情
     */
    public function info()
    {
        $postid = $this->request->param('postid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        $post = $this->post_model->with(['business', 'cate'])->find($postid);
        if (!$post) {
            $this->error('暂无帖子信息');
        }
        //如果有找到用户信息
        if (!empty($this->user)) {
            //查询收藏的状态
            $collect = $this->user->askPostCollections()->find();
            if ($collect) {
                //追加自定义数组元素
                $post['collect'] = true;
            } else {
                $post['collect'] = false;
            }
        }
        if (!empty($this->user)) {
            $record = $this->user->starUser()->where(["busid" => $post->busid])->find();
            $post->followee = !empty($record);
        }
        $this->success('返回帖子信息', $post);
    }


    //采纳
    public function accept()
    {
        $comid = $this->request->param('comid', 0, 'trim');
        $postid = $this->request->param('postid', 0, 'trim');
//            $busid = $this->request->param('busid', 0, 'trim');
        $busid = $this->user->id;
        $comment = $this->CommentModel->find($comid);
        if (!$comment) {
            $this->error('评论不存在');
        }
        //采纳人的信息
        $acceptid = isset($comment['busid']) ? $comment['busid'] : 0;
        $accept = $this->business_model->find($acceptid);
        if (!$accept) {
            $this->error('采纳人信息未知');
        }
        $where = ['id' => $postid, 'busid' => $busid];
        $post = $this->post_model->where($where)->find();
        if (!$post) {
            $this->error('帖子不存在');
        }
        //采纳不能采纳自己
        if ($acceptid == $busid) {
            $this->error('帖子不能采纳自己');
        }
        //帖子表(更新采纳人和状态)
        // 评论表改状态
        // 更改用户表
        Db::startTrans();
        //更新帖子表
        $PostData = [
            'id' => $post['id'],
            'status' => '1',
            'accept' => $acceptid,
        ];
        $PostStatus = $this->post_model->isUpdate()->save($PostData);
        if ($PostStatus === FALSE) {
            $this->error($this->post_model->getError());
        }
        //评论表
        $CommentData = [
            'id' => $comid,
            'status' => '1',
        ];
        $CommentStatus = $this->CommentModel->isUpdate()->save($CommentData);
        if ($CommentStatus === FALSE) {
            Db::rollback();
            $this->error($this->CommentModel->getError());
        }
        //更新用户表
        $PostPoint = $post['point'];
        $BusPoint = $accept['point'];
        $UpdatePoint = bcadd($PostPoint, $BusPoint);
        $BusData = [
            'id' => $accept['id'],
            'point' => $UpdatePoint
        ];
        //更新用户积分
        $BusStatus = $this->business_model->isUpdate(true)->save($BusData);
        if ($BusStatus === FALSE) {
            Db::rollback();
            $this->error($this->business_model->getError());
        }
        if ($PostStatus === FALSE || $CommentStatus === FALSE || $BusStatus === FALSE) {
            Db::rollback();
            $this->error('采纳失败');
        } else {
            Db::commit();
            $this->success('采纳成功');
        }
    }

    /**
     * 关注
     */
    public function flowee($busid)
    {
        if (empty($busid)) {
            throw new ParamNotFoundException();
        }
        $record = $this->user->starUser()->where("busid", "=", $busid)->find();
        if (empty($record)) {
//            关注
            $result = $this->user->starUser()->save(["busid" => $busid]);
            if (empty($result)) {
                $this->error("关注失败");
            }
            $this->success("关注成功", true);
        }
        $delete = $record->delete();
        if (empty($delete)) {
            $this->error("服务器异常");

        }
        $this->success("取消关注", false);
    }
}
