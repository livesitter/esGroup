<?php

namespace App\HttpController;

use EasySwoole\Http\Message\Status;
use App\Service\Report as ReportService;
use EasySwoole\Component\Context\ContextManager;

class Report extends ApiBase
{
    // 需要检查用户状态的方法
    protected $checkAction = ['index'];

    /**
     * 反馈
     * @Method(allow={POST})
     * @InjectParamsContext(key="data")
     * @Param(name="article_id",from={POST},notEmpty="不能为空",integer="非法id值")
     * @Param(name="content",from={POST},notEmpty="不能为空",lengthMax={100,"内容最多不超过100字"}")
     */
    public function index($articleId, $content)
    {
        // 请求参数
        $data = ContextManager::getInstance()->get('data');

        // 附加用户ID
        $data['user_id'] = $this->userId;

        // 新增反馈
        ReportService::getInstance()->add($data);

        $this->writeJson(Status::CODE_OK, [], 'success');
    }
}
