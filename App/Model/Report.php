<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * 反馈模型
 * Class Report
 */
class Report extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'report';

    // 自动时间戳
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}
