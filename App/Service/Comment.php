<?php

namespace App\Service;

use App\Exception\ErrCode;
use App\Exception\SystemException;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use App\Exception\ParameterException;
use App\Exception\PermissionException;
use App\Model\Comment as CommentModel;
use App\Model\Article as ArticleModel;
use App\Service\Article as ArticleService;
use EasySwoole\EasySwoole\Config as ESConfig;

class Comment
{
    use Singleton;

    /**
     * 新增回复（主方法）
     * @param  Array  $data  数据
     */
    public function addReply($data)
    {
        // 查询楼主用户ID
        $authorId = ArticleService::getInstance()->getAuthor($data['article_id']);

        // 附加被评论用户
        $data['to_user_id'] = $authorId;

        // 新增
        $this->add($data);
    }

    /**
     * 新增评论（主方法）
     * @param  Array  $data  数据
     */
    public function addComment($data)
    {
        // 查询层主用户ID
        $authorId = $this->getAuthor($data['comment_id']);

        // 附加被评论用户
        $data['to_user_id'] = $authorId;

        // 新增
        $this->add($data);
    }

    /**
     * 删除评论（主方法）
     * @param  Int  $articleId  文章ID
     * @param  Int  $commentId  评论ID
     * @param  Int  $userId     用户ID
     */
    public function del($articleId, $commentId, $userId)
    {
        // 检查是否有权限删除
        $flag = $this->ableToDel($articleId, $commentId, $userId);
        if (!$flag) {
            throw new PermissionException([]);
        }

        // 更新评论状态
        $flag2 = CommentModel::create()->update([
            'status' => 0
        ], ['id' => $commentId]);
        if (!$flag2) {
            throw new SystemException([]);
        }

        // 回复数-1
        ArticleService::getInstance()->decr($articleId);
    }

    /**
     * 新增
     * @param  Array  $data  数据
     */
    private function add($data)
    {
        // 新增评论
        $model = new CommentModel($data);
        $id = $model->save();
        if (!$id) {
            throw new SystemException([]);
        }

        // 回复数+1
        ArticleService::getInstance()->incr($data['article_id']);
    }

    /**
     * 获取文章评论列表
     * @param  Int  $articleId  文章ID
     * @param  Int  $page       页码
     * @return Array
     */
    public function list($articleId, $page)
    {
        $limit = ESConfig::getInstance()->getConf('PAGE_SIZE');
        $offset = $limit * ($page - 1);

        // 分页查询模型
        $model = CommentModel::create()
            ->limit($offset, $limit)
            ->withTotalCount();

        // 列表数据
        $list = $model->with(['source'])->all(['article_id' => $articleId, 'status' => 1]);

        // 记录数
        $total = $model->lastQueryResult()->getTotalCount();

        // 返回结果
        $res = [
            'total_num' => $total,
            'total_page' => ceil($total / $limit),
            'content'   => $list
        ];

        return $res;
    }

    /**
     * 获取层主的用户ID
     * @param  Int  $commentId  评论楼层ID
     */
    public function getAuthor($commentId)
    {
        // 查询层主用户ID
        $authorId = CommentModel::create()
            ->get($commentId)
            ->val('user_id');

        // 查询不到，则表明文章肯定不存在
        if (!$authorId) {
            throw new ParameterException([]);
        }

        return $authorId;
    }

    /**
     * 判断是否有权限删除评论
     * @param  Int  $articleId  文章ID
     * @param  Int  $commentId  评论ID
     * @param  Int  $userId     用户ID
     */
    public function ableToDel($articleId, $commentId, $userId)
    {
        // 层主ID
        $commentAuthorId = $this->getAuthor($commentId);

        // 作者ID
        $articleAuthorId = ArticleService::getInstance()->getAuthor($articleId);

        if ($commentAuthorId == $userId  || $articleAuthorId == $userId) {
            return true;
        }

        return false;
    }
}
