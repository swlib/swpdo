<?php
/**
 * Copyright: Swlib
 * Author: Twosee <twose@qq.com>
 * Date: 2018/4/7 下午4:11
 */

namespace Swlib\SwPDO;

class Mysql
{
    private static $default_options = [
        'host' => '',
        'port' => 3306,
        'user' => '',
        'password' => '',
        'database' => '',//数据库名称
        'charset' => 'utf8mb4', //指定字符集
        'timeout' => 1.000,  //可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
        'strict_type' => true //开启严格类型模式
    ];

    /** @var \Swoole\Coroutine\Mysql */
    public $client;

    public $inTransaction = false;

    public static function construct(array $options)
    {
        $mysql = new self();
        static $keyMap = [
            'dbname' => 'database'
        ];
        foreach ($keyMap as $pdoKey => $swpdoKey) {
            if (isset($options[$pdoKey])) {
                $options[$swpdoKey] = $options[$pdoKey];
                unset($options[$pdoKey]);
            }
        }
        $options = $options + self::$default_options;
        $mysql->client = new \Swoole\Coroutine\Mysql();
        $mysql->client->connect($options);

        return $mysql;
    }

    public function beginTransaction()
    {
        $this->client->begin();
        $this->inTransaction = true;
    }

    public function rollBack()
    {
        $this->client->rollback();
        $this->inTransaction = false;
    }

    public function commit()
    {
        $this->client->commit();
        $this->inTransaction = true;
    }

    public function inTransaction()
    {
        return $this->client->connect_errno;
    }

    public function lastInsertId()
    {
        return $this->client->insert_id;
    }

    public function errorCode()
    {
        $this->client->errno;
    }

    public function errorInfo()
    {
        return $this->client->errno;
    }

    public function exec(string $statement): int
    {
        $this->query($statement);

        return $this->client->affected_rows;
    }

    public function query(string $statement, float $timeout = 1.000)
    {
        return new MysqlStatement($this, $statement, ['timeout' => $timeout]);
    }

    private function rewriteToPosition(string $statement)
    {

    }

    public function prepare(string $statement, array $driver_options = [])
    {
        //rewriting :name to ? style.
        if (strpos($statement, ':') !== false) {
            $i = 0;
            $bindKeyMap = [];
            $statement = preg_replace_callback(
                '/:(\w+)\b/',
                function ($matches) use (&$i, &$bindKeyMap) {
                    $bindKeyMap[$matches[1]] = $i++;

                    return '?';
                },
                $statement
            );
        }
        $stmt_obj = $this->client->prepare($statement);
        if ($stmt_obj) {
            $stmt_obj->bindKeyMap = $bindKeyMap ?? [];
            return new MysqlStatement($this, $stmt_obj, $driver_options);
        } else {
            return false;
        }
    }

    public function getAttribute(int $attribute)
    {
        switch ($attribute) {
            case \PDO::ATTR_AUTOCOMMIT:
                return true;
            case \PDO::ATTR_CASE:
            case \PDO::ATTR_CLIENT_VERSION:
            case \PDO::ATTR_CONNECTION_STATUS:
                return $this->client->connected;
            case \PDO::ATTR_DRIVER_NAME:
            case \PDO::ATTR_ERRMODE:
                return 'Swoole Style';
            case \PDO::ATTR_ORACLE_NULLS:
            case \PDO::ATTR_PERSISTENT:
            case \PDO::ATTR_PREFETCH:
            case \PDO::ATTR_SERVER_INFO:
                return $this->serverInfo['timeout'] ?? self::$default_options['timeout'];
            case \PDO::ATTR_SERVER_VERSION:
            case \PDO::ATTR_TIMEOUT:
            default:
                throw new \InvalidArgumentException('Not implemented yet!');
        }
    }

    public function quote()
    {
        throw new \BadMethodCallException(<<<TXT
If you are using this function to build SQL statements, 
you are strongly recommended to use PDO::prepare() to prepare SQL statements 
with bound parameters instead of using PDO::quote() to interpolate user input into an SQL statement.
Prepared statements with bound parameters are not only more portable, more convenient, 
immune to SQL injection, but are often much faster to execute than interpolated queries,
as both the server and client side can cache a compiled form of the query.
TXT
        );
    }

}
