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

    public function bindParam($parameter, &$variable)
    {
        if (!is_string($parameter) && !is_int($parameter)) {
            return false;
        }
        $parameter = ltrim($parameter, ':');
        $this->bindMap[$parameter] = &$variable;

        return true;
    }

    public function bindValue($parameter, $variable)
    {
        if (!is_string($parameter) && !is_int($parameter)) {
            return false;
        }
        if (is_object($variable)) {
            if (!method_exists($variable, '__toString')) {
                return false;
            } else {
                $variable = (string)$variable;
            }
        }
        $parameter = ltrim($parameter, ':');
        $this->bindMap[$parameter] = $variable;

        return true;
    }

    private function afterExecute()
    {
        $this->cursor = -1;
        $this->bindMap = [];
    }

    public function execute(array $input_parameters = [], ?float $timeout = null)
    {
        if (!empty($input_parameters)) {
            foreach ($input_parameters as $key => $value) {
                $this->bindParam($key, $value);
            }
        }
        $input_parameters = [];
        if (!empty($this->statement->bindKeyMap)) {
            foreach ($this->statement->bindKeyMap as $name_key => $num_key) {
                $input_parameters[$num_key] = $this->bindMap[$name_key];
            }
        } else {
            $input_parameters = $this->bindMap;
        }
        $r = $this->statement->execute($input_parameters, $timeout ?? $this->timeout);
        $this->result_set = ($ok = $r !== false) ? $r : [];
        $this->afterExecute();

        return $ok;
    }

    private function __executeWhenStringQueryEmpty()
    {
        if (is_string($this->statement) && empty($this->result_set)) {
            $this->result_set = $this->parent->client->query($this->statement);
            $this->afterExecute();
        }
    }

    private static function transBoth($raw_data)
    {
        $temp = [];
        foreach ($raw_data as $row) {
            $row_set = [];
            $i = 0;
            foreach ($row as $key => $value) {
                $row_set[$key] = $value;
                $row_set[$i++] = $value;
            }
            $temp[] = $row_set;
        }

        return $temp;
    }

    private static function transStyle(
        $raw_data,
        int $fetch_style = \PDO::FETCH_BOTH,
        $fetch_argument = null,
        array $ctor_args = []
    ) {
        if (!is_array($raw_data)) {
            return false;
        }
        if (empty($raw_data)) {
            return $raw_data;
        }
        $result_set = [];
        switch ($fetch_style) {
            case \PDO::FETCH_BOTH:
                $result_set = self::transBoth($raw_data);
                break;
            case \PDO::FETCH_COLUMN:
                $result_set = array_column(
                    is_numeric($fetch_argument) ? self::transBoth($raw_data) : $raw_data,
                    $fetch_argument
                );
                break;
            case \PDO::FETCH_OBJ:
                foreach ($raw_data as $row) {
                    $result_set[] = (object)$row;
                }
                break;
            case \PDO::FETCH_NUM:
                foreach ($raw_data as $row) {
                    $result_set[] = array_values($row);
                }
                break;
            case \PDO::FETCH_ASSOC:
            default:
                return $raw_data;
        }

        return $result_set;
    }

    public function fetch(
        int $fetch_style = \PDO::FETCH_BOTH,
        int $cursor_orientation = \PDO::FETCH_ORI_NEXT,
        int $cursor_offset = 0,
        $fetch_argument = null
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

        if (isset($this->result_set[$this->cursor])) {
            $result = $this->result_set[$this->cursor];
            unset($this->result_set[$this->cursor]);
        } else {
            $result = false;
        }

        if (empty($result)) {
            return $result;
        } else {
            return self::transStyle([$result], $fetch_style, $fetch_argument)[0];
        }
    }

    /**
     * Returns a single column from the next row of a result set or FALSE if there are no more rows.
     *
     * @param int $column_number
     * 0-indexed number of the column you wish to retrieve from the row.
     * If no value is supplied, PDOStatement::fetchColumn() fetches the first column.
     *
     * @return bool|mixed
     */
    public function fetchColumn(int $column_number = 0)
    {
        $this->__executeWhenStringQueryEmpty();
        return $this->fetch(\PDO::FETCH_COLUMN, \PDO::FETCH_ORI_NEXT, 0, $column_number);
    }

    public function fetchAll(int $fetch_style = \PDO::FETCH_BOTH, $fetch_argument = null, array $ctor_args = [])
    {
        $this->__executeWhenStringQueryEmpty();
        $result_set = self::transStyle($this->result_set, $fetch_style, $fetch_argument, $ctor_args);
        $this->result_set = [];

        return $result_set;
    }

}