<?php
use tools\WebHook;
use tools\ask\SimpleAsk;
use tools\AppException;

$basePath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath);
require BASE_PATH.'vendor/autoload.php';

$ask = new SimpleAsk();

$wh = new WebHook($ask);

try {
    //var_dump($wh->init());      // установка webHook
    var_dump($wh->getInfo());     // проверка установки
} catch (AppException $e) {
    echo $e->getMessage();
}

