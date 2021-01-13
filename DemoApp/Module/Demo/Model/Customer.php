<?php
namespace App\Module\Demo\Model;

use App\Db\DBConfig;
use wrxswoole\Core\Database\Model\AbstractDbModel;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Customer extends AbstractDbModel
{

    /**
     *
     * @var string
     */
    protected $tableName = 'customer';

    public $connectionName = DBConfig::CONNECTION_TRADE;
}

?>