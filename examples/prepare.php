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
        'mysql:host=127.0.0.1;port=9502;dbname=custed;charset=UTF8',
        'php',
        'justfortest'
    ];

    //PDO
    $pdo = new \PDO(...$options);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //strong type
    $statement = $pdo->prepare('select * from `user`');
    $statement->execute();
    $pdo_fetch = $statement->fetch(\PDO::FETCH_ASSOC);

    //PDO
    $swpdo = SwPDO::construct(...$options);
    $statement = $swpdo->prepare('select * from `user`');
    $statement->execute();
    $swpdo_fetch = $statement->fetch(\PDO::FETCH_ASSOC);

    var_dump($pdo_fetch === $swpdo_fetch);
});