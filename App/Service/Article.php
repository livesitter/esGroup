<?php

namespace App\Service;

use App\Exception\ErrCode;
use App\Exception\BaseException;
use App\Exception\SystemException;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use App\Exception\ParameterException;
use App\Model\Article as ArticleModel;
use EasySwoole\EasySwoole\Config as ESConfig;
use App\Model\ArticleAuthority as ArticleAuthorityModel;

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
        $this->ableToPublish($this->userId);

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
        $content = ArticleModel::create()->with(['author'])->get($articleId);
        if (!$content) {
            throw new ParameterException(['code' => Status::CODE_NOT_FOUND]);
        }

        // 删除隐藏内容
        unset($content['hidden']);

        return $content;
    }

    /**
     * 获取文章内容（主方法）
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

        $content = ArticleModel::create()->get($articleId)->val('hidden');
        return $content;
    }

    /**
     * 获取分类文章列表
     * @param  Int  $categoryId  分类ID
     * @param  Int  $page        页码
     * @return Array
     */
    public function list($categoryId, $page)
    {
        $limit = 1;
        $offset = $limit * ($page - 1);

        // 分页查询模型
        $model = ArticleModel::create()
            ->limit($offset, $limit)
            ->withTotalCount();

        // 列表数据
        $list = $model->with(['user'])->all(['category_id' => $categoryId]);

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

        if (!$authority) {
            return false;
        }

        return true;
    }
}
