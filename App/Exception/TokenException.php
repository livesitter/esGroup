<?php

namespace App\Exception;

use App\Exception\ErrCode;
use EasySwoole\Http\Message\Status;

class TokenException extends BaseException
{
    // HTTP状态码
    public $code = Status::CODE_FORBIDDEN;

    // 错误信息
    public $msg = 'TOKEN无效或已过期';

    // 错误码
    public $errorCode = ErrCode::TOKEN_ERROR;
}
