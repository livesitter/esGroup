<?php

namespace App\Service;

use EasySwoole\Component\Singleton;
use App\Exception\WordMatchException;
use EasySwoole\WordsMatch\WordsMatchClient;

class WordMatch
{
    use Singleton;

    /**
     * 检查内容
     * @param  String  $content  内容
     * @return Boolean
     */
    public function check($content)
    {
        $res = WordsMatchClient::getInstance()->search($content);
        if ($res) {
            throw new WordMatchException([]);
        }
    }
}
