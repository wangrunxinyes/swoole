<?php
namespace wrxswoole\Core\Audit\Model;

use App\Db\DBConfig;
use wrxswoole\Core\Database\Model\AbstractDbModel;

class AuditModel extends AbstractDbModel
{

    function __construct()
    {
        parent::__construct();
        $this->connectionName = DBConfig::getAuditDbName();
    }
}

?>