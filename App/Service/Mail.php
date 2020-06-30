<?php

namespace App\Service;

use App\Exception\ErrCode;
use EasySwoole\Smtp\Mailer;
use EasySwoole\Utility\Random;
use EasySwoole\RedisPool\Redis;
use App\Exception\BaseException;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Attach;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\EasySwoole\Config as ESConfig;

class Mail
{
    use Singleton;

    /**
     * @var MailerConfig $config
     */
    protected $config;

    public function __construct()
    {
        $config = new MailerConfig();
        $conf = ESConfig::getInstance()->getConf('STMP');
        $config->setServer($conf['server']);
        $config->setPort($conf['port']);
        $config->setSsl(false);
        $config->setUsername($conf['from']);
        $config->setPassword($conf['pwd']);
        $config->setMailFrom($conf['from']);
        $config->setTimeout($conf['timeout']);
        $config->setMaxPackage($conf['maxsize']);

        $this->config = $config;
    }

    /**
     * 发送邮件
     * @param  String  $subject  邮件主题
     * @param  String  $body     邮件内容
     * @param  String  $address  收件地址
     */
    protected function send($subject, $body, $address)
    {

        // 设置文本或者html格式
        $mimeBean = new Html();
        $mimeBean->setSubject($subject);
        $mimeBean->setBody($body);

        // 发送邮件
        $mailer = new Mailer($this->config);
        $flag = $mailer->sendTo($address, $mimeBean);
        return $flag;
    }

    /**
     * 发送验证码
     * @param  String  $address  收件地址
     */
    public function sendCode($address)
    {
        // 生成随机码
        $captcha = Random::character();

        // defer方式获取redis连接
        $redis = Redis::defer('redis');

        // 尝试获取
        $info = $redis->get($address);
        if ($info) {
            throw new BaseException([
                'code' => Status::CODE_FORBIDDEN,
                'msg'  => '请不要重复获取',
                'error_code' => ErrCode::CAPTCHA_ERROR
            ]);
        }

        // 发送验证码
        $flag = $this->send('注册验证码', '<h1>你的验证码是：<a>' . $captcha . '</a></h1>', $address);
        if (!$flag) {
            Logger::getInstance()->error('mail send error');
            return false;
        }

        // 记录到redis里面
        $flag2 = $redis->set($address, $captcha, 300);
        if (!$flag2) {
            Logger::getInstance()->error('redis write error');
            return false;
        }

        return true;
    }
}
