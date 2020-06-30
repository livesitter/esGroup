<?php

namespace App\Service;

use App\Exception\BaseException;
use App\Exception\ErrCode;
use App\Model\Article as ArticleModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\EasySwoole\Config as ESConfig;

class Article
{
    use Singleton;

    /**
     * 能否发布文章
     * @param  Int  $userId  用户ID
     */
    public function ableToPublish($userId)
    {
        // 上一篇文章
        $lastArticle = ArticleModel::create()->where(['id' => $userId])->order('id', 'desc')->get();

        // 时间差
        $timeDiff = time() - strtotime($lastArticle['created_at']);

        if ($timeDiff < ESConfig::getInstance()->getConf('ARTICLE')['wait']) {
            throw new BaseException([
                'code'  => Status::CODE_FORBIDDEN,
                'msg'   => ESConfig::getInstance()->getConf('ARTICLE')['wran'],
                'error_code' => ErrCode::USER_STATUS_ERROR
            ]);
        }
    }
}
