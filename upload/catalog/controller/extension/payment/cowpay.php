<?php

class ControllerExtensionPaymentCowpay extends Controller
{
    private $error = array();

    public function index()
    {

        $this->load->language('extension/payment/cowpay');

        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['form_action'] = $this->url->link('extension/payment/cowpay/confirm');

        return $this->load->view('extension/payment/cowpay', $data);
    }

    public function confirm()
    {
        $this->load->language('extension/payment/cowpay');

        $json = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $cowpay_data = array(
                'merchant_code' => $this->config->get('payment_cowpay_merchant_code'),
                'merchant_hash_key' => $this->config->get('payment_cowpay_merchant_hash_key'),
            );
            $cowpay = new Cowpay($cowpay_data);

            /* ************************ */

            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $cardname = $this->request->post['cardname'];

            $customer_data = array(
                'customer_merchant_profile_id' => $order_info['customer_id'],
                'customer_name' => $cardname,
                'customer_mobile' => $order_info['telephone'],
                'customer_email' => $order_info['email'],
            );
            $cowpay->setCustomerData($customer_data);

            /* ************************ */

            $charge_items = array();

            $products = $this->cart->getProducts();

            foreach ($products as $product) {
                $charge_items[] = array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'description' => $product['name'],
                    'quantity' => $product['quantity'],
                    'price' => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false),
                );
            }

            $order_data = array(
                'merchant_reference_id' => $order_info['order_id'],
                'payment_method' => 'CARD',
                'currency_code' => 'EGP',
                'amount' => number_format((float) $order_info['total'], 2, '.', ''),
                'description' => 'Description',
                'charge_items' => json_encode($charge_items),
            );
            $cowpay->setOrderData($order_data);

            /* ************************ */

            $cardnumber = $this->request->post['cardnumber'];
            $expirationdate = $this->request->post['expirationdate'];
            $expiry_month = explode('/', $expirationdate)[0];
            $expiry_year = explode('/', $expirationdate)[1];
            $securitycode = $this->request->post['securitycode'];

            $card_data = array(
                'card_number' => str_replace(' ', '', $cardnumber),
                'expiry_year' => $expiry_year,
                'expiry_month' => $expiry_month,
                'cvv' => $securitycode,
                'save_card' => 0,
                'card_token' => '',
            );
            $cowpay->setCardData($card_data);

            /* ************************ */

            $cowpay->setSignature();

            /* ************************ */

            $result = $cowpay->callApi();

            switch ($result['status_code']) {
                case '200':
                    $order_note = "payment_gateway_reference_id is: " . $result['payment_gateway_reference_id'];
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cowpay_order_status_id'), $order_note, false);
                    $json['redirect'] = 'index.php?route=checkout/success';
                    break;

                case '422':
                    $json['error'] = $this->language->get('error_invalid');
                    break;

                case '4050':
                    $json['error'] = $this->language->get('error_insufficient');
                    break;

                default:
                    $json['error'] = $this->language->get('error_failed');
                    $json['redirect'] = 'index.php?route=checkout/checkout';
                    break;
            }

            $cowpay_log = new Log('cowpay.log');
            $cowpay_log->write([
                'order_id' => $order_info['order_id'],
                'response' => $result
            ]);

        }

        if ($this->error) {
            $json['error'] = $this->error['warning'];
        }

        $json['validate'] = $this->validate();

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

    }

    protected function validate()
    {

        if (!isset($this->request->post['cardname']) || !$this->request->post['cardname']) {
            $this->error['warning'] = $this->language->get('error_required');
        }
        if (!isset($this->request->post['cardnumber']) || utf8_strlen($this->request->post['cardnumber']) < 16) {
            $this->error['warning'] = $this->language->get('error_required');
        }
        if (!isset($this->request->post['expirationdate']) || !$this->request->post['expirationdate']) {
            $this->error['warning'] = $this->language->get('error_required');
        }
        if (!isset($this->request->post['securitycode']) || utf8_strlen($this->request->post['securitycode']) < 3) {
            $this->error['warning'] = $this->language->get('error_required');
        }

        return !$this->error;
    }
}
