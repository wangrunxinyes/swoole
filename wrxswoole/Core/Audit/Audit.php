<?php
namespace wrxswoole\Core\Audit;

use wrxswoole\Core\Audit\Model\AuditEntry;
use wrxswoole\Core\Trace\Tracker;

class Audit
{

    private $_entry = null;

    function __construct()
    {
        $this->record();
    }

    function record()
    {
        $this->_entry = AuditEntry::init(true);
    }

    function getEntryId(): int
    {
        return $this->_entry->id;
    }

    function getEntry(): AuditEntry
    {
        return $this->_entry;
    }

    function finalize()
    {
        $this->_entry->finalize();
    }
}

?>