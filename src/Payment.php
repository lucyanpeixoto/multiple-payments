<?php

namespace Payment;

use Payment\Exceptions\InvalidArgumentException;
use Payment\Exceptions\RequiredArgumentException;

class Payment {

    const PRIMARY_RECEIVER = 1;
    const SECONDARY_RECEIVER = 2;

    private $intermediary;

    public function __construct(Intermediary $intermediary) {
        $this->intermediary = $intermediary;
    }

    public function auth() {
        return $this->intermediary->auth();
    }

    public function basicAuth() {
        return $this->intermediary->basicAuth();
    }

    public function test() {
        return $this->intermediary->test();
    }

    public function orders($id) {
        return $this->intermediary->orders($id);
    }

    public function create() {
        return $this->intermediary->create();
    }

    public function payment(array $data) {
        return $this->intermediary->payment($data);
    }

    public function send() {
        return $this->intermediary->send();
    }

    public function addItems($items) {
        return $this->intermediary->addItems($items);
    }

    public function addItem($name, $price, $quantity = 1, $description = '') {
        return $this->intermediary->addItem($name, $price, $quantity, $description);
    }

    public function addReceiver($data) {

        if (!isset($data)) {
            throw new RequiredArgumentException('$data é obrigatório', 400);
        }elseif (!is_array($data)) {
            throw new InvalidArgumentException('$data deve ser array, foi passado '.gettype($data), 400);
        }elseif (!isset($data['receiverId']) || empty($data['receiverId'])) {
            throw new RequiredArgumentException('receiver_id é obrigatório', 400);
        }elseif (isset($data['type']) && !in_array($data['type'], [self::PRIMARY_RECEIVER, self::SECONDARY_RECEIVER])) {
            throw new InvalidArgumentException('$data[\'type\'] tem que ser 1 (Primário) ou 2 (Secundário), foi passado ' . $data['type'], 400);
        }

        $data += [
            'type' => self::PRIMARY_RECEIVER,
            'fixed' => null,
            'percentage' => null,
            'processingFee' => false
        ];

        $this->intermediary->addReceiver($data);
    }

    public function addCustomer($data) {
        $this->intermediary->addCustomer($data);
    }

    public function addUniqueId($compraId) {
        $this->intermediary->addUniqueId($compraId);
    }

    public function addPaymentMethod($type, $data) {
        $this->intermediary->addPaymentMethod($type, $data);
    }

    public function getItems() {
        return $this->intermediary->getItems();
    }

    public function order($data) {
        return $this->intermediary->order($data);
    }
}