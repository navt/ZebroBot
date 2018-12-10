<?php
use tools\ask\SimpleAsk;
use tools\Basis;
use tools\access\SQLiteAccess;
use tools\Route;

$basePath = dirname(dirname(__FILE__));
require $basePath.'/vendor/autoload.php';

$ask   = new SimpleAsk();
$basis = new Basis($ask);
$DB    = new SQLiteAccess($basis);
$route = new Route($basis, $DB);

$route->checkUpdateType();
