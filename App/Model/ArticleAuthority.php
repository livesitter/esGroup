<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * 文章权限模型
 * Class ArticleAuthority
 */
class ArticleAuthority extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'article_authority';

    // 自动时间戳
    protected $autoTimeStamp = false;

}
