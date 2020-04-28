<?php

class Cowpay {
    private $merchant_code;
    private $merchant_hash_key;
    private $signature;

    private $customer_data = array();
    private $card_data = array();
    private $order_data = array();

    private $api_url = 'https://cowpay.me/api/fawry/charge-request-cc';

    public function __construct($data) {
        $this->merchant_code = $data['merchant_code'];
        $this->merchant_hash_key = $data['merchant_hash_key'];
    }
    
    // customer_merchant_profile_id, customer_name, customer_mobile, customer_email
    public function setCustomerData($data) {
        $this->customer_data = $data;
    }

    // merchant_reference_id, payment_method, amount, currency_code, description, charge_items
    public function setOrderData($data) {
        $this->order_data = $data;
    }

    // card_number, expiry_year, expiry_month, cvv, save_card
    public function setCardData($data) {
        $this->card_data = $data;
    }

    public function setSignature() {

        $plain_signature = $this->merchant_code;
        $plain_signature .= $this->order_data['merchant_reference_id'];
        $plain_signature .= $this->customer_data['customer_merchant_profile_id'];
        $plain_signature .= $this->order_data['payment_method'];
        $plain_signature .= $this->order_data['amount'];
        $plain_signature .= $this->card_data['card_token'];
        $plain_signature .= $this->merchant_hash_key;
        
        $this->signature = hash('sha256', $plain_signature);
    }

    public function callApi() {
        $api_data = array(
            'merchant_code' => $this->merchant_code,
            'signature' => $this->signature
        );
        $post_data = array_merge($this->card_data, $this->customer_data, $this->order_data, $api_data);
        
        return json_decode($this->post_curl($post_data), 1);
    }

    public function post_curl($post_data) {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL,
            $this->api_url
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data, '', '&'));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}