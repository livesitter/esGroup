<?php

namespace App\HttpController;

use App\Exception\ErrCode;
use App\Exception\UserException;
use App\Exception\TokenException;
use EasySwoole\Http\Message\Status;
use App\Service\User as UserService;
use App\Service\Token as TokenService;
use EasySwoole\Component\Context\ContextManager;

class ApiBase extends Base
{

    protected function onRequest(?string $action): ?bool
    {
        // 只检查需要检查的方法
        if (!in_array($action, $this->checkAction)) {
            return true;
        }

        // 头部token字段
        $header = $this->request()->getHeaders();
        $token = $header['token'][0];
        if (!$token) {
            throw new TokenException([]);
        }

        // 校验token
        $userId = TokenService::getInstance()->parseToken($token);
        if (!$userId) {
            throw new TokenException([]);
        }

        // 判断用户状态是否正常
        $status = UserService::getInstance()->checkStatus($userId);
        if (!$status) {
            throw new UserException([
                'code'  => Status::CODE_FORBIDDEN,
                'msg'   => '用户状态异常',
                'error_code' => ErrCode::USER_STATUS_ERROR
            ]);
        }

        $this->userId = $userId;

        return true;
    }
}
