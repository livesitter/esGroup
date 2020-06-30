<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * 文章模型
 * Class Article
 */
class Article extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'article';

    // 自动时间戳
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 定义一对一关联用户表模型
    public function user()
    {
        return $this->hasOne(User::class, null, 'user_id', 'id');
    }
}
