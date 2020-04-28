<?php
class ModelExtensionPaymentCowpay extends Model {
	public function getMethod($address, $total) {
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_cowpay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('payment_cowpay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
        }
        
        if ($status) {
            $method_data = array(
                'code'       => 'cowpay',
                'title'      => $this->config->get('payment_cowpay_title'),
                'terms'      => '',
                'sort_order' => 10
            );
        }

        return $method_data;
	}
}