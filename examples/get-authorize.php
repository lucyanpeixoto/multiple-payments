<?php

require_once '../vendor/autoload.php';

use Payment\Moip;

$moip = new Moip();

$data = [
    "appId" => 'APP-AKYEZZOJQS9T',
    "redirectUri" => "http://payments.local/redirect/",
    "code" => "7d35b1f533124585017f4b2094a2a8c969df670c",
    'secret' => "e8b9ac893e9c4bb6a6ac4f8ccfb04832",
];

$oauth = $moip->getOAuth($data);

pr($oauth); 