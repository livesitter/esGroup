<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * 评论模型
 * Class Comment
 */
class Comment extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'comment';

    // 自动时间戳
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 定义一对一关联
    public function source()
    {
        return $this->hasOne(Comment::class, null, 'comment_id', 'id');
    }
}
