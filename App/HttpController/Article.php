<?php

namespace App\HttpController;

use App\Exception\ParameterException;
use EasySwoole\Http\Message\Status;
use App\Service\Article as ArticleService;
use EasySwoole\Component\Context\ContextManager;

class Article extends ApiBase
{
    // 需要检查用户状态的方法
    protected $checkAction = ['newArticle', 'delArticle', 'articleHiddenText', 'userArticleList'];

    /**
     * 发布新文章
     * @Method(allow={POST})
     * @InjectParamsContext(key="data")
     * @Param(name="category_id",from={POST},notEmpty="不能为空",between={1,10,"非法栏目ID"})
     * @Param(name="content",from={POST},optional="", lengthMax={3000,"文章字数最多不超过3000字"})
     * @Param(name="image",from={POST},optional="", json="image不是合法类型",lengthMax={3000,"文章字数最多不超过3000字"})
     * @Param(name="hid",from={POST},optional="", lengthMax={3000,"隐藏内容最多不超过3000字"})
     * @Param(name="price",from={POST},optional="", integer="非法price值")
     */
    public function newArticle($categoryId, $content, $image, $hid, $price)
    {
        // 请求参数
        $data = ContextManager::getInstance()->get('data');

        // 文字和图片均为空，返回提示
        if (!isset($data['image']) && !isset($data['content'])) {
            throw new ParameterException(['msg' => '图片和文字，至少填写其中一个']);
        }

        // 附加用户ID
        $data['user_id'] = $this->userId;

        // 发布文章
        $articleId = ArticleService::getInstance()->add($data);

        $this->writeJson(Status::CODE_OK, ['id' => $articleId], 'success');
    }

    /**
     * 删除文章
     * @Method(allow={DELETE})
     * @Param(name="id",from={GET},notEmpty="不能为空",integer="非法ID")
     */
    public function delArticle($id)
    {
        ArticleService::getInstance()->del($id, $this->userId);

        $this->writeJson(Status::CODE_OK, null, 'success');
    }

    /**
     * 文章详情
     * @Method(allow={GET})
     * @Param(name="id",from={GET},notEmpty="不能为空",integer="非法ID")
     */
    public function articleDetail($id)
    {
        $content = ArticleService::getInstance()->detail($id);

        $this->writeJson(Status::CODE_OK, $content, 'success');
    }

    /**
     * 文章隐藏内容
     * @Method(allow={GET})
     * @Param(name="id",from={GET},notEmpty="不能为空",integer="非法ID")
     */
    public function articleHiddenText($id)
    {
        $content = ArticleService::getInstance()->hiddenText($id, $this->userId);

        $this->writeJson(Status::CODE_OK, $content, 'success');
    }

    /**
     * 分类文章列表
     * @Method(allow={GET})
     * @Param(name="category_id",from={GET},optional="",notEmpty="不能为空",between={1,10,"非法栏目ID"})
     * @Param(name="user_id",from={GET},optional="",notEmpty="不能为空",between={1,10,"非法栏目ID"})
     * @Param(name="page",from={GET},optional="",integer="非法page参数",min={1,"page参数最小为1"})
     */
    public function articleList($categoryId, $userId, $page)
    {
        // 文字和图片均为空，返回提示
        if (!$categoryId && !$userId) {
            throw new ParameterException(['msg' => '分类ID和用户ID至少传一个']);
        }

        $res = ArticleService::getInstance()->list($categoryId, $userId, $page);

        $this->writeJson(Status::CODE_OK, $res, 'success');
    }

    /**
     * 用户文章列表
     * @Param(name="page",from={GET},integer="非法page参数",min={1,"page参数最小为1"}
     */
    public function userArticleList($page)
    {
        $res = ArticleService::getInstance()->listOfUser($this->userId, $page);

        $this->writeJson(Status::CODE_OK, $res, 'success');
    }
}
