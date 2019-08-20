<?php 

namespace Payment;

class Customer
{
    use \Rakshazi\GetSetTrait;

    private $name;
    private $lastName;
    private $email;
    private $tax_document;
    private $phone;
    private $street;
    private $number;
    private $district;
    private $city;
    private $state;
    private $zip;
    private $complement;
    private $country;

}