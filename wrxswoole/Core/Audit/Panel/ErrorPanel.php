<?php
namespace wrxswoole\Core\Audit\Panel;

use wrxswoole\Core\Audit\Component\Helper;
use wrxswoole\Core\Audit\Model\AuditError;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Database\Connection;
use App\App;

class ErrorPanel
{

    const PanelName = 'audit/error';

    private $_exceptions = [];

    /**
     * Log an exception
     *
     * @param int $entry_id
     *            Entry to associate the error with
     * @param \Exception|\Throwable $exception
     * @return null|static
     */
    public function log(\Throwable $exception)
    {
        $auditModel = Tracker::getInstance()->audit;

        if (is_null($auditModel)) {
            $entry_id = null;
        } else {
            $entry_id = $auditModel->getEntryId();
        }

        // Only log each exception once
        $exceptionId = spl_object_hash($exception);
        if (in_array($exceptionId, $this->_exceptions))
            return true;

        // If this is a follow up exception, make sure to log the base exception first
        if ($exception->getPrevious())
            $this->log($exception->getPrevious());

        if (App::getInstance()->enableAudit()) {
            $error = new AuditError();
            Connection::create($error->connectionName)->setNonTraceable()
                ->createCommand()
                ->insert($error->getTableName(), [
                'entry_id' => $entry_id,
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => serialize(Helper::cleanupTrace($exception->getTrace())),
                'hash' => Helper::hash($error->message . $error->file . $error->line),
                'created' => date('Y-m-d H:i:s')
            ])
                ->execute();
        }

        $this->_exceptions[] = $exceptionId;

        return true;
    }

    /**
     * Log a regular error message
     *
     * @param int $entry_id
     *            Entry to associate the error with
     * @param string $message
     * @param int $code
     * @param string $file
     * @param int $line
     * @param array $trace
     *            Stack trace to include. Use `Helper::generateTrace()` to create it.
     * @return null|static
     */
    public function logMessage($message, $code = 0, $file = '', $line = 0, $trace = [])
    {
        $entry_id = Tracker::getInstance()->audit->getEntryId();

        $error = new AuditError();
        Connection::create($error->connectionName)->setNonTraceable()
            ->createCommand()
            ->insert($error->getTableName(), [
            'entry_id' => $entry_id,
            'message' => $message,
            'code' => $code,
            'file' => $file,
            'line' => $line,
            'trace' => serialize(Helper::cleanupTrace($trace)),
            'hash' => Helper::hash($error->message . $error->file . $error->line),
            'created' => date('Y-m-d H:i:s')
        ])
            ->execute();

        return true;
    }

    /**
     *
     * @inheritdoc
     */
    public function hasEntryData($entry)
    {
        return count($entry->linkedErrors) > 0;
    }

    /**
     *
     * @inheritdoc
     */
    protected function getChartModel()
    {
        return AuditError::className();
    }

    /**
     *
     * @inheritdoc
     */
    public function cleanup($maxAge = null)
    {
        $maxAge = $maxAge !== null ? $maxAge : $this->maxAge;
        if ($maxAge === null)
            return false;
        return AuditError::deleteAll([
            '<=',
            'created',
            date('Y-m-d 23:59:59', strtotime("-$maxAge days"))
        ]);
    }
}

?>