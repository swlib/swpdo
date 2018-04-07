<?php
/**
 * Copyright: Swlib
 * Author: Twosee <twose@qq.com>
 * Date: 2018/4/7 下午7:49
 */

namespace Swlib\SwPDO;

class MysqlStatement
{

    private $parent;
    /**
     * @var \Swoole\Coroutine\Mysql\Statement | string
     */
    public $statement;
    public $timeout;

    public $bindMap = [];

    public $cursor = -1;
    public $cursor_orientation = \PDO::FETCH_ORI_NEXT;
    public $result_set = [];

    public function __construct(Mysql $parent, $statement, array $driver_options = [])
    {
        $this->parent = $parent;
        $this->statement = $statement;
        $this->timeout = $driver_options['timeout'] ?? 1.000;
    }

    public function errorCode()
    {
        return $this->statement->errno;
    }

    public function errorInfo()
    {
        return $this->statement->error;
    }

    public function rowCount()
    {
        return $this->statement->affected_rows;
    }

    public function execute(array $input_parameters = [], ?float $timeout = null)
    {
        return $this->statement->execute($input_parameters, $timeout ?? $this->timeout);
    }

    private function __executeWhenStringQueryEmpty()
    {
        if (is_string($this->statement) && empty($this->result_set)) {
            $this->result_set = $this->parent->client->query($this->statement);
        }
    }

    public function fetch(
        int $fetch_style = \PDO::FETCH_BOTH,
        int $cursor_orientation = \PDO::FETCH_ORI_NEXT,
        int $cursor_offset = 0
    ) {
        $this->__executeWhenStringQueryEmpty();
        switch ($cursor_orientation) {
            case \PDO::FETCH_ORI_ABS:
                $this->cursor = $cursor_offset;
                break;
            case \PDO::FETCH_ORI_REL:
                $this->cursor += $cursor_offset;
                break;
            case \PDO::FETCH_ORI_NEXT:
            default:
                $this->cursor++;
        }
        $temp = $this->result_set[$this->cursor] ?? false;
        switch ($fetch_style) {
            case \PDO::FETCH_BOTH:
                $result = [];
                $i = 0;
                foreach ($temp as $key => $value) {
                    $result[$key] = $value;
                    $result[$i++] = $value;
                }
                break;
            case \PDO::FETCH_OBJ:
                $result = (object)$temp;
                break;
            case \PDO::FETCH_NUM:
                $result = array_values($temp);
                break;
            case \PDO::FETCH_ASSOC:
            default:
                $result = &$temp;
                break;
        }

        return $result;
    }

    public function fetchAll()
    {
        $this->__executeWhenStringQueryEmpty();

        return $this->result_set;
    }


}