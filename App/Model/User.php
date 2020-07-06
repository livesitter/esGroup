<?php

namespace App\Model;

use EasySwoole\Utility\Hash;
use EasySwoole\EasySwoole\Config;
use EasySwoole\ORM\AbstractModel;

/**
 * 用户模型
 * Class User
 */
class User extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'user';

    // 自动时间戳
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /**
     * $value mixed 是原值
     * $data  array 是当前model所有的值 
     */
    protected function setPwdAttr($value, $data)
    {
        $salt = Config::getInstance()->getConf('APP_SALT');
        return Hash::makePasswordHash($value . $salt);
    }

}
