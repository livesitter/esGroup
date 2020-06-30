<?php

namespace  App\Exception;

use Throwable;
use App\Exception\ErrCode;
use EasySwoole\Http\Message\Status;

class  BaseException extends \Exception
{
    // 默认HTTP状态码
    public $code = Status::CODE_BAD_REQUEST;

    // 默认错误提示信息
    public $msg = '参数错误';

    // 默认业务错误码
    public $errorCode = ErrCode::PARAM_ERROR;

    public function __construct(array $params)
    {
        // 判断是否数组
        if (!is_array($params)) {
            return;
        }

        // 判断是否有code、errorCode、msg这些参数代入
        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }
        if (array_key_exists('msg', $params)) {
            $this->msg = $params['msg'];
        }
        if (array_key_exists('error_code', $params)) {
            $this->errorCode = $params['errorCode'];
        }
    }
}
