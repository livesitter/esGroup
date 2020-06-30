<?php

namespace App\Service;

use App\Exception\SystemException;
use EasySwoole\Component\Singleton;
use App\Model\Report as ReportModel;

class Report
{
    use Singleton;

    /**
     * 新建反馈
     * @param  Array  $data  数据
     */
    public function add($data)
    {
        // 新增反馈
        $model = new ReportModel($data);
        $id = $model->save();
        if (!$id) {
            throw new SystemException([]);
        }
    }
}
