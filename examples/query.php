<?php
/**
 * Copyright: Swlib
 * Author: Twosee <twose@qq.com>
 * Date: 2018/4/7 下午4:08
 */

use Swlib\SwPDO;

require __DIR__ . '/../vendor/autoload.php';

go(function () {
    $options = [
        'mysql:host=127.0.0.1;dbname=test;charset=UTF8',
        'root',
        'root'
    ];
    $sql = 'select * from `user` LIMIT 1';

    //PDO
    $pdo = new \PDO(...$options);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //strong type
    $pdo_both = $pdo->query($sql)->fetch();
    $pdo_assoc = $pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
    $pdo_object = $pdo->query($sql)->fetch(\PDO::FETCH_OBJ);
    $pdo_number = $pdo->query($sql)->fetch(\PDO::FETCH_NUM);

    //SwPDO
    $swpdo = SwPDO::construct(...$options); //default is strong type
    $swpdo_both = $swpdo->query($sql)->fetch();
    $swpdo_assoc = $swpdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
    $swpdo_object = $swpdo->query($sql)->fetch(\PDO::FETCH_OBJ);
    $swpdo_number = $swpdo->query($sql)->fetch(\PDO::FETCH_NUM);

    var_dump($pdo_both === $swpdo_both);
    var_dump($pdo_assoc === $swpdo_assoc);
    var_dump($pdo_object == $swpdo_object);
    var_dump($pdo_number === $swpdo_number);
});