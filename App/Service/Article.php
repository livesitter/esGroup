<?php

namespace App\Service;

use App\Exception\ErrCode;
use EasySwoole\ORM\DbManager;
use App\Exception\BaseException;
use EasySwoole\EasySwoole\Logger;
use App\Exception\SystemException;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use App\Exception\ParameterException;
use App\Exception\PermissionException;
use App\Model\Article as ArticleModel;
use EasySwoole\EasySwoole\Config as ESConfig;
use App\Service\WordMatch as WordMatchService;
use App\Model\ArticleLikes as ArticleLikesModel;
use App\Model\ArticleAuthority as ArticleAuthorityModel;
use App\Model\ArticleWithUserInfo as ArticleWithUserInfoModel;

class Article
{
    use Singleton;

    /**
     * 发布文章（主方法）
     * @param  Array  $data  文章内容
     */
    public function add($data)
    {

        // 检查是否可以发布文章
        $this->ableToPublish($data['user_id']);

        // 检查内容
        WordMatchService::getInstance()->check($data['content']);

        // 新增文章
        $model = new ArticleModel($data);
        $articleId = $model->save();
        if (!$articleId) {
            throw new SystemException([]);
        }

        return $articleId;
    }

    /**
     * 获取文章内容（主方法）
     * @param  Int  $articleId  文章ID
     */
    public function detail($articleId)
    {
        // 文章内容
        $content = ArticleWithUserInfoModel::create()->with(['comment'])->where('id', $articleId)->get();
        if (!$content) {
            throw new ParameterException(['code' => Status::CODE_NOT_FOUND, 'msg' => '未找到文章']);
        }

        return $content;
    }

    /**
     * 获取文章隐藏内容（主方法）
     * @param  Int  $articleId  文章ID
     * @param  Int  $userId     用户ID
     */
    public function hiddenText($articleId, $userId)
    {
        // 是否有隐藏内容权限
        $flag = $this->ableToReadHidden($articleId, $userId);
        if (!$flag) {
            return '';
        }

        $content = ArticleModel::create()->get($articleId)->val('hid');
        return $content;
    }

    /**
     * 获取文章列表
     * @param  Int  $categoryId  分类ID
     * @param  Int  $userId      用户ID
     * @param  Int  $page        页码
     * @return Array
     */
    public function list($categoryId, $userId, $page)
    {
        // 返回结果
        $res = ArticleModel::create()->getList($categoryId, $userId, $page ?? 1);

        return $res;
    }

    /**
     * 获取用户文章列表
     * @param  Int  $userId  用户ID
     * @param  Int  $page    页码
     * @return Array
     */
    public function listOfUser($userId, $page)
    {
        $limit = ESConfig::getInstance()->getConf('PAGE_SIZE');
        $page = $page ?? 1;
        $offset = $limit * ($page - 1);

        // 分页查询模型
        $model = ArticleModel::create()
            ->limit($offset, $limit)
            ->withTotalCount();

        // 列表数据
        $list = $model->with(['user'])->all(['user_id' => $userId]);

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
     * 删除文章
     * @param  Int  $articleId  文章ID
     * @param  Int  $userId     用户ID
     */
    public function del($articleId, $userId)
    {
        $flag = ArticleModel::create()->update(['status' => 0], [
            'id'      => $articleId,
            'user_id' => $userId
        ]);

        if (!$flag) {
            throw new SystemException([]);
        }
    }

    /**
     * 点赞文章
     * @param  Int  $articleId  文章ID
     * @param  Int  $userId     用户ID
     */
    public function like($articleId, $userId)
    {
        // 检查有无点过赞
        $this->checkLikes($articleId, $userId);

        try {
            // 开启事务
            DbManager::getInstance()->startTransaction();

            // 增加文章点赞数
            $flag = ArticleModel::create()->update(['likes' => QueryBuilder::inc(1)], [
                'id'      => $articleId
            ]);

            // 新增点赞
            $data = [
                'article_id' => $articleId,
                'user_id'    => $userId
            ];
            $model = new ArticleLikesModel($data);
            $likeId = $model->save();
        } catch (\Throwable  $e) {

            // 回滚事务
            DbManager::getInstance()->rollback();

            // 记录错误
            Logger::getInstance()->error('like article, msg:' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE));

            throw new SystemException([]);
        } finally {

            // 提交事务
            DbManager::getInstance()->commit();
        }
    }

    /**
     * 能否发布文章
     * @param  Int  $userId  用户ID
     */
    public function ableToPublish($userId)
    {
        // 上一篇文章
        $lastArticle = ArticleModel::create()->where(['id' => $userId])->order('id', 'desc')->get();

        // 时间差
        $timeDiff = time() - strtotime($lastArticle['created_at']);

        if ($timeDiff < ESConfig::getInstance()->getConf('ARTICLE')['wait']) {
            throw new BaseException([
                'code'  => Status::CODE_FORBIDDEN,
                'msg'   => ESConfig::getInstance()->getConf('ARTICLE')['wran'],
                'error_code' => ErrCode::USER_STATUS_ERROR
            ]);
        }
    }

    /**
     * 是否有文章隐藏内容查看权限
     * @param  Int  $articleId  文章ID
     * @param  Int  $userId     用户ID
     * @return Boolean
     */
    public function ableToReadHidden($articleId, $userId)
    {
        // 查看文章权限表
        $authority = ArticleAuthorityModel::create()->where([
            'user_id' => $userId,
            'article_id' => $articleId
        ])->get();

        // 文章作者ID
        $authorId = $this->getAuthor($articleId);

        if ($authority || $authorId == $userId) {
            return true;
        }

        return false;
    }

    /**
     * 获取作者的用户ID
     * @param  Int  $articleId  文章ID
     */
    public function getAuthor($articleId)
    {
        // 查询楼主用户ID
        $authorId = ArticleModel::create()
            ->where('status', 1)
            ->get($articleId)
            ->val('user_id');

        // 查询不到，则表明文章肯定不存在
        if (!$authorId) {
            throw new ParameterException([
                'code' => Status::CODE_NOT_FOUND,
                'msg'  => '文章不存在',
                'error_code' => ErrCode::PARAM_ERROR
            ]);
        }

        return $authorId;
    }

    /**
     * 回复数自增
     * @param  Int  $articleId  文章ID
     */
    public function incr($articleId)
    {
        ArticleModel::create()->update([
            'comment_sum' => QueryBuilder::inc(1),
        ], [
            'id' => $articleId
        ]);
    }

    /**
     * 回复数自减
     * @param  Int  $articleId  文章ID
     */
    public function decr($articleId)
    {
        ArticleModel::create()->update([
            'comment_sum' => QueryBuilder::dec(1),
        ], [
            'id' => $articleId
        ]);
    }

    /**
     * 检查是否点过赞
     * @param  Int  $articleId  文章ID
     */
    public function checkLikes($articleId, $userId)
    {
        $liked = ArticleLikesModel::create()->where([
            'user_id' => $userId,
            'article_id' => $articleId
        ])->get();

        if ($liked) {
            throw new PermissionException([
                'msg' => '请勿重复点赞'
            ]);
        }
    }
}
