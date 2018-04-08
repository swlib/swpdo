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
    $sql = 'select * from `user` where id=:id';
    $id = 1;

    //PDO
    $pdo = new \PDO(...$options);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //strong type
    $statement = $pdo->prepare($sql);
    $statement->execute(['id' => 1]);
    $pdo_bind_exec = $statement->fetch(\PDO::FETCH_ASSOC);

    $statement->bindValue(':id', 1);
    $statement->execute();
    $pdo_bind_val = $statement->fetch(\PDO::FETCH_ASSOC);

    $statement->bindParam(':id', $id);
    $statement->execute();
    $pdo_bind_param = $statement->fetch(\PDO::FETCH_ASSOC);

    //SwPDO
    $swpdo = SwPDO::construct(...$options);
    $statement = $swpdo->prepare($sql);
    $statement->execute(['id' => 1]);
    $swpdo_bind_exec = $statement->fetch(\PDO::FETCH_ASSOC);

    $statement->bindValue(':id', 1);
    $statement->execute();
    $swpdo_bind_val = $statement->fetch(\PDO::FETCH_ASSOC);

    $statement->bindParam(':id', $id);
    $statement->execute();
    $swpdo_bind_param = $statement->fetch(\PDO::FETCH_ASSOC);

    var_dump($pdo_bind_exec === $swpdo_bind_exec); //true
    var_dump($pdo_bind_val === $swpdo_bind_val); //true
    var_dump($pdo_bind_param === $swpdo_bind_param); //true
});