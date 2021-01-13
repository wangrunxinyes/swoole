<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace wrxswoole\Core\Database;

use App\App;
use PDO;
use wrxswoole\Core\Component\BaseObject;
use wrxswoole\Core\Database\Component\Command;
use wrxswoole\Core\Database\Component\QueryBuilder;

trait ConnectionTrait
{

    public $dsn;

    /**
     *
     * @var string the username for establishing DB connection. Defaults to `null` meaning no username to use.
     */
    public $username;

    /**
     *
     * @var string the password for establishing DB connection. Defaults to `null` meaning no password to use.
     */
    public $password;

    /**
     *
     * @var array PDO attributes (name => value) that should be set when calling [[open()]]
     *      to establish a DB connection. Please refer to the
     *      [PHP manual](https://secure.php.net/manual/en/pdo.setattribute.php) for
     *      details about available attributes.
     */
    public $attributes;

    public $pdo;

    public $enableSchemaCache = false;

    public $schemaCacheDuration = 3600;

    public $schemaCacheExclude = [];

    public $schemaCache = 'cache';

    public $enableQueryCache = true;

    public $queryCacheDuration = 3600;

    public $queryCache = 'cache';

    /**
     *
     * @var string the charset used for database connection. The property is only used
     *      for MySQL, PostgreSQL and CUBRID databases. Defaults to null, meaning using default charset
     *      as configured by the database.
     *     
     *      For Oracle Database, the charset must be specified in the [[dsn]], for example for UTF-8 by appending `;charset=UTF-8`
     *      to the DSN string.
     *     
     *      The same applies for if you're using GBK or BIG5 charset with MySQL, then it's highly recommended to
     *      specify charset via [[dsn]] like `'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'`.
     */
    public $charset;

    /**
     *
     * @var bool whether to turn on prepare emulation. Defaults to false, meaning PDO
     *      will use the native prepare support if available. For some databases (such as MySQL),
     *      this may need to be set true so that PDO can emulate the prepare support to bypass
     *      the buggy native prepare support.
     *      The default value is null, which means the PDO ATTR_EMULATE_PREPARES value will not be changed.
     */
    public $emulatePrepare;

    /**
     *
     * @var string the common prefix or suffix for table names. If a table name is given
     *      as `{{%TableName}}`, then the percentage character `%` will be replaced with this
     *      property value. For example, `{{%post}}` becomes `{{tbl_post}}`.
     */
    public $tablePrefix = '';

    /**
     *
     * @var array mapping between PDO driver names and [[Schema]] classes.
     *      The keys of the array are PDO driver names while the values are either the corresponding
     *      schema class names or configurations. Please refer to [[Yii::createObject()]] for
     *      details on how to specify a configuration.
     *     
     *      This property is mainly used by [[getSchema()]] when fetching the database schema information.
     *      You normally do not need to set this property unless you want to use your own
     *      [[Schema]] class to support DBMS that is not supported by Yii.
     */
    public $schemaMap = [
        'pgsql' => '\wrxswoole\Core\Database\Component\pgsql\Schema', // PostgreSQL
        'mysqli' => '\wrxswoole\Core\Database\Component\mysql\Schema', // MySQL
        'mysql' => '\wrxswoole\Core\Database\Component\mysql\Schema', // MySQL
        'sqlite' => '\wrxswoole\Core\Database\Component\sqlite\Schema', // sqlite 3
        'sqlite2' => '\wrxswoole\Core\Database\Component\sqlite\Schema', // sqlite 2
        'sqlsrv' => '\wrxswoole\Core\Database\Component\mssql\Schema', // newer MSSQL driver on MS Windows hosts
        'oci' => '\wrxswoole\Core\Database\Component\oci\Schema', // Oracle driver
        'mssql' => '\wrxswoole\Core\Database\Component\mssql\Schema', // older MSSQL driver on MS Windows hosts
        'dblib' => '\wrxswoole\Core\Database\Component\mssql\Schema', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
        'cubrid' => '\wrxswoole\Core\Database\Component\cubrid\Schema' // CUBRID
    ];

