<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 16/3/24
 * Time: 11:57
 */

header("Content-type:text/html;charset=utf-8");
require '../vendor/autoload.php';
require 'config.php';

$youzan = new \Youzan\Youzan(AppId, AppSecret, CacheDIR);
$service = $youzan->goods();
list($items, $total) = $service->itemsOnsaleGet();
var_dump($total);
var_dump($items);
var_dump($service->getLastError());
