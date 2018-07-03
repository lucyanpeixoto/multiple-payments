<?php

namespace Payment;

use Payment\Exceptions\ValidationException;

class Payment {

    const PRIMARY_RECEIVER = 1;
    const SECONDARY_RECEIVER = 2;
    const CREDIT_CARD = 1;
    const BOLETO = 2;
    const ONLINE_DEBIT = 3;

    private $intermediary;

    public function __construct(Intermediary $intermediary) 
    {
        $this->intermediary = $intermediary;
    }

    public function create() 
    {
        return $this->intermediary->create();
    }

    /**
     * @return mixed
     */
    public function send() 
    {
        return $this->intermediary->send();
    }

    /**
     * @param $items
     * @return mixed
     */
    public function addItems($items) 
    {
        return $this->intermediary->addItems($items);
    }

    /**
     * @param $name
     * @param $price
     * @param int $quantity
     * @param string $description
     * @return mixed
     */
    public function addItem($name, $price, $quantity = 1, $description = '') 
    {
        return $this->intermediary->addItem($name, $price, $quantity, $description);
    }

    /**
     * @param $data
     * @throws ValidationException
     * @throws ValidationException
     */
    public function addReceiver($data) 
    {
        if (!isset($data)) {
            throw new ValidationException('$data é obrigatório', 400);
        }elseif (!is_array($data)) {
            throw new ValidationException('$data deve ser array, foi passado '.gettype($data), 400);
        }elseif (!isset($data['receiverId']) || empty($data['receiverId'])) {
            throw new ValidationException('receiver_id é obrigatório', 400);
        }elseif (isset($data['type']) && !in_array($data['type'], [self::PRIMARY_RECEIVER, self::SECONDARY_RECEIVER])) {
            throw new ValidationException('$data[\'type\'] tem que ser 1 (Primário) ou 2 (Secundário), foi passado ' . $data['type'], 400);
        }

        $data += [
            'type' => self::PRIMARY_RECEIVER,
            'fixed' => null,
            'percentage' => null,
            'processingFee' => false
        ];

        $this->intermediary->addReceiver($data);
    }

    public function addCustomer($data) 
    {
        if (!isset($data)) {
            throw new ValidationException('$data é obrigatório', 400);
        }

        $this->intermediary->addCustomer($data);
    }

    public function addUniqueId($uniqueId) 
    {
        if (!isset($uniqueId)) {
            throw new ValidationException('$uniqueId é obrigatório', 400);
        }

        $this->intermediary->addUniqueId($uniqueId);
    }

    public function addPaymentMethod($type, $data) 
    {
        if (!isset($type)) {
            throw new ValidationException('$type é obrigatório', 400);
        }elseif (!isset($data)){
            throw new ValidationException('$data é obrigatório', 400);
        }

        $this->intermediary->addPaymentMethod($type, $data);
    }
}