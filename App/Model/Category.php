<?php

namespace App\Model;

use EasySwoole\Utility\Hash;
use EasySwoole\EasySwoole\Config;
use EasySwoole\ORM\AbstractModel;

/**
 * 栏目模型
 * Class Category
 */
class Category extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'category';
}
