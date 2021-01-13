<?php
namespace wrxswoole\Core\Audit\Model;

use App\Db\DBConfig;
use wrxswoole\Core\Audit\Component\Helper;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Database\Connection;
use wrxswoole\Core\Database\Component\Expression;
use wrxswoole\Core\HttpController\CoreHttpController;
use wrxswoole\Core\HttpController\NonHttpEnvController;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Log\Logger;
use wrxswoole\Core\Trace\Traits\TraceTrait;

/**
 * AuditEntry
 *
 * @package wrxswoole\Core\Audit\Model
 *         
 * @property int $id
 * @property string $created
 * @property float $duration
 * @property int $user_id 0 means anonymous
 * @property string $ip
 * @property string $route
 * @property int $memory_max
 * @property string $request_method
 * @property string $ajax
 *
 * @property AuditError[] $linkedErrors
 * @property AuditJavascript[] $javascripts
 * @property AuditTrail[] $trails
 * @property AuditMail[] $mails
 * @property AuditData[] $data
 */
class AuditEntry extends AuditModel
{

    use TraceTrait;

    /**
     *
     * @var bool
     */
    protected $autoSerialize = false;

    public $tableName = 'audit_entry';

    function __construct()
    {
        parent::__construct();
        $this->connectionName = DBConfig::getAuditDbName();
        $this->enableAudit = false;
    }

    /**
     *
     * @param bool $initialise
     * @return static
     */
    public static function init($initialise = true): AuditEntry
    {
        $entry = new static();
        if ($initialise)
            $entry->record();

        return $entry;
    }

    /**
     * Writes a number of associated data records in one go.
     *
     * @param
     *            $batchData
     * @param bool $compact
     * @throws \Exception
     */
    public function addBatchData($batchData, $compact = true)
    {
        $columns = [
            'entry_id',
            'type',
            'created',
            'data'
        ];
        $rows = [];
        $params = [];
        $date = date('Y-m-d H:i:s');
        // Some database like postgres depend on the data being escaped correctly.
        // PDO can take care of this if you define the field as a LOB (Large OBject), but unfortunately Yii does threat values
        // for batch inserts the same way. This code adds a number of literals instead of the actual values
        // so that they can be bound right before insert and still get escaped correctly
        foreach ($batchData as $type => $data) {
            $param = ':data_' . str_replace('/', '_', $type);
            $rows[] = [
                $this->id,
                $type,
                $date,
                new Expression($param)
            ];
            $params[$param] = serialize($data);
        }
        Connection::create($this->connectionName)->setNonTraceable()
            ->createCommand()
            ->batchInsert(AuditData::create()->getTableName(), $columns, $rows)
            ->bindValues($params)
            ->execute();
    }

    /**
     *
     * @param
     *            $type
     * @param
     *            $data
     * @param bool|true $compact
     * @throws \Exception
     */
    public function addData($type, $data, $compact = true)
    {
        // Make sure to mark data as a large object so it gets escaped
        $record = [
            'entry_id' => $this->id,
            'type' => $type,
            'created' => date('Y-m-d H:i:s'),
            'data' => [
                Helper::serialize($data, $compact),
                \PDO::PARAM_LOB
            ]
        ];
        Connection::create($this->connectionName)->setNonTraceable()
            ->createCommand()
            ->insert(AuditData::create()->getTableName(), $record)
            ->execute();
    }

    /**
     * Records the current application state into the instance.
     */
    public function record()
    {
        $this->route = self::getRoute(CoreHttpController::getInstance());
        if (CoreHttpController::getInstance()->isHttpRequest()) {
            $this->user_id = Tracker::getInstance()->user_id;
            $this->ip = $this->getUserIP();
            $this->request_method = CoreHttpController::getInstance()->request()->getMethod();
        } else {
            $this->request_method = 'Swoole';
        }
        $this->created = date("Y-m-d H:i:s");

        $this->save();
    }

    static function getRoute(CoreHttpController $controller)
    {
        if ($controller->isHttpRequest()) {
            $target = $controller->request()->getRequestTarget();
            $data = explode("?", $target);
            if (count($data) == 1) {
                return $target;
            }

            return $data[0];
        }

        return NonHttpEnvController::getInstance()->request()->identifier;
    }

    /**
     *
     * @return string
     */
    public function getUserIP()
    {
        $server = CoreHttpController::getInstance()->request()->getServerParams();
        if (! empty($server['http_x_forwarded_for'])) {
            return current(array_values(array_filter(explode(',', $server['http_x_forwarded_for']))));
        }
        $header = CoreHttpController::getInstance()->request()->getHeaders();
        if (! empty($header['x-forwarded-for'])) {
            return $header['x-forwarded-for'][0];
        }
        return isset($server['remote_addr']) ? $server['remote_addr'] : null;
    }

    /**
     *
     * @return bool
     */
    public function finalize()
    {
        $this->duration = microtime(true) - CoreCoroutineThread::getInstance()->getCoreController()->getStartAt();
        $this->memory_max = memory_get_peak_usage();

        if (is_null($this->id)) {
            Logger::getInstance()->error([
                "msg" => "entry_id is null",
                "data" => $this->data
            ], "AuditLogError");
            return false;
        } else {
            return $this->update();
        }
    }
}
