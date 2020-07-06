<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;
use EasySwoole\EasySwoole\Config as ESConfig;

/**
 * 文章模型
 * Class Article
 */
class Article extends AbstractModel
{
    /**
     * @var string 
     */
    protected $tableName = 'article';

    /**
     * @var array 
     */
    protected $hidKeys = ['price', 'status', 'pwd', 'mail', 'status', 'money'];

    // 自动时间戳
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 定义一对一关联用户表模型
    public function author()
    {
        return $this->hasOne(User::class, null, 'user_id', 'id');
    }

    /**
     * 获取文章详情
     * @param  Int  $id  文章ID
     */
    public function getDetail($id)
    {
        $content = $this->with(['author'])
            ->where('status', 1)
            ->field([
                'id',
                'user_id',
                'category_id', 
                'content', 
                'image', 
                'price', 
                'likes', 
                'comment_sum', 
                'created_at', 
                'updated_at'
            ])
            ->get($id);
        if (!$content) {
            return [];
        }

        // 作者信息
        $authorInfo = $content['author']->toArray();

        // 删除敏感内容
        unset($authorInfo['pwd']);
        unset($authorInfo['mail']);

        $content['author'] = $authorInfo;
        return $content;
    }

    /**
     * 获取分类文章列表
     * @param  Int  $categoryId  分类ID
     * @param  Int  $page        页码
     */
    public function getList($categoryId, $page)
    {
        $limit = ESConfig::getInstance()->getConf('PAGE_SIZE');
        $offset = $limit * ($page - 1);

        // 分页查询模型
        $model = $this->limit($offset, $limit)->withTotalCount();

        // 列表数据
        $list = $model->with(['author'])
            ->field([
                'id', 
                'user_id', 
                'category_id', 
                'content', 
                'image', 
                'price', 
                'likes', 
                'comment_sum', 
                'created_at', 
                'updated_at'])
            ->all(['category_id' => $categoryId, 'status' => 1]);

        // 记录数
        $total = $model->lastQueryResult()->getTotalCount();

        if ($list) {

            foreach ($list as $content) {
                // 作者信息
                $authorInfo = $content['author']->toArray();

                // 删除敏感内容
                unset($authorInfo['pwd']);
                unset($authorInfo['mail']);

                $content['author'] = $authorInfo;
            }
        }

        // 返回结果
        $res = [
            'total_num' => $total,
            'total_page' => ceil($total / $limit),
            'content'   => $list
        ];

        return $res;
    }
}
