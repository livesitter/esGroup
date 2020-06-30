<?php

namespace App\HttpController;

use EasySwoole\Http\Message\Status;
use App\Model\Category as CategoryModel;

class Category extends ApiBase
{
    protected $checkAction = ['list'];

    /**
     * 获取社区栏目
     */
    public function list()
    {
        $list = CategoryModel::create()->all();
        $this->writeJson(Status::CODE_OK, $list, 'success');
    }
}
