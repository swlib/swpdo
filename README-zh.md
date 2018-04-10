# SwPDO

[![Latest Version](https://img.shields.io/github/release/swlib/swpdo.svg?style=flat-square)](https://github.com/swlib/swpdo/releases)
[![Build Status](https://travis-ci.org/swlib/swpdo.svg?branch=master)](https://github.com/swlib/swpdo/releases)
[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=2.1.2-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![SwPDO License](https://img.shields.io/hexpm/l/plug.svg?maxAge=2592000)](https://github.com/swlib/swpdo/blob/master/LICENSE)

## 简介

0成本迁移PDO到Swoole协程客户端.

<br>

## 安装

最好的安装方法是通过 [Composer](http://getcomposer.org/) 包管理器 :

```shell
composer require swlib/saber
```

## 协程调度

Swoole底层实现协程调度, **业务层无需感知**, 开发者可以无感知的**用同步的代码编写方式达到异步IO的效果和超高性能**，避免了传统异步回调所带来的离散的代码逻辑和陷入多层回调中导致代码无法维护.

需要在`onRequet`, `onReceive`, `onConnect`等事件回调函数中使用, 或是使用go关键字包裹 (`swoole.use_shortname`默认开启).

<br>

## 例子

> 由于PDO使用了多引擎, PHP层面难以用类实现构造函数返回不同的实例.

除了构造函数有所不同, 其它使用方法完全一样

#### query

```php
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
//output: true true true true
```

#### prepare

```php
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

var_dump($pdo_fetch === $swpdo_fetch); //true
var_dump($pdo_fetch_all === $swpdo_fetch_all); //true
var_dump($pdo_fetch_all_column === $swpdo_fetch_all_column); //true
var_dump($pdo_fetch_column === $swpdo_fetch_column); //true
```

### bind

```php
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
```

