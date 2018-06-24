<?php
namespace Payment;

use Moip\Auth\BasicAuth;
use Moip\Auth\Connect;
use Moip\Auth\OAuth;
use Moip\Exceptions\UnautorizedException;
use Moip\Exceptions\UnexpectedException;
use Moip\Exceptions\ValidationException;
use Moip\Moip as Sdk;
use Payment\Contracts\PaymentInterface;
use Payment\Exceptions\InvalidArgumentException;
use Payment\Exceptions\RequiredArgumentException;

class Moip extends Intermediary implements PaymentInterface{

    const REDIRECT_URI = 'admin/eventos/redirect_uri';
    const RECEIVER_PRIMARY = 1;
    const RECEIVER_SECONDARY = 2;

    private $moip;
    private $order;
    private $accessToken;
    private $items;
    private $receiver;
    private $customer;
    private $uniqueId;
    private $paymentMethodData;
    private $paymentMethodType;
    private $payment;

    /**
     * Moip constructor.
     * @throws RequiredArgumentException
     * @throws InvalidArgumentException
     */
    public function __construct() {

        $args = func_get_args();

        if (!empty($args)) {

            if (count($args) == 1) {
                $this->setAccessToken($args[0]);
                $this->moip = $this->auth();
            } elseif (count($args) == 2) {
                $this->setToken($args[0]);
                $this->setKey($args[1]);
                $this->moip = $this->basicAuth();
            } else {
                throw new InvalidArgumentException('');
            }

        } else {
            throw new RequiredArgumentException('a');
        }

        $this->order = $this->moip->orders();

        $this->setEndPoint(Sdk::ENDPOINT_SANDBOX);

    }

    public function addCredentials() {

            $this->setToken('IVYBKAKRP8HN9MSUJOPBP7QESIANKECF');
            $this->setKey('LTGUDRGBTJ8SSQUN7XMA0TVXMIUIJKM7OZQDSD2H');
            $this->setEndPoint(Sdk::ENDPOINT_SANDBOX);
            $this->setAccessToken($this->accessToken);
            $this->moip = $this->auth();
            $this->order = $this->moip->orders();
    }

    public function basicAuth() {
        return new Sdk(new BasicAuth($this->getToken(), $this->getKey()), Sdk::ENDPOINT_SANDBOX);
    }
    public function auth() {
        return new Sdk(new OAuth($this->getAccessToken()), $this->getEndPoint());
    }

