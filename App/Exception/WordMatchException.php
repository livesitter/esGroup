<?php

namespace  App\Exception;

use App\Exception\ErrCode;
use EasySwoole\Http\Message\Status;

class WordMatchException extends BaseException
{
    // HTTP状态码
    public $code = Status::CODE_FORBIDDEN;

    // 错误信息
    public $msg = '输入内容包含敏感词汇，请修改后再重新提交';

    // 错误码
    public $errorCode = ErrCode::SENSITIVE_ERROR;
}