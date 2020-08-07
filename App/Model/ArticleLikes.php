<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * 文章点赞模型
 * Class ArticleLikes
 */
class ArticleLikes extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'article_likes';

    // 自动时间戳
    protected $autoTimeStamp = false;

}
