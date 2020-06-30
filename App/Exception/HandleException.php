<?php

namespace App\Exception;

use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;

/**
 * 适用于 http 服务中的 mvc模式 全局异常捕获
 */
class HandleException
{
    public static function handle(\Throwable $exception, Request $request, Response $response)
    {
        // 初始化
        $code = Status::CODE_BAD_REQUEST;
        $msg = '参数错误';
        $errorCode = ErrCode::PARAM_ERROR;

        // 自定义错误
        if ($exception instanceof BaseException) {
            $code = $exception->code;
            $errorCode = $exception->errorCode;
            $msg = $exception->msg;
            // 注解校验错误
        } else if ($exception instanceof ParamValidateError) {
            $msg = $exception->getValidate()->getError()->getErrorRuleMsg();
            // 其他错误（开发环境）
        } else if (Core::getInstance()->isDev()) {
            $code = Status::CODE_INTERNAL_SERVER_ERROR;
            $msg = $exception->getMessage();
            $errorCode = ErrCode::SYSTEM_ERROR;
            // 其他错误（正式环境）
        } else {
            $code = Status::CODE_INTERNAL_SERVER_ERROR;
            $msg = '系统错误';
            $errorCode = ErrCode::SYSTEM_ERROR;
        }

        // 写入日志
        self::recordErrorLog($exception, $msg);

        // 返回错误
        $data['code'] = $code;
        $data['msg'] = $msg;
        $data['errorCode'] = $errorCode;
        $result = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $response->withHeader("Content-Type", "application/json;charset=UTF-8")
            ->withHeader("Access-Control-Allow-Origin", "*")
            ->write($result);
    }

    // 服务器内部错误信息写入日志
    private static function recordErrorLog(\Throwable $exception, $msg)
    {
        Logger::getInstance()->error($msg . '\t' . $exception->getTraceAsString());
    }
}
