<?php
use tools\ask\SimpleAsk;
use tools\Basis;
use \tools\access\FileAccess;
use tools\Route;

$basePath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
define("BASE_PATH", $basePath);

require BASE_PATH.'vendor/autoload.php';

$ask = new SimpleAsk();
$basis = new Basis($ask);
$FA = new FileAccess($basis);
$route = new Route($basis, $FA);

$route->checkUpdateType();
