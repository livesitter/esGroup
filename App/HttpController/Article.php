<?php

namespace App\HttpController;

use App\Exception\SystemException;
use EasySwoole\Http\Message\Status;
use App\Service\User as UserService;
use App\Exception\ParameterException;
use App\Model\Article as ArticleModel;
use App\Service\Article as ArticleService;
use EasySwoole\Component\Context\ContextManager;
use App\Model\ArticleAuthority as ArticleAuthorityModel;

class Article extends ApiBase
{

    protected $checkAction = ['newArticle', 'delArticle'];

    /**
     * 发布新文章
     * @Method(allow={POST})
     * @InjectParamsContext(key="data")
     * @Param(name="category_id",from={POST},notEmpty="不能为空",between={1,10,"非法栏目ID"})
     * @Param(name="content",from={POST},lengthMax={3000,"文章字数最多不超过3000字"})
     * @Param(name="image",from={POST},json="image不是合法类型",lengthMax={3000,"文章字数最多不超过3000字"})
     * @Param(name="hidden",from={POST},lengthMax={3000,"隐藏内容最多不超过3000字"})
     * @Param(name="price",from={POST},integer="非法price值",lengthMax={3000,"隐藏内容最多不超过3000字"})
     */
    public function newArticle($categoryId, $content, $image, $price)
    {
        // 请求参数
        $data = ContextManager::getInstance()->get('data');

        // 文字和图片均为空，返回提示
        if (!isset($data['image']) && !isset($data['content'])) {
            $this->writeJson(Status::CODE_FORBIDDEN, null, '图片和文字，至少填写其中一个');
        }

        // 附加用户ID
        $data['user_id'] = $this->userId;

        // 检查是否可以发布文章
        ArticleService::getInstance()->ableToPublish($this->userId);

        // 新增文章
        $model = new ArticleModel($data);
        $articleId = $model->save();
        if (!$articleId) {
            throw new SystemException([]);
        }

        $this->writeJson(Status::CODE_OK, ['id' => $articleId], 'success');
    }

    /**
     * 删除文章
     * @Method(allow={DELETE})
     * @Param(name="id",from={GET},notEmpty="不能为空",integer="非法ID")
     */
    public function delArticle($id)
    {
        $flag = ArticleModel::create()->update(['status' => 0], [
            'id'      => $id,
            'user_id' => $this->userId,
        ]);

        if (!$flag) {
            throw new SystemException([]);
        }

        $this->writeJson(Status::CODE_OK, null, 'success');
    }

    /**
     * 文章详情
     * @Method(allow={GET})
     * @Param(name="id",from={GET},notEmpty="不能为空",integer="非法ID")
     */
    public function detail($id)
    {
        // 文章内容
        $content = ArticleModel::create()->with(['author'])->get($id);
        if (!$content) {
            throw new ParameterException(['code' => Status::CODE_NOT_FOUND]);
        }

        // 不返回隐藏内容
        unset($content['hidden']);

        $this->writeJson(Status::CODE_OK, $content, 'success');
    }

    /**
     * 文章隐藏内容
     * @Method(allow={GET})
     * @Param(name="id",from={GET},notEmpty="不能为空",integer="非法ID")
     */
    public function hiddenTxt($id)
    {
        // 查看文章权限表
        $authority = ArticleAuthorityModel::create()->where([
            'user_id' => $this->userId
        ])->get($id);
        if (!$authority) {
            $this->writeJson(Status::CODE_OK, '', 'success');
        }

        // 文章内容
        $content = ArticleModel::create()->get($id)->val('hidden');

        $this->writeJson(Status::CODE_OK, $content, 'success');
    }

    /**
     * 文章列表
     * @Method(allow={GET})
     * @Param(name="category_id",from={GET},notEmpty="不能为空",between={1,10,"非法栏目ID"})
     * @Param(name="page",from={GET},integer="非法page参数",min={1,"page参数最小为1"})
     */
    public function list($categoryId, $page = 1)
    {
        $limit = 1;
        $offset = $limit * ($page - 1);

        // 分页查询模型
        $model = ArticleModel::create()
            ->limit($offset, $limit)
            ->withTotalCount();

        // 列表数据
        $list = $model->with(['user'])->all(['category_id' => $categoryId]);

        // 记录数
        $total = $model->lastQueryResult()->getTotalCount();

        // 返回结果
        $res = [
            'total_num' => $total,
            'total_page' => ceil($total / $limit),
            'content'   => $list
        ];

        $this->writeJson(Status::CODE_OK, $res, 'success');
    }
}
