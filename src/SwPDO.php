<?php
/**
 * Copyright: Swlib
 * Author: Twosee <twose@qq.com>
 * Date: 2018/4/7 下午4:04
 */

namespace Swlib;

class SwPDO
{

    /**
     * SwPDO constructor.
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $driver_options
     *
     * @return SwPDO\Mysql
     */
    public static function construct(
        string $dsn,
        string $username = '',
        string $password = '',
        array $driver_options = []
    ) {
        $dsn = explode(':', $dsn);
        $driver = ucwords(array_shift($dsn));
        $dsn = explode(';', implode(':', $dsn));
        $options = [];
        foreach ($dsn as $kv) {
            $kv = explode('=', $kv);
            if ($kv) {
                $options[$kv[0]] = $kv[1] ?? '';
            }
        }
        $authorization = [
            'user' => $username,
            'password' => $password,
        ];
        $options = $driver_options + $authorization + $options;
        $class = SwPDO::class . "\\$driver";

        return $class::construct($options);
    }

    public static function getAvailableDrivers()
    {
        return ['MySQL'];
    }

}