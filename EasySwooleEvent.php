<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Di;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\Redis;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\ORM\Db\Config as DbConfig;
use EasySwoole\WordsMatch\WordsMatchServer;
use EasySwoole\EasySwoole\Config as ESConfig;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\ORM\Db\Connection as DbConnection;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use App\Exception\HandleException as ExceptionHandler;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {

        date_default_timezone_set('Asia/Shanghai');

        // 数据库配置
        $config = new DbConfig(ESConfig::getInstance()->getConf('MYSQL'));
        DbManager::getInstance()->addConnection(new DbConnection($config));

        // Redis配置
        $redisPoolConfig = Redis::getInstance()->register('redis', new RedisConfig(ESConfig::getInstance()->getConf('REDIS')));
        // 配置连接池连接数
        $redisPoolConfig->setMinObjectNum(5);
        $redisPoolConfig->setMaxObjectNum(20);
        // 配置连接池连接数
        $redisPoolConfig->setMinObjectNum(5);
        $redisPoolConfig->setMaxObjectNum(20);
        $redisPoolConfig->setAutoPing(10);

        // 设置自定义错误处理器
        Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER, [ExceptionHandler::class, 'handle']);
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 注册文本内容检测服务
        WordsMatchServer::getInstance()
            ->setMaxMem('1024M') // 每个进程最大内存
            ->setProcessNum(5) // 设置进程数量
            ->setServerName('Easyswoole words-match') // 服务名称
            ->setTempDir(EASYSWOOLE_TEMP_DIR) // temp地址
            ->setWordsMatchPath(EASYSWOOLE_ROOT . '/WordsMatch/')
            ->setDefaultWordBank('sensitive_word.txt') // 服务启动时默认导入的词库文件路径
            ->setSeparator(',') // 词和其它信息分隔符
            ->attachToServer(ServerManager::getInstance()->getSwooleServer());
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
