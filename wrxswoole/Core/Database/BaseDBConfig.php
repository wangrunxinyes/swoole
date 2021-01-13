<?php
namespace wrxswoole\Core\Database;

use App\App;
use EasySwoole\Component\Singleton;
use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use App\Db\DBConfig;

/**
 *
 * @author WANG RUNXIN
 *        
 */
trait BaseDBConfig
{

    static function initialize()
    {
        DBConfig::getInstance()->enableDB()->enableAuditDB();
    }

    static function getReadDbName()
    {
        return "read";
    }

    static function getWriteDbName()
    {
        return "default";
    }

    static function getAuditDbName()
    {
        return "audit";
    }

    static function Init(): self
    {
        return new self();
    }

    function enableDB(): self
    {
        App::getDb()->addConnection(new Connection(self::getConfig(self::getReadDbName())), self::getReadDbName());
        App::getDb()->addConnection(new Connection(self::getConfig(self::getWriteDbName())), self::getWriteDbName());
        
        $this->loadExtDB();
        
        return $this;
    }
    
    abstract function loadExtDB();

    function enableAuditDB(): self
    {
        if (App::getInstance()->enableAudit()) {
            App::getDb()->addConnection(new Connection(self::getConfig(self::getAuditDbName())), self::getAuditDbName());
        }

        return $this;
    }

    static function initConfig(): Config
    {
        $config = new Config();
        $config->setGetObjectTimeout(3.0); // 设置获取连接池对象超时时间
        $config->setIntervalCheckTime(10 * 1000); // 设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime(20); // 连接池对象最大闲置时间(秒)
        $config->setMinObjectNum(1); // 设置最小连接池存在连接对象数量
        $config->setMaxObjectNum(10); // 设置最大连接池存在连接对象数量
        $config->setAutoPing(5); // 设置自动ping客户端链接的间隔

        return $config;
    }

    static function getConfig($connectionName): Config
    {
        throw new \Exception();
    }
}

?>