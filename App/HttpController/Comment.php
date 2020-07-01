<?php

namespace App\HttpController;

use EasySwoole\Http\Message\Status;
use App\Service\Comment as CommentService;
use EasySwoole\Component\Context\ContextManager;

class Comment extends ApiBase
{
    // 需要检查用户状态的方法
    protected $checkAction = ['newComment', 'delComment'];

    /**
     * 新增回复（回复楼主）
     * @Method(allow={POST})
     * @Param(name="article_id",from={POST},notEmpty="不能为空",integer="非法文章ID",min={1,"非法文章ID"})
     * @Param(name="content",from={POST},notEmpty="不能为空",lengthMax={200,"字数最多不超过200字"})
     * @Param(name="comment_id",from={POST},integer="非法评论ID",min={1,"非法评论ID"})
     */
    public function newComment($articleId, $content, $commentId = 0)
    {
        // 请求参数
        $data = ContextManager::getInstance()->get('data');

        // 附加用户ID
        $data['user_id'] = $this->userId;

        // 附加类型
        $data['type'] = $commentId ? 2 : 1;

        // 操作方法
        $method = $commentId ? 'addComment' : 'addReply';

        // 发布文章
        CommentService::getInstance()->$method($data);

        $this->writeJson(Status::CODE_OK, [], 'success');
    }

    /**
     * 评论列表
     * @Method(allow={GET})
     * @Param(name="article_id",from={GET},notEmpty="不能为空",integer="非法文章ID",min={1,"非法文章ID"})
     * @Param(name="page",from={GET},integer="非法page参数",min={1,"page参数最小为1"})
     */
    public function CommentList($articleId, $page = 1)
    {
        $res = CommentService::getInstance()->list($articleId, $page);

        $this->writeJson(Status::CODE_OK, $res, 'success');
    }

    /**
     * 删除评论
     * @Method(allow={DELETE})
     * @Param(name="article_id",from={GET},notEmpty="不能为空",integer="非法文章ID",min={1,"非法文章ID"})
     * @Param(name="id",from={GET},notEmpty="不能为空",integer="非法ID",min={1,"非法ID"})
     */
    public function delComment($articleId, $id)
    {
        $res = CommentService::getInstance()->del($articleId, $id, $this->userId);

        $this->writeJson(Status::CODE_OK, $res, 'success');
    }
}
