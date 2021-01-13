<?php
namespace wrxswoole\Core\Audit\Model;

use App\Db\DBConfig;

/**
 * AuditData
 * Extra data associated with a specific audit line.
 * There are currently no guidelines concerning what the name/type
 * needs to be, this is at your own discretion.
 *
 * @property int $id
 * @property int $entry_id
 * @property string $type
 * @property string $data
 * @property string $created
 *
 * @property AuditEntry $entry
 *
 * @package wrxswoole\Core\Audit\Model
 */
class AuditData extends AuditModel
{

    public $tableName = 'audit_data';

    function __construct()
    {
        parent::__construct();
        $this->connectionName = DBConfig::getAuditDbName();
        $this->enableAudit = false;
    }
}