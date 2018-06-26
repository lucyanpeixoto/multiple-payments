<?php

require_once '../vendor/autoload.php';

use Payment\Moip;

$config = json_decode(file_get_contents('data/config.json'), true);
$moip = new Moip($config);
$account = $moip->getAppAccount();
pr($account); exit;