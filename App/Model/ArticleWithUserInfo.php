<?php

namespace App\Model;

use App\Model\Comment;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\EasySwoole\Config as ESConfig;

/**
 * 文章视图
 * Class ArticleWithUserInfo
 */
class ArticleWithUserInfo extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'article_with_userdata';

    // 定义一对多关联评论表模型
    public function comment()
    {
        return $this->hasMany(Comment::class, function(QueryBuilder $query){
            $query->where('status', 1);
            return $query;
        }, 'id', 'article_id');
    }
}