    /**
     *
     * @var string Custom PDO wrapper class. If not set, it will use [[PDO]] or [[\\wrxswoole\Core\Database\Component\mssql\PDO]] when MSSQL is used.
     * @see pdo
     */
    public $pdoClass;

    public $commandClass = '\wrxswoole\Core\Database\Component\Command';

    /**
     *
     * @var array mapping between PDO driver names and [[Command]] classes.
     *      The keys of the array are PDO driver names while the values are either the corresponding
     *      command class names or configurations. Please refer to [[Yii::createObject()]] for
     *      details on how to specify a configuration.
     *     
     *      This property is mainly used by [[createCommand()]] to create new database [[Command]] objects.
     *      You normally do not need to set this property unless you want to use your own
     *      [[Command]] class or support DBMS that is not supported by Yii.
     * @since 2.0.14
     */
    public $commandMap = [
        'pgsql' => '\wrxswoole\Core\Database\Component\Command', // PostgreSQL
        'mysqli' => '\wrxswoole\Core\Database\Component\Command', // MySQL
        'mysql' => '\wrxswoole\Core\Database\Component\Command', // MySQL
        'sqlite' => '\wrxswoole\Core\Database\Component\sqlite\Command', // sqlite 3
        'sqlite2' => '\wrxswoole\Core\Database\Component\sqlite\Command', // sqlite 2
        'sqlsrv' => '\wrxswoole\Core\Database\Component\Command', // newer MSSQL driver on MS Windows hosts
        'oci' => '\wrxswoole\Core\Database\Component\Command', // Oracle driver
        'mssql' => '\wrxswoole\Core\Database\Component\Command', // older MSSQL driver on MS Windows hosts
        'dblib' => '\wrxswoole\Core\Database\Component\Command', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
        'cubrid' => '\wrxswoole\Core\Database\Component\Command' // CUBRID
    ];

    /**
     *
     * @var bool whether to enable [savepoint](http://en.wikipedia.org/wiki/Savepoint).
     *      Note that if the underlying DBMS does not support savepoint, setting this property to be true will have no effect.
     */
    public $enableSavepoint = true;

    /**
     */
    public $serverStatusCache = 'cache';

    /**
     *
     * @var int the retry interval in seconds for dead servers listed in [[masters]] and [[slaves]].
     *      This is used together with [[serverStatusCache]].
     */
    public $serverRetryInterval = 600;

    /**
     *
     * @var bool whether to enable read/write splitting by using [[slaves]] to read data.
     *      Note that if [[slaves]] is empty, read/write splitting will NOT be enabled no matter what value this property takes.
     */
    public $enableSlaves = true;

    /**
     *
     * @var array list of slave connection configurations. Each configuration is used to create a slave DB connection.
     *      When [[enableSlaves]] is true, one of these configurations will be chosen and used to create a DB connection
     *      for performing read queries only.
     */
    public $slaves = [];

    /**
     *
     * @var array the configuration that should be merged with every slave configuration listed in [[slaves]].
     *      For example,
     *     
     *      ```php
     *      [
     *      'username' => 'slave',
     *      'password' => 'slave',
     *      'attributes' => [
     *      // use a smaller connection timeout
     *      PDO::ATTR_TIMEOUT => 10,
     *      ],
     *      ]
     *      ```
     */
    public $slaveConfig = [];

    /**
     *
     * @var array list of master connection configurations. Each configuration is used to create a master DB connection.
     *      When [[open()]] is called, one of these configurations will be chosen and used to create a DB connection
     *      which will be used by this object.
     *      Note that when this property is not empty, the connection setting (e.g. "dsn", "username") of this object will
     *      be ignored.
     */
    public $masters = [];

