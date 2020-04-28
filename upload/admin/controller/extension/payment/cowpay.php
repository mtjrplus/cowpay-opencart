<?php
class ControllerExtensionPaymentCowpay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/cowpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_cowpay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
        }
        
        if (isset($this->error['merchant_code'])) {
			$data['error_merchant_code'] = $this->error['merchant_code'];
		} else {
			$data['error_merchant_code'] = '';
		}
        
        if (isset($this->error['merchant_hash_key'])) {
			$data['error_merchant_hash_key'] = $this->error['merchant_hash_key'];
		} else {
			$data['error_merchant_hash_key'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/cowpay', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/cowpay', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_cowpay_merchant_code'])) {
			$data['payment_cowpay_merchant_code'] = $this->request->post['payment_cowpay_merchant_code'];
		} else {
			$data['payment_cowpay_merchant_code'] = $this->config->get('payment_cowpay_merchant_code');
		}

		if (isset($this->request->post['payment_cowpay_merchant_hash_key'])) {
			$data['payment_cowpay_merchant_hash_key'] = $this->request->post['payment_cowpay_merchant_hash_key'];
		} else {
			$data['payment_cowpay_merchant_hash_key'] = $this->config->get('payment_cowpay_merchant_hash_key');
		}

		if (isset($this->request->post['payment_cowpay_merchant_email'])) {
			$data['payment_cowpay_merchant_email'] = $this->request->post['payment_cowpay_merchant_email'];
		} else {
			$data['payment_cowpay_merchant_email'] = $this->config->get('payment_cowpay_merchant_email');
        }
        
        if (isset($this->request->post['payment_cowpay_order_status_id'])) {
			$data['payment_cowpay_order_status_id'] = $this->request->post['payment_cowpay_order_status_id'];
		} else {
			$data['payment_cowpay_order_status_id'] = $this->config->get('payment_cowpay_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_cowpay_geo_zone_id'])) {
			$data['payment_cowpay_geo_zone_id'] = $this->request->post['payment_cowpay_geo_zone_id'];
		} else {
			$data['payment_cowpay_geo_zone_id'] = $this->config->get('payment_cowpay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_cowpay_status'])) {
			$data['payment_cowpay_status'] = $this->request->post['payment_cowpay_status'];
		} else {
			$data['payment_cowpay_status'] = $this->config->get('payment_cowpay_status');
		}

		if (isset($this->request->post['payment_cowpay_title'])) {
			$data['payment_cowpay_title'] = $this->request->post['payment_cowpay_title'];
		} else {
			$data['payment_cowpay_title'] = $this->config->get('payment_cowpay_title');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/cowpay', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/cowpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_cowpay_merchant_code']) {
			$this->error['merchant_code'] = $this->language->get('error_merchant_code');
		}

		if (!$this->request->post['payment_cowpay_merchant_hash_key']) {
			$this->error['merchant_hash_key'] = $this->language->get('error_merchant_hash_key');
		}
	
		return !$this->error;
	}
}
