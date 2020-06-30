<?php

namespace App\Exception;

use App\Exception\ErrCode;
use EasySwoole\Http\Message\Status;

class ParameterException extends BaseException
{
    // HTTP状态码
    public $code = Status::CODE_BAD_REQUEST;

    // 错误信息
    public $msg = '参数错误';

    // 错误码
    public $errorCode = ErrCode::PARAM_ERROR;
}