    public function createApp($data) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->getToken() . ':' . $this->getKey())
            )
        );
        curl_setopt($ch, CURLOPT_URL, $this->getEndPoint() . "/v2/channels");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);

        $app = json_decode($server_output);
        curl_close ($ch);

        return $app;
    }

    public function getAuthUrl($clientId) {
        /*$connect = new Connect(\Router::url('/', true) . self::REDIRECT_URI, $clientId, true, \Configure::read('AmbienteProducaoMoip') ? Connect::ENDPOINT_PRODUCTION : Connect::ENDPOINT_SANDBOX);
        $connect->setScope(Connect::RECEIVE_FUNDS)
            ->setScope(Connect::REFUND)
            ->setScope(Connect::MANAGE_ACCOUNT_INFO)
            ->setScope(Connect::RETRIEVE_FINANCIAL_INFO);
        return $connect->getAuthUrl();*/
    }

    public function oAuth($clientId, $clientSecret, $code) {
        /*try {
            $connect = new Connect(\Router::url('/', true) . self::REDIRECT_URI, $clientId, true, \Configure::read('AmbienteProducaoMoip') ? Connect::ENDPOINT_PRODUCTION : Connect::ENDPOINT_SANDBOX);
            $connect->setClientSecret($clientSecret);
            $connect->setCode($code);
            return $connect->authorize();
        }catch (UnexpectedException $e) {
            pr($e->getMessage());
        }catch (ValidationException $e) {
            pr($e->getMessage());
        }*/
    }

    public function checkAccountExists($identity_document){

        try{
            $moip = $this->auth();
            return $moip->accounts()->checkExistence($identity_document);
        }catch (UnexpectedException $e) {
            pr('UnexpectedException');
            pr($e->getMessage());
        }catch (ValidationException $e) {
            pr('ValidationException');
            pr($e->getErrors());
        }


    }


    public function consultAccount(){

        /*try{
            $account_id = 'MPA-8307EF11B83E';
            $account = $this->moip->accounts()->get($account_id);
            pr($account);
        }catch (UnexpectedException $e) {
            pr('UnexpectedException');
            pr($e->getMessage());
        }catch (ValidationException $e) {
            pr('ValidationException');
            pr($e->getErrors());
        }catch(\UnauthorizedException $e){
            pr('UnauthorizedException');
            pr($e->getMessage());
        }*/

    }


    public function createAccount(){

        try {
            $street = 'Rua de teste';
            $number = 123;
            $district = 'Bairro';
            $city = 'Sao Paulo';
            $state = 'SP';
            $zip = '01234567';
            $complement = 'Apt. 23';
            $country = 'BRA';
            $area_code = 11;
            $phone_number = 66778899;
            $country_code = 55;
            $identity_document = '4737283560';
            $issuer = 'SSP';
            $issue_date = '2015-06-23';
            return $this->moip->accounts()
                ->setName('Fulano')
                ->setLastName('De Tal')
                ->setEmail('fulano@emailqqq2d2.com')
                ->setIdentityDocument($identity_document, $issuer, $issue_date)
                ->setBirthDate('1988-12-30')
                ->setTaxDocument('16262131000')
                ->setType('MERCHANT')
                ->setPhone($area_code, $phone_number, $country_code)
                ->addAlternativePhone(11, 66448899, 55)
                ->addAddress($street, $number, $district, $city, $state, $zip, $complement, $country)
                ->setCompanyName('Empresa Teste', 'Teste Empresa ME')
                ->setCompanyOpeningDate('2011-01-01')
                ->setCompanyPhone(11, 66558899, 55)
                ->setCompanyTaxDocument('69086878000198')
                ->setCompanyAddress('Rua de teste 2', 123, 'Bairro Teste', 'Sao Paulo', 'SP', '01234567', 'Apt. 23', 'BRA')
                ->setCompanyMainActivity('82.91-1/00', 'Atividades de cobranças e informações cadastrais')
                ->create();
        }catch (UnexpectedException $e) {
            pr($e->getMessage());
        }catch (ValidationException $e) {
            pr($e->getErrors());
        }



    }

    public function createTransparentAccount(){


        try{

            $account = $this->moip->accounts()
                ->setName('Fulano')
                ->setLastName('De Tal')
                ->setEmail('fulano@email2.com')
                ->setIdentityDocument('4737283560', 'SSP', '2015-06-23')
                ->setBirthDate('1988-12-30')
                ->setTaxDocument('16262131000')
                ->setType('MERCHANT')
                ->setTransparentAccount(true)
                ->setPhone(11, 66778899, 55)
                ->addAlternativePhone(11, 66448899, 55)
                ->addAddress('Rua de teste', 123, 'Bairro', 'Sao Paulo', 'SP', '01234567', 'Apt. 23', 'BRA')
                ->setCompanyName('Empresa Teste', 'Teste Empresa ME')
                ->setCompanyOpeningDate('2011-01-01')
                ->setCompanyPhone(11, 66558899, 55)
                ->setCompanyTaxDocument('69086878000198')
                ->setCompanyAddress('Rua de teste 2', 123, 'Bairro Teste', 'Sao Paulo', 'SP', '01234567', 'Apt. 23', 'BRA')
                ->setCompanyMainActivity('82.91-1/00', 'Atividades de cobranças e informações cadastrais')
                ->create();

                pr($account); exit;

        }catch (UnexpectedException $e) {
            pr($e->getMessage());
        }catch (ValidationException $e) {
            pr($e->getMessage());
        }


    }

    public function send() {

        try {
            //Criando um pedido
            $this->order = $this->order->create();
            //Tratando forma de pagamento
            $this->setPaymentMethod();
            //Criando um pagamento
            return $this->payment->execute();

        }catch (UnexpectedException $e) {
            pr($e->getMessage());
        }catch (ValidationException $e) {
            pr($e->getErrors());
        }catch (UnautorizedException $e) {
            pr($e->getMessage());
        }
    }

    public function payment() {

        try {
            //Criando um pedido
            $this->order = $this->order->create();
            //Tratando forma de pagamento
            $this->setPaymentMethod();
            //Criando um pagamento
            return $this->payment->execute();

        }catch (UnexpectedException $e) {
            pr($e->getMessage());
        }catch (ValidationException $e) {
            pr($e->getErrors());
        }catch (UnautorizedException $e) {
            pr($e->getMessage());
        }
    }

    public function addItems($items) {
        foreach($items as $key => $item) {
            //$item = new Item($item['name'], $item['price'], $item['quantity'], $item['description']);
            $this->order->addItem($item['name'], $item['quantity'], $item['description'], $item['price']);
        }
    }

    public function addItem($name, $price, $quantity = 1, $description = '') {
        //$item = new Item($name, $price, $quantity, $description);
        $this->order->addItem($name, $quantity, $description, $price);
    }

    public function order($data) {
        $order = new Order();
        $order->setName($data['name']);

        return [];
    }

    public function payments(array $data) {
        $data = ['items' => $this->items, 'receiver' => $this->receiver];
        return $data;
    }

    public function addReceiver($data) {

        $data['type'] = $data['type'] == self::PRIMARY_RECEIVER ? 'PRIMARY' : 'SECONDARY';

        $this->order->addReceiver($data['receiverId'], $data['type'], $data['fixed'], $data['percentage'], $data['processingFee']);
    }

    public function getItems() {
        return $this->items;
    }

    public function addCustomer($data) {
        $customer = $this->moip->customers()->setOwnId(uniqid())
            ->setFullname($data['name'] . ' ' . $data['lastName'])
            ->setEmail($data['email'])
            ->setTaxDocument($data['taxDocument'])
            ->setPhone(substr($data['phone'], 0, 2), substr($data['phone'], 2, 9))
            ->addAddress('BILLING',
                $data['street'], $data['number'],
                $data['district'], $data['city'], $data['state'],
                $data['zip'], $data['complement'], $data['country'])
            ->addAddress('SHIPPING',
                $data['street'], $data['number'],
                $data['district'], $data['city'], $data['state'],
                $data['zip'], $data['complement'], $data['country'])
            ->create();

        $this->order->setCustomer($customer);

    }

    public function addUniqueId($compraId) {
        $this->order->setOwnId($compraId);
    }

    public function setPaymentMethod() {

        if ($this->paymentMethodType == self::BOLETO) {
            $logo_uri = 'https://cdn.moip.com.br/wp-content/uploads/2016/05/02163352/logo-moip.png';
            $expiration_date = new \DateTime();
            $instruction_lines = ['INSTRUÇÃO 1', 'INSTRUÇÃO 2', 'INSTRUÇÃO 3'];

            $this->payment = $this->order->payments()
                ->setBoleto($expiration_date, $logo_uri, $instruction_lines);
        }
    }

    public function addPaymentMethod($type, $data) {
        $this->paymentMethodType = $type;
        $this->paymentMethodData = $data;
    }
}