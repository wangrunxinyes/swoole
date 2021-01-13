<?php
namespace App\Db;

use App\App;
use EasySwoole\Component\Singleton;
use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use wrxswoole\Core\Database\BaseDBConfig;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class DBConfig
{

    const CONNECTION_WRITE = "default";

    use BaseDBConfig;
    use Singleton;

    static function getConfig($connectionName): Config
    {
        $config = self::initConfig();

        switch ($connectionName) {
            default:
                $config->setDatabase('*');
                $config->setUser('*');
                $config->setPassword('*');
                $config->setHost('*');
                break;
        }

        return $config;
    }

    function loadExtDB()
    {
        App::getDb()->addConnection(new Connection(self::getConfig(DBConfig::CONNECTION_TRADE)), DBConfig::CONNECTION_TRADE);
    }
}

?>