<?php
namespace App\Module\Demo\HttpController;

use App\Module\Demo\Model\Customer;
use wrxswoole\Core\Database\Connection;
use wrxswoole\Core\Database\Component\Query;
use wrxswoole\Core\Database\Component\Mysql\QueryBuilder;
use wrxswoole\Core\Database\Traits\DbTrait;
use wrxswoole\Core\HttpController\CoreHttpController;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Db extends CoreHttpController
{

    use DbTrait;

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function index()
    {
        // single model;
        $customer = new Customer();
        $customer->save();
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function model()
    {
        // single model;
        $customer = Customer::create()->get([
            "id" => 1
        ]);
        $this->writeSuccessJson([
            "customer" => $customer,
            "type" => get_class($customer)
        ]);
    }

    /**
     *
     * @Method(allow={POST})
     * @Authenticate(false)
     * @Param(name="type",inArray={"insert","update","get","delete"},notEmpty={"type"})
     */
    public function single()
    {
        switch ($this->request()->getRequestParam("type")) {
            case "insert":
                $command = Connection::create(Customer::create()->getConnectionName())->createCommand(Customer::create()->getConnectionName())
                    ->insert(Customer::create()->getTableName(), [
                    "name" => uniqid()
                ]);
                $result = $command->execute();
                break;
            case "update":
                $command = Connection::create(Customer::create()->getConnectionName())->createCommand()->update(Customer::create()->getTableName(), [
                    "name" => time()
                ], "id = :id", [
                    ":id" => 1
                ]);
                $result = $command->execute();
                break;
            case "get":
                $this->error("not support yet.");
                $command = Query::get()->from(Customer::create()->getTableName())
                    ->andWhere("id = :id", [
                    ":id" => 1
                ]);
                $builder = new QueryBuilder(Connection::create());
                $builder->build($command);
                break;
            case "delete":
                $this->error("not support yet.");
                $command = Connection::create()->createCommand()->insert(Customer::create()->getTableName(), [
                    "code" => uniqid()
                ]);
                break;
        }

        $this->writeSuccessJson([
            "sql" => $command->getRawSql(),
            "result" => $result->getResult()
        ]);
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function batch()
    {
        $records = [
            [
                "name" => uniqid()
            ],
            [
                "name" => uniqid()
            ]
        ];

        $command = Connection::create()->createCommand()->batchInsert(Customer::create()->getTableName(), [
            "name"
        ], $records);

        $command->execute();

        $this->writeSuccessJson([
            "sql" => $command->getRawSql()
        ]);
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function nontraceable()
    {
        $record = [
            "name" => uniqid()
        ];

        $command = Connection::create()->setNonTraceable()
            ->createCommand()
            ->insert(Customer::create()->getTableName(), $record);

        $command->execute();

        $this->writeSuccessJson([
            "sql" => $command->getRawSql()
        ]);
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function queryAll()
    {
        $query = new Query();
        $query->from("customer")
            ->andWhere("id is not null")
            ->andWhere("id != :id", [
            ":id" => 0
        ]);
        $result = $query->all();
        $sql = $query->createCommand()->getRawSql();
        $this->writeSuccessJson([
            "sql" => $sql,
            "result" => $result->getResult()
        ]);
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function queryOne()
    {
        $query = new Query();
        $query->from("customer")
            ->andWhere("id is not null")
            ->andWhere("id = :id", [
            ":id" => 1
        ]);
        $result = $query->one();
        $sql = $query->createCommand()->getRawSql();
        $this->writeSuccessJson([
            "sql" => $sql,
            "result" => $result
        ]);
    }
}
