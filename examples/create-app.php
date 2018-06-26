<?php 


require_once '../vendor/autoload.php';

use Payment\Moip;

$config = [
    "token" => "IVYBKAKRP8HN9MSUJOPBP7QESIANKECF",
    "key" => "LTGUDRGBTJ8SSQUN7XMA0TVXMIUIJKM7OZQDSD2H",
];
$moip = new Moip($config);

$appData = [ 
    "name"=> "App name",
    "description"=> "App description",
    "site"=> "http://payments.local",
    "redirectUri"=> "http://payments.local/redirect/"  
];

$app = $moip->createApp($appData);

pr($app); exit;