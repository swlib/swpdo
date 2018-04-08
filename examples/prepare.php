<?php
/**
 * Copyright: Swlib
 * Author: Twosee <twose@qq.com>
 * Date: 2018/4/7 下午11:53
 */

use Swlib\SwPDO;

require __DIR__ . '/../vendor/autoload.php';

go(function () {
    $options = [
        'mysql:host=127.0.0.1;dbname=test;charset=UTF8',
        'root',
        'root'
    ];
    $sql = 'select * from `user`';

    //PDO
    $pdo = new \PDO(...$options);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //strong type
    $statement = $pdo->prepare($sql);
    $statement->execute();
    $pdo_fetch = $statement->fetch(\PDO::FETCH_ASSOC);
    $statement->execute();
    $pdo_fetch_all = $statement->fetchAll();
    $statement->execute();
    $pdo_fetch_all_column = $statement->fetchAll(\PDO::FETCH_COLUMN, 1);
    $statement->execute();
    $pdo_fetch_column = $statement->fetchColumn();
    $statement->execute();

    //SwPDO
    $swpdo = SwPDO::construct(...$options);
    $statement = $swpdo->prepare($sql);
    $statement->execute();
    $swpdo_fetch = $statement->fetch(\PDO::FETCH_ASSOC);
    $statement->execute();
    $swpdo_fetch_all = $statement->fetchAll();
    $statement->execute();
    $swpdo_fetch_all_column = $statement->fetchAll(\PDO::FETCH_COLUMN, 1);
    $statement->execute();
    $swpdo_fetch_column = $statement->fetchColumn();
    $statement->execute();

    var_dump($pdo_fetch === $swpdo_fetch);
    var_dump($pdo_fetch_all === $swpdo_fetch_all);
    var_dump($pdo_fetch_all_column === $swpdo_fetch_all_column);
    var_dump($pdo_fetch_column === $swpdo_fetch_column);
});