<?php

namespace App\Exception;

class ErrCode
{
    const SYSTEM_ERROR      = 9999;
    const PARAM_ERROR       = 1000;
    const USER_NOT_FOUND    = 1001;
    const USER_REGISTED     = 1002;
    const USER_STATUS_ERROR = 1003;
    const TOKEN_ERROR       = 1004;
    const CAPTCHA_ERROR     = 1005;
    const PERMISSION_ERROR  = 1006;
}
