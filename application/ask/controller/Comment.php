<?php

namespace app\ask\controller;

use app\common\controller\AskController;

class Comment extends AskController
{
    protected $PostModel;
    protected $CommentModel;
    protected $noNeedLogin = ['index'];

    public function __construct()
    {
        parent::__construct();
        $this->PostModel = model('common/post/Post');
        $this->CommentModel = model('common/post/Comment');
    }

    /**
     * 获取帖子评论
     */
    public function index()
    {
        $postid = $this->request->param('postid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');
        $pid = $this->request->param('pid', 0, 'trim');
        $post = $this->PostModel->find($postid);
        if (!$post) {
            $this->error('暂无帖子信息');
        }
        $top = $this->CommentModel
            ->with(['business'])
            ->where(['postid' => $postid, 'pid' => $pid])
            ->order(['pid asc', 'id asc'])
            ->select();
        if (empty($top)) {
            $this->error('暂无评论');
        }
        //循环顶级
        foreach ($top as $key => &$item) {
            $item['children'] = $this->CommentModel->sublist($item['id']);
        }
        $this->success('返回评论列表', $top);
    }

    /**
     * 评论帖子
     */
    public function add()
    {
        $pid = $this->request->param('pid', 0, 'trim');
        $content = $this->request->param('content', '', 'trim');
        $postid = $this->request->param('postid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');
        $post = $this->PostModel->find($postid);
        if (!$post) {
            $this->error('帖子不存在');
        }
        //组装数据
        $data = [
            'pid' => $pid,
            'content' => $content,
            'postid' => $postid,
            'status' => '0',
        ];
        $result = $this->user->askPostComments()->save($data);
        if ($result === FALSE) {
            $this->error('评论失败');
        }
        $this->success('评论成功');
    }

}
