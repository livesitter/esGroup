<?php

namespace App\Service;

use App\Exception\ErrCode;
use EasySwoole\Utility\Hash;
use EasySwoole\RedisPool\Redis;
use App\Model\User as UserModel;
use App\Exception\UserException;
use EasySwoole\EasySwoole\Config;
use App\Exception\SystemException;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use App\Service\Mail as MailService;
use App\Exception\ParameterException;
use App\Service\Token as TokenService;

class User
{
    use Singleton;

    /**
     * 注册用户(主方法)
     * @param  String  $mail      邮箱地址
     * @param  String  $captcha   验证码
     * @param  String  $name      昵称
     * @param  Array   $data      用户数据
     */
    public function register($mail, $captcha, $name, $data)
    {
        // 检查验证码
        $this->checkCaptcha($mail, $captcha);

        // 检查有无注册
        $this->checkRegisted($mail);

        // 新增用户
        $model = new UserModel($data);
        $userId = $model->save();
        if (!$userId) {
            throw new SystemException([]);
        }

        // 颁发token
        $token = TokenService::getInstance()->getToken($userId, $name);

        return $token;
    }

    /**
     * 登录（主方法）
     * @param  String  $account  账户
     * @param  String  $pwd      密码
     */
    public function login($account, $pwd)
    {
        // 检查密码
        $userInfo = $this->checkPwd($account, $pwd);

        // 颁发token
        $token = TokenService::getInstance()->getToken($userInfo['id'], $userInfo['name']);

        return $token;
    }

    /**
     * 检查验证码
     * @param  String  $mail      邮箱地址
     * @param  String  $captcha   验证码
     */
    public function checkCaptcha($mail, $captcha)
    {
        // defer方式获取redis连接
        $redis = Redis::defer('redis');

        // 获取验证码
        $info = $redis->get($mail);
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
        $redis->del($mail);
    }

    /**
     * 检测有无该用户
     * @param  String  $mail   邮箱地址
     */
    public function checkRegisted($mail)
    {
        // 有无注册过
        $user = UserModel::create()->where(['mail' => $mail])->get();
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
        $userInfo = UserModel::create()->where(" name = '$account' or mail = '$account' ")->get();

        // 对比加密值
        $flag = Hash::validatePasswordHash($pwd, $userInfo['pwd']);

        // 密码错误
        if (!$flag) {
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
