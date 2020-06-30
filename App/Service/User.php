<?php

namespace App\Service;

use App\Exception\ErrCode;
use EasySwoole\Utility\Hash;
use EasySwoole\RedisPool\Redis;
use App\Model\User as UserModel;
use App\Exception\UserException;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use App\Exception\ParameterException;

class User
{
    use Singleton;

    /**
     * 检查验证码
     * @param  String  $address   邮箱地址
     * @param  String  $captcha   验证码
     */
    public function checkCaptcha($address, $captcha)
    {
        // defer方式获取redis连接
        $redis = Redis::defer('redis');

        // 获取验证码
        $info = $redis->get($address);
        if (!$info) {
            throw new ParameterException([
                'code' => Status::CODE_FORBIDDEN,
                'msg'  => '验证码已失效，请重新获取'
            ]);
        }

        // 对比验证码
        if (strtolower($info) != strtolower($captcha)) {
            throw new ParameterException([
                'code' => Status::CODE_FORBIDDEN,
                'msg'  => '验证码错误，请重新输入'
            ]);
        }

        // 删除键值
        $redis->del($address);
    }

    /**
     * 检测有无该用户
     * @param  String  $address   邮箱地址
     */
    public function checkRegisted($address)
    {
        // 有无注册过
        $user = UserModel::create()->where(['mail' => $address])->get();
        if ($user) {
            throw new UserException([
                'msg'      => '请勿重复注册',
                'code'     => Status::CODE_FORBIDDEN,
                'error_code' => ErrCode::USER_REGISTED
            ]);
        }
    }

    /**
     * 检查密码
     * @param  String  $account   用户名 or 邮箱
     * @param  String  $pwd       密码
     */
    public function checkPwd($account, $pwd)
    {
        // 用户信息
        $userInfo = UserModel::create()->where(" (name = $account or mail = $account) ")->get();

        // 盐
        $salt = Config::getInstance()->getConf('APP_SALT');

        // 加密后的密码
        $pwd =  Hash::makePasswordHash($pwd . $salt);

        // 密码错误
        if ($pwd != $userInfo['pwd']) {
            throw new ParameterException([
                'code' => Status::CODE_FORBIDDEN,
                'msg' => '密码错误'
            ]);
        }

        return $userInfo;
    }

    /**
     * 检查用户状态
     * @param  Int  $userId   用户ID
     */
    public function checkStatus($userId)
    {
        $status = UserModel::create()->where(['id' => $userId])->val('status');
        return $status;
    }
}