    /**
     *
     * @var array the configuration that should be merged with every master configuration listed in [[masters]].
     *      For example,
     *     
     *      ```php
     *      [
     *      'username' => 'master',
     *      'password' => 'master',
     *      'attributes' => [
     *      // use a smaller connection timeout
     *      PDO::ATTR_TIMEOUT => 10,
     *      ],
     *      ]
     *      ```
     */
    public $masterConfig = [];

    /**
     *
     * @var bool whether to shuffle [[masters]] before getting one.
     * @since 2.0.11
     */
    public $shuffleMasters = true;

    /**
     *
     * @var bool whether to enable logging of database queries. Defaults to true.
     *      You may want to disable this option in a production environment to gain performance
     *      if you do not need the information being logged.
     * @since 2.0.12
     */
    public $enableLogging = true;

    /**
     *
     * @var bool whether to enable profiling of opening database connection and database queries. Defaults to true.
     *      You may want to disable this option in a production environment to gain performance
     *      if you do not need the information being logged.
     * @since 2.0.12
     */
    public $enableProfiling = true;

    /**
     */
    private $_transaction;

    /**
     */
    private $_schema;

    /**
     *
     * @var string driver name
     */
    private $_driverName;

    /**
     *
     * @var Connection|false the currently active master connection
     */
    private $_master = false;

    /**
     *
     * @var Connection|false the currently active slave connection
     */
    private $_slave = false;

    /**
     *
     * @var array query cache parameters for the [[cache()]] calls
     */
    private $_queryCacheInfo = [];

    /**
     *
     * @var string[] quoted table name cache for [[quoteTableName()]] calls
     */
    private $_quotedTableNames;

    /**
     *
     * @var string[] quoted column name cache for [[quoteColumnName()]] calls
     */
    private $_quotedColumnNames;

    /**
     * Returns the schema information for the database opened by this connection.
     *
     * @throws \Exception if there is no support for the current driver type
     */
    function getSchema()
    {
        if ($this->_schema !== null) {
            return $this->_schema;
        }

        $config = [
            'db' => $this,
            'class' => 'wrxswoole\Core\Database\Component\Mysql\Schema'
        ];

        return $this->_schema = BaseObject::createObject($config);
    }

    function createCommand($sql = null, $params = []): Command
    {
        $config = [
            'db' => $this,
            'class' => 'wrxswoole\Core\Database\Component\Command',
            'sql' => $sql,
            'pdoStatement' => new \PDOStatement()
        ];

        /** @var Command $command */
        $command = BaseObject::createObject($config);
        return $command->bindValues($params);
    }

    function getQueryBuilder(): QueryBuilder
    {
        return $this->getSchema()->getQueryBuilder();
    }

    /**
     * Can be used to set [[QueryBuilder]] configuration via Connection configuration array.
     *
     * @param array $value
     *            the [[QueryBuilder]] properties to be configured.
     * @since 2.0.14
     */
    public function setQueryBuilder($value)
    {
        BaseObject::configure($this->getQueryBuilder(), $value);
    }

