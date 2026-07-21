<?php
class ModelExtensionShippingRajaOngkir extends Model {
    function getQuote($address) {
        $this->load->language('extension/shipping/rajaongkir');

        $status = true;

        if ($this->config->get('rajaongkir_status')) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $quote_data = array();

            // Total weight in Grams (RajaOngkir requires Grams)
            $weight = $this->cart->getWeight();
            $weight_class_id = $this->config->get('rajaongkir_weight_class_id');
            
            // Convert to grams if in Kilograms
            if ($weight_class_id == '1') { // KG
                $weight = $weight * 1000;
            }
            
            // Limit minimum weight to 100 grams
            if ($weight < 100) {
                $weight = 100;
            }

            // Origin Settings
            $origin = $this->config->get('rajaongkir_origin_city');
            $origin_type = 'city';
            
            $api_type = $this->config->get('rajaongkir_api_type') ? $this->config->get('rajaongkir_api_type') : 'starter';
            if ($api_type == 'pro') {
                $origin = $this->config->get('rajaongkir_origin_subdistrict');
                $origin_type = 'subdistrict';
            }

            // Destination from Address (Custom-injected fields or standard Zone/City)
            // For Starter/Basic: we look for address['city_id'] or query city by name
            // For Pro: we look for address['subdistrict_id']
            $destination = '';
            $destination_type = 'city';

            if ($api_type == 'pro' && isset($address['subdistrict_id']) && $address['subdistrict_id']) {
                $destination = $address['subdistrict_id'];
                $destination_type = 'subdistrict';
            } elseif (isset($address['city_id']) && $address['city_id']) {
                $destination = $address['city_id'];
                $destination_type = 'city';
            } else {
                // If ID is not directly available, find matching City ID in database by City Name
                if (isset($address['city'])) {
                    // Check local komerce_city first, fallback to rajaongkir_city
                    $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_city'");
                    if ($table_check->num_rows) {
                        $city_query = $this->db->query("SELECT city_id FROM `" . DB_PREFIX . "komerce_city` WHERE LOWER(city_name) = '" . $this->db->escape(strtolower($address['city'])) . "' LIMIT 1");
                        if ($city_query->num_rows) {
                            $destination = $city_query->row['city_id'];
                        }
                    }

                    if (!$destination) {
                        $table_check_old = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "rajaongkir_city'");
                        if ($table_check_old->num_rows) {
                            $city_query = $this->db->query("SELECT city_id FROM `" . DB_PREFIX . "rajaongkir_city` WHERE LOWER(city_name) = '" . $this->db->escape(strtolower($address['city'])) . "' LIMIT 1");
                            if ($city_query->num_rows) {
                                $destination = $city_query->row['city_id'];
                            }
                        }
                    }
                }
            }

            // Fallback to default city if destination is missing
            if (!$destination) {
                $destination = '23'; // Bandung fallback
            }

            $apiKey = $this->config->get('rajaongkir_apikey');
            $couriers = $this->config->get('rajaongkir_couriers');
            if (!$couriers) {
                $couriers = array('jne');
            }

            // Check if shipping cache table exists
            $cache_table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_shipping_cache'");
            $has_cache_table = ($cache_table_check->num_rows > 0);

            // Call RajaOngkir API or load from Cache
            foreach ($couriers as $courier) {
                $response = null;

                // Try to load from Cache first to save API quota ("Lebih Ringan")
                if ($has_cache_table) {
                    $cache_query = $this->db->query("SELECT response FROM `" . DB_PREFIX . "komerce_shipping_cache` WHERE origin = '" . $this->db->escape($origin) . "' AND origin_type = '" . $this->db->escape($origin_type) . "' AND destination = '" . $this->db->escape($destination) . "' AND destination_type = '" . $this->db->escape($destination_type) . "' AND weight = '" . (int)$weight . "' AND courier = '" . $this->db->escape($courier) . "' AND date_added > DATE_SUB(NOW(), INTERVAL 1 DAY) LIMIT 1");
                    if ($cache_query->num_rows) {
                        $response = json_decode($cache_query->row['response'], true);
                    }
                }

                if (!$response) {
                    $response = $this->callRajaOngkir($apiKey, $api_type, $origin, $origin_type, $destination, $destination_type, $weight, $courier);
                    
                    // Save response to local Cache for future requests
                    if ($response && $has_cache_table && isset($response['rajaongkir']['results'][0]['costs']) && count($response['rajaongkir']['results'][0]['costs']) > 0) {
                        $this->db->query("DELETE FROM `" . DB_PREFIX . "komerce_shipping_cache` WHERE origin = '" . $this->db->escape($origin) . "' AND origin_type = '" . $this->db->escape($origin_type) . "' AND destination = '" . $this->db->escape($destination) . "' AND destination_type = '" . $this->db->escape($destination_type) . "' AND weight = '" . (int)$weight . "' AND courier = '" . $this->db->escape($courier) . "'");
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "komerce_shipping_cache` SET origin = '" . $this->db->escape($origin) . "', origin_type = '" . $this->db->escape($origin_type) . "', destination = '" . $this->db->escape($destination) . "', destination_type = '" . $this->db->escape($destination_type) . "', weight = '" . (int)$weight . "', courier = '" . $this->db->escape($courier) . "', response = '" . $this->db->escape(json_encode($response)) . "', date_added = NOW()");
                    }
                }
                
                if ($response && isset($response['rajaongkir']['results'][0]['costs'])) {
                    $results = $response['rajaongkir']['results'][0];
                    $courier_code = $results['code'];
                    $courier_name = $results['name'];

                    foreach ($results['costs'] as $service) {
                        $service_code = strtolower($courier_code . '_' . str_replace(' ', '_', $service['service']));
                        $service_title = $courier_name . ' (' . $service['service'] . ')';
                        $cost_value = $service['cost'][0]['value'];
                        $etd = $service['cost'][0]['etd'];
                        
                        $quote_data[$service_code] = array(
                            'code'         => 'rajaongkir.' . $service_code,
                            'title'        => $service_title . ' - Est. ' . $etd . ' Hari',
                            'cost'         => $this->currency->convert($cost_value, 'IDR', $this->session->data['currency']),
                            'tax_class_id' => 0,
                            'text'         => $this->currency->format($this->currency->convert($cost_value, 'IDR', $this->session->data['currency']), $this->session->data['currency'])
                        );
                    }
                }
            }

            if ($quote_data) {
                $method_data = array(
                    'code'       => 'rajaongkir',
                    'title'      => 'RajaOngkir Pengiriman Real-Time',
                    'quote'      => $quote_data,
                    'sort_order' => $this->config->get('rajaongkir_sort_order'),
                    'error'      => false
                );
            }
        }

        return $method_data;
    }

    private function callRajaOngkir($apiKey, $api_type, $origin, $origin_type, $destination, $destination_type, $weight, $courier) {
        // Build url based on account type
        if ($api_type == 'starter') {
            $url = 'https://api.rajaongkir.com/starter/cost';
        } elseif ($api_type == 'basic') {
            $url = 'https://api.rajaongkir.com/basic/cost';
        } else { // pro
            $url = 'https://pro.rajaongkir.com/api/cost';
        }

        // Setup request headers
        $headers = array(
            "key: " . $apiKey,
            "content-type: application/x-www-form-urlencoded"
        );

        // Setup POST fields
        $fields = array(
            'origin' => $origin,
            'originType' => $origin_type,
            'destination' => $destination,
            'destinationType' => $destination_type,
            'weight' => $weight,
            'courier' => strtolower($courier)
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return false;
        } else {
            return json_decode($response, true);
        }
    }
}
?>