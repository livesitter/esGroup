<?php

namespace App\Service;

use EasySwoole\Jwt\Jwt;
use App\Exception\TokenException;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config as ESConfig;

class Token
{
    use Singleton;

    public function __construct()
    {
        $this->conf = ESConfig::getInstance()->getConf('JWT');
    }

    /**
     * 颁发token
     * @param  String  $userId    用户ID
     * @param  String  $name      用户名
     * @return String
     */
    public function getToken($userId, $name)
    {
        $jwtObject = Jwt::getInstance()
            ->setSecretKey($this->conf['secretKey'])
            ->publish();

        // 加密方式
        $jwtObject->setAlg('HMACSHA256');

        // 用户
        $jwtObject->setAud($name);

        // 过期时间
        $jwtObject->setExp(time() + 3600);

        // 发布时间
        $jwtObject->setIat(time());

        // 发行人
        $jwtObject->setIss($this->conf['iss']);

        // jwt id 用于标识该jwt
        $jwtObject->setJti(md5(time()));

        // 自定义数据
        $jwtObject->setData([
            'userId' => $userId
        ]);

        // 最终生成的token
        $token = $jwtObject->__toString();

        return $token;
    }

    /**
     * 校验token，成功则返回用户ID
     * @param  String  $token  token签名串
     * @return Int  
     */
    public function parseToken($token)
    {

        $jwtObject = Jwt::getInstance()
            ->setSecretKey($this->conf['secretKey'])
            ->decode($token);

        $status = $jwtObject->getStatus();

        if ($status != '1') {
            throw new TokenException([]);
        }

        $userId = $jwtObject->getData()['userId'];

        return $userId;
    }
}
