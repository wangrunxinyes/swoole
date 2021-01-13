<?php
namespace wrxswoole\Core\Audit\Model;

use App\Db\DBConfig;

/**
 * AuditError
 *
 * @package wrxswoole\Core\Audit\Model
 *         
 * @property int $id
 * @property int $entry_id
 * @property string $created
 * @property string $message
 * @property int $code
 * @property string $file
 * @property int $line
 * @property mixed $trace
 * @property string $hash
 * @property int $emailed
 *
 * @property AuditEntry $entry
 */
class AuditError extends AuditModel
{

    public $tableName = "audit_error";

    function __construct()
    {
        parent::__construct();
        $this->connectionName = DBConfig::getAuditDbName();
        $this->enableAudit = false;
    }
}
