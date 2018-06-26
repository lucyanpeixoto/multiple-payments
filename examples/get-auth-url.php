<?php 


require_once '../vendor/autoload.php';

use Payment\Moip;

$moip = new Moip();

$data = [
    "appId" => 'APP-AKYEZZOJQS9T',
    "redirectUri" => "http://payments.local/redirect/"
];

$auth = $moip->getAuthUrl($data);

pr($auth); exit;
header("Location:$auth");