<?php 

require_once '../vendor/autoload.php';

use Payment\Moip;
use Payment\Exceptions\ValidationException;
use Exception;

$config = [
    "access_token" => "6b3f198d1d994d7da8ef482f70368963_v2"
];
$moip = new Moip($config);

$dataAccount = json_decode(file_get_contents('data/create-account-data.json'), true);

try {
    $account = $moip->createAccount($dataAccount);
    pr($account); exit;
}catch (ValidationException $e) {
    pr($e->getMessage());
}catch (Exception $e) {
    pr($e->getMessage());
}
