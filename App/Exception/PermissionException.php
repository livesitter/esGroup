<?php

namespace  App\Exception;

use App\Exception\ErrCode;
use EasySwoole\Http\Message\Status;

class PermissionException extends BaseException
{
    // HTTP状态码
    public $code = Status::CODE_FORBIDDEN;

    // 错误信息
    public $msg = '无权限';

    // 错误码
    public $errorCode = ErrCode::PERMISSION_ERROR;
}