    /**
     * Obtains the schema information for the named table.
     *
     * @param string $name
     *            table name.
     * @param bool $refresh
     *            whether to reload the table schema even if it is found in the cache.
     */
    public function getTableSchema($name, $refresh = false)
    {
        return $this->getSchema()->getTableSchema($name, $refresh);
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string $sequenceName
     *            name of the sequence object (required by some DBMS)
     * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
     * @see https://secure.php.net/manual/en/pdo.lastinsertid.php
     */
    public function getLastInsertID($sequenceName = '')
    {
        return $this->getSchema()->getLastInsertID($sequenceName);
    }

    /**
     * Quotes a string value for use in a query.
     * Note that if the parameter is not a string, it will be returned without change.
     *
     * @param string $value
     *            string to be quoted
     * @return string the properly quoted string
     * @see https://secure.php.net/manual/en/pdo.quote.php
     */
    public function quoteValue($value)
    {
        return $this->getSchema()->quoteValue($value);
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * If the table name is already quoted or contains special characters including '(', '[[' and '{{',
     * then this method will do nothing.
     *
     * @param string $name
     *            table name
     * @return string the properly quoted table name
     */
    public function quoteTableName($name)
    {
        if (isset($this->_quotedTableNames[$name])) {
            return $this->_quotedTableNames[$name];
        }
        return $this->_quotedTableNames[$name] = $this->getSchema()->quoteTableName($name);
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * If the column name is already quoted or contains special characters including '(', '[[' and '{{',
     * then this method will do nothing.
     *
     * @param string $name
     *            column name
     * @return string the properly quoted column name
     */
    public function quoteColumnName($name)
    {
        if (isset($this->_quotedColumnNames[$name])) {
            return $this->_quotedColumnNames[$name];
        }
        return $this->_quotedColumnNames[$name] = $this->getSchema()->quoteColumnName($name);
    }

    /**
     * Processes a SQL statement by quoting table and column names that are enclosed within double brackets.
     * Tokens enclosed within double curly brackets are treated as table names, while
     * tokens enclosed within double square brackets are column names. They will be quoted accordingly.
     * Also, the percentage character "%" at the beginning or ending of a table name will be replaced
     * with [[tablePrefix]].
     *
     * @param string $sql
     *            the SQL to be quoted
     * @return string the quoted SQL
     */
    public function quoteSql($sql)
    {
        return preg_replace_callback('/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])/', function ($matches) {
            if (isset($matches[3])) {
                return $this->quoteColumnName($matches[3]);
            }

            return str_replace('%', $this->tablePrefix, $this->quoteTableName($matches[2]));
        }, $sql);
    }

    /**
     * Returns the name of the DB driver.
     * Based on the the current [[dsn]], in case it was not set explicitly
     * by an end user.
     *
     * @return string name of the DB driver
     */
    public function getDriverName()
    {
        if ($this->_driverName === null) {
            if (($pos = strpos($this->dsn, ':')) !== false) {
                $this->_driverName = strtolower(substr($this->dsn, 0, $pos));
            } else {
                $this->_driverName = strtolower($this->getSlavePdo()->getAttribute(PDO::ATTR_DRIVER_NAME));
            }
        }

        return $this->_driverName;
    }

    /**
     * Changes the current driver name.
     *
     * @param string $driverName
     *            name of the DB driver
     */
    public function setDriverName($driverName)
    {
        $this->_driverName = strtolower($driverName);
    }

    /**
     * Returns a server version as a string comparable by [[\version_compare()]].
     *
     * @return string server version as a string.
     * @since 2.0.14
     */
    public function getServerVersion()
    {
        return $this->getSchema()->getServerVersion();
    }

    /**
     * Returns the PDO instance for the currently active slave connection.
     * When [[enableSlaves]] is true, one of the slaves will be used for read queries, and its PDO instance
     * will be returned by this method.
     *
     * @param bool $fallbackToMaster
     *            whether to return a master PDO in case none of the slave connections is available.
     * @return PDO the PDO instance for the currently active slave connection. `null` is returned if no slave connection
     *         is available and `$fallbackToMaster` is false.
     */
    public function getSlavePdo($fallbackToMaster = true)
    {
        return $this;
    }

    /**
     * Returns the PDO instance for the currently active master connection.
     * This method will open the master DB connection and then return [[pdo]].
     *
     * @return PDO the PDO instance for the currently active master connection.
     */
    public function getMasterPdo()
    {
        return $this;
    }

    /**
     * Returns the currently active slave connection.
     * If this method is called for the first time, it will try to open a slave connection when [[enableSlaves]] is true.
     *
     * @param bool $fallbackToMaster
     *            whether to return a master connection in case there is no slave connection available.
     * @return ConnectionTrait the currently active slave connection. `null` is returned if there is no slave available and
     *         `$fallbackToMaster` is false.
     */
    public function getSlave($fallbackToMaster = true)
    {
        if (! $this->enableSlaves) {
            return $fallbackToMaster ? $this : null;
        }

        if ($this->_slave === false) {
            $this->_slave = $this->openFromPool($this->slaves, $this->slaveConfig);
        }

        return $this->_slave === null && $fallbackToMaster ? $this : $this->_slave;
    }

    /**
     * Returns the currently active master connection.
     * If this method is called for the first time, it will try to open a master connection.
     *
     * @return ConnectionTrait the currently active master connection. `null` is returned if there is no master available.
     * @since 2.0.11
     */
    public function getMaster()
    {
        if ($this->_master === false) {
            $this->_master = $this->shuffleMasters ? $this->openFromPool($this->masters, $this->masterConfig) : $this->openFromPoolSequentially($this->masters, $this->masterConfig);
        }

        return $this->_master;
    }

    /**
     * Executes the provided callback by using the master connection.
     *
     * This method is provided so that you can temporarily force using the master connection to perform
     * DB operations even if they are read queries. For example,
     *
     * ```php
     * $result = $db->useMaster(function ($db) {
     * return $db->createCommand('SELECT * FROM user LIMIT 1')->queryOne();
     * });
     * ```
     *
     * @param callable $callback
     *            a PHP callable to be executed by this method. Its signature is
     *            `function (Connection $db)`. Its return value will be returned by this method.
     * @return mixed the return value of the callback
     * @throws \Exception|\Throwable if there is any exception thrown from the callback
     */
    public function useMaster(callable $callback)
    {
        if ($this->enableSlaves) {
            $this->enableSlaves = false;
            try {
                $result = call_user_func($callback, $this);
            } catch (\Exception $e) {
                $this->enableSlaves = true;
                throw $e;
            } catch (\Throwable $e) {
                $this->enableSlaves = true;
                throw $e;
            }
            // TODO: use "finally" keyword when miminum required PHP version is >= 5.5
            $this->enableSlaves = true;
        } else {
            $result = call_user_func($callback, $this);
        }

        return $result;
    }

    /**
     * Opens the connection to a server in the pool.
     * This method implements the load balancing among the given list of the servers.
     * Connections will be tried in random order.
     *
     * @param array $pool
     *            the list of connection configurations in the server pool
     * @param array $sharedConfig
     *            the configuration common to those given in `$pool`.
     * @return ConnectionTrait the opened DB connection, or `null` if no server is available
     * @throws \Exception if a configuration does not specify "dsn"
     */
    protected function openFromPool(array $pool, array $sharedConfig)
    {
        shuffle($pool);
        return $this->openFromPoolSequentially($pool, $sharedConfig);
    }

    /**
     * Opens the connection to a server in the pool.
     * This method implements the load balancing among the given list of the servers.
     * Connections will be tried in sequential order.
     *
     * @param array $pool
     *            the list of connection configurations in the server pool
     * @param array $sharedConfig
     *            the configuration common to those given in `$pool`.
     * @throws \Exception if a configuration does not specify "dsn"
     * @since 2.0.11
     */
    protected function openFromPoolSequentially(array $pool, array $sharedConfig)
    {
        App::getDb();
    }

    /**
     * Close the connection before serializing.
     *
     * @return array
     */
    public function __sleep()
    {
        $fields = (array) $this;

        unset($fields['pdo']);
        unset($fields["\000" . __CLASS__ . "\000" . '_master']);
        unset($fields["\000" . __CLASS__ . "\000" . '_slave']);
        unset($fields["\000" . __CLASS__ . "\000" . '_transaction']);
        unset($fields["\000" . __CLASS__ . "\000" . '_schema']);

        return array_keys($fields);
    }

    /**
     * Reset the connection after cloning.
     */
    public function __clone()
    {
        parent::__clone();

        $this->_master = false;
        $this->_slave = false;
        $this->_schema = null;
        $this->_transaction = null;
        if (strncmp($this->dsn, 'sqlite::memory:', 15) !== 0) {
            // reset PDO connection, unless its sqlite in-memory, which can only have one connection
            $this->pdo = null;
        }
    }
}
