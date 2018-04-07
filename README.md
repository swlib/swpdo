# SwPDO

[![Latest Version](https://img.shields.io/github/release/swlib/swpdo.svg?style=flat-square)](https://github.com/swlib/swpdo/releases)
[![Build Status](https://travis-ci.org/swlib/swpdo.svg?branch=master)](https://github.com/swlib/swpdo/releases)
[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=2.1.2-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![SwPDO License](https://img.shields.io/hexpm/l/plug.svg?maxAge=2592000)](https://github.com/swlib/swpdo/blob/master/LICENSE)

## Introduction

Traditional PDO to Swoole Coroutine migration plan without cost.

<br>

## Coroutine

The bottom layer of Swoole implements coroutine scheduling, **and the business layer does not need to be aware**. Developers can use synchronization code writing methods to achieve the effect and ultra-high performance of asynchronous IO without perception, avoiding the discrete code logic and trapping caused by traditional asynchronous callbacks. Too many callback layers causes the code too difficult to maintain.

It needs to be used in event callback functions such as `onRequet`, `onReceive`, and `onConnect`, or wrapped using the go keyword (`swoole.use_shortname` is on by default).

<br>

## Example

> Because PDO uses multiple engines, it is difficult at the PHP level to return different instances with class - implemented constructors.

Except that the constructor is different, all methods use in the same way.

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
```
