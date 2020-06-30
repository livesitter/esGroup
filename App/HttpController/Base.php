<?php

namespace App\HttpController;

use EasySwoole\Validate\Validate;
use EasySwoole\HttpAnnotation\AnnotationTag\Di;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Context;
use EasySwoole\HttpAnnotation\AnnotationTag\CircuitBreaker;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;

class Base extends AnnotationController
{

    /**
     * @Param(name="account",from={GET,POST},notEmpty="不能为空")
     * @Param(name="session",notEmpty="不能为空")
     */
    public function index()
    {
        $this->actionNotFound('index');
    }
}
