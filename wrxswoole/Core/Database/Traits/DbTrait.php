<?php
namespace wrxswoole\Core\Database\Traits;

use App\Db\DBConfig;
use EasySwoole\ORM\Db\Result;
use wrxswoole\Core\Database\Connection;
use wrxswoole\Core\Database\Model\AbstractDbModel;

/**
 *
 * @author wangrunxin
 *        
 */
trait DbTrait
{

    /**
     *
     * @var string|string[] character used to quote schema, table, etc. names.
     *      An array of 2 characters can be used in case starting and ending characters are different.
     *     
     */
    protected $tableQuoteCharacter = "`";

    /**
     *
     * @var string|string[] character used to quote column names.
     *      An array of 2 characters can be used in case starting and ending characters are different.
     *     
     */
    protected $columnQuoteCharacter = '`';

    public function batch_insert_on_duplicate_update(AbstractDbModel $model, $rows)
    {
        if (! is_array($rows) || count($rows) == 0) {
            return;
        }

        $example = $this->array_first($rows);

        $column = array_keys($example);

        $sql = $this->batchInsert($model->getTableName(), $column, $rows);

        $update_text = [];
        foreach ($column as $line) {
            $update_text[] = $line . "=VALUES(" . $line . ")";
        }

        $sql .= 'ON DUPLICATE KEY UPDATE ' . implode(", ", $update_text);

        return $this->query($sql, [], $model->getCurrentDbConnectionName());
    }

    public function query($sql, $params = [], $connectionNanme = DBConfig::CONNECTION_WRITE): Result
    {
        return Connection::create($connectionNanme)->createCommand($sql, $params)->execute();
    }

    public function fetch($sql, $params = [], $connectionNanme = DBConfig::CONNECTION_WRITE)
    {
        $result = $this->query($sql, $params, $connectionNanme);
        return $result->getResult();
    }

    public function fetchOne($sql, $params = [], $connectionNanme = DBConfig::CONNECTION_WRITE)
    {
        $res = $this->fetch($sql, $params, $connectionNanme);
        if (empty($res)) {
            if ($res === false) {
                return false;
            }
            return null;
        }

        return $res[0];
    }

    public function batchInsert($table, $columns, $rows, &$params = [])
    {
        if (empty($rows)) {
            return '';
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {

                if (is_string($value)) {
                    $value = $this->quoteValue($value);
                } elseif (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    $value = self::floatToString($value);
                } elseif ($value === false) {
                    $value = 0;
                } elseif ($value === null) {
                    $value = 'NULL';
                }
                $vs[] = $value;
            }
            $values[] = '(' . implode(', ', $vs) . ')';
        }
        if (empty($values)) {
            return '';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $this->quoteColumnName($name);
        }

        return 'INSERT INTO ' . $this->quoteTableName($table) . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
    }

    /**
     * Quotes a simple table name for use in a query.
     * A simple table name should contain the table name only without any schema prefix.
     * If the table name is already quoted, this method will do nothing.
     *
     * @param string $name
     *            table name
     * @return string the properly quoted table name
     */
    public function quoteSimpleTableName($name)
    {
        if (is_string($this->tableQuoteCharacter)) {
            $startingCharacter = $endingCharacter = $this->tableQuoteCharacter;
        } else {
            list ($startingCharacter, $endingCharacter) = $this->tableQuoteCharacter;
        }
        return strpos($name, $startingCharacter) !== false ? $name : $startingCharacter . $name . $endingCharacter;
    }

    /**
     * Splits full table name into parts
     *
     * @param string $name
     * @return array
     * @since 2.0.22
     */
    protected function getTableNameParts($name)
    {
        return explode('.', $name);
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * If the table name is already quoted or contains '(' or '{{',
     * then this method will do nothing.
     *
     * @param string $name
     *            table name
     * @return string the properly quoted table name
     * @see quoteSimpleTableName()
     */
    public function quoteTableName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '{{') !== false) {
            return $name;
        }
        if (strpos($name, '.') === false) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = $this->getTableNameParts($name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }

        return implode('.', $parts);
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * If the column name is already quoted or contains '(', '[[' or '{{',
     * then this method will do nothing.
     *
     * @param string $name
     *            column name
     * @return string the properly quoted column name
     * @see quoteSimpleColumnName()
     */
    public function quoteColumnName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '[[') !== false) {
            return $name;
        }
        if (($pos = strrpos($name, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else {
            $prefix = '';
        }
        if (strpos($name, '{{') !== false) {
            return $name;
        }

        return $prefix . $this->quoteSimpleColumnName($name);
    }

    /**
     * Quotes a simple column name for use in a query.
     * A simple column name should contain the column name only without any prefix.
     * If the column name is already quoted or is the asterisk character '*', this method will do nothing.
     *
     * @param string $name
     *            column name
     * @return string the properly quoted column name
     */
    public function quoteSimpleColumnName($name)
    {
        if (is_string($this->tableQuoteCharacter)) {
            $startingCharacter = $endingCharacter = $this->columnQuoteCharacter;
        } else {
            list ($startingCharacter, $endingCharacter) = $this->columnQuoteCharacter;
        }
        return $name === '*' || strpos($name, $startingCharacter) !== false ? $name : $startingCharacter . $name . $endingCharacter;
    }

    /**
     * Quotes a string value for use in a query.
     * Note that if the parameter is not a string, it will be returned without change.
     *
     * @param string $str
     *            string to be quoted
     * @return string the properly quoted string
     * @see https://secure.php.net/manual/en/function.PDO-quote.php
     */
    public function quoteValue($str)
    {
        if (! is_string($str)) {
            return $str;
        }

        // the driver doesn't support quote (e.g. oci)
        return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
    }

    public static function floatToString($number)
    {
        // . and , are the only decimal separators known in ICU data,
        // so its safe to call str_replace here
        return str_replace(',', '.', (string) $number);
    }

    /**
     *
     * @param AbstractDbModel[] $models
     * @param array $attributes
     */
    public function batch_insert_model_on_duplicate_update($models, $attributes = [])
    {
        if (! is_array($models) || count($models) == 0) {
            return;
        }

        $model = $this->get_target_model($models);

        if (count($attributes) == 0) {
            $attributes = array_keys($model->getAttributes());
        }

        $rows = [];

        foreach ($models as $model) {
            $line = [];
            foreach ($attributes as $key) {
                $line[$key] = $model->$key;
            }

            $rows[] = $line;
        }

        return $this->batch_insert_on_duplicate_update($model, $rows);
    }

    /**
     *
     * @param AbstractDbModel[] $models
     * @return AbstractDbModel
     */
    public function get_target_model($models)
    {
        return $this->array_first($models);
    }

    function array_first(array $array)
    {
        foreach ($array as $item) {
            return $item;
        }
    }
}

?>