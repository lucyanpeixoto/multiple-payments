<?php

require_once '../../vendor/autoload.php';

use Payment\PagarMe;
use Payment\Exceptions\ValidationException;

$bankCode = '341';
$agenciaNumber = '0932';
$accountNumber = '58054';
$accountDigit = '5';
$documentNumber = '26268738888';
$legalName = 'Conta Teste 1';
$agenciaDigit = '1';

$data = [
    'bankCode' => $bankCode,
    'agencyNumber' => $agenciaNumber,
    'agencyDigit' => $agenciaDigit,
    'accountNumber' => $accountNumber,
    'accountDigit' => $accountDigit,
    'taxDocument' => $documentNumber,
    'holder' => $legalName,
    'accountType' => ''
];

$pagarMe = new PagarMe(['access_token' => 'ak_test_oVkp8o1tBmYJR3GtbQ9L0Nkx8ygHM7']);
try {
    $receiver = $pagarMe->createReceiver($data);
    pr($receiver);
} catch (ValidationException $e) {
    pr($e->getMessage());
}

