<?php

namespace App\HttpController;

use App\Model\User as UserModel;
use App\Exception\SystemException;
use EasySwoole\Http\Message\Status;
use App\Service\User as UserService;
use App\Service\Mail as MailService;
use App\Service\Token as TokenService;
use EasySwoole\Component\Context\ContextManager;

class User extends ApiBase
{
    protected $checkAction = [];

    /**
     * 发送验证码到邮箱
     * @Method(allow={POST})
     * @Param(name="address",from={POST},notEmpty="不能为空",email="不是合法邮箱地址")
     */
    public function sendCaptcha($address)
    {
        MailService::getInstance()->sendCode($address);
        $this->writeJson(Status::CODE_OK, null, '发送成功');
    }

    /**
     * 注册用户
     * @Method(allow={POST})
     * @InjectParamsContext(key="data")
     * @Param(name="captcha",from={POST},notEmpty="不能为空",length={6,"请输入6位数字或者英文字母"})
     * @Param(name="name",from={POST},notEmpty="不能为空",betweenLen={1,8,"请输入1~8个字"})
     * @Param(name="pwd",from={POST},notEmpty="不能为空",betweenLen={8,15,"请输入8~15个字符"})
     * @Param(name="mail",from={POST},notEmpty="不能为空",email="不是合法邮箱地址")
     */
    public function register($captcha, $name, $pwd, $mail)
    {
        // 请求参数
        $data = ContextManager::getInstance()->get('data');

        $UserService = UserService::getInstance();

        // 检查验证码
        $UserService->checkCaptcha($mail, $captcha);

        // 检查有无注册
        $UserService->checkRegisted($mail);

        // 新增用户
        $model = new UserModel($data);
        $userId = $model->save();
        if (!$userId) {
            throw new SystemException([]);
        }

        // 颁发token
        $token = TokenService::getInstance()->getToken($userId, $name);
        $this->writeJson(Status::CODE_OK, ['token' => $token], 'success');
    }

    /**
     * 登录
     * @Method(allow={POST})
     * @InjectParamsContext(key="data")
     * @Param(name="account",from={POST},notEmpty="不能为空",betweenLen={1,8,"请输入1~8个字"})
     * @Param(name="pwd",from={POST},notEmpty="不能为空",betweenLen={8,15,"请输入8~15个字符"})
     */
    public function login($account, $pwd)
    {
        // 检查密码
        $userInfo = UserService::getInstance()->checkPwd($account, $pwd);

        // 颁发token
        $token = TokenService::getInstance()->getToken($userInfo['id'], $userInfo['name']);
        $this->writeJson(Status::CODE_OK, ['token' => $token], 'success');
    }
}
