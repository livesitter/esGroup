<?php

namespace  App\Exception;

use App\Exception\ErrCode;
use EasySwoole\Http\Message\Status;

class UserException extends BaseException
{
    // HTTP状态码
    public $code = Status::CODE_NOT_FOUND;

    // 错误信息
    public $msg = '用户不存在';

    // 错误码
    public $errorCode = ErrCode::USER_NOT_FOUND;
}
