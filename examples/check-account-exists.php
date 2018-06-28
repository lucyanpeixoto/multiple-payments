<?php

require_once '../vendor/autoload.php';

use Payment\Moip;
use Payment\Exceptions\ValidationException;
use Payment\Exceptions\RequiredArgumentException;
use Exception;

$moip = new Moip(['access_token' => '6b3f198d1d994d7da8ef482f70368963_v2']);

try {
    $account = $moip->checkAccountExists('576.174.860-65');
    pr($account); 
}catch(ValidationException $e) {
    pr($e->getMessage());
}catch(RequiredArgumentException $e) {
    pr($e->getMessage());
}catch(Exception $e) {
    pr($e->getMessage());
}