<?php

namespace  App\Exception;

use App\Exception\ErrCode;
use EasySwoole\Http\Message\Status;

class SystemException extends BaseException
{
    // HTTP状态码
    public $code = Status::CODE_INTERNAL_SERVER_ERROR;

    // 错误信息
    public $msg = '系统错误';

    // 错误码
    public $errorCode = ErrCode::SYSTEM_ERROR;
}
