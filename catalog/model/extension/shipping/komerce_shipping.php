<?php
/**
 * @package     Komerce Shipping Method Model with RajaOngkir integration (V2 OpenAPI)
 * @author      Zoraya Developer Team
 * @description Highly optimized database caching & synchronization to reduce API overhead and accelerate checkout.
 */
class ModelExtensionShippingKomerceShipping extends Model {
    public function getQuote($address) {
        $this->load->language('extension/shipping/komerce_shipping');

        $quote_data = array();

        // Check if module active
        if (!$this->config->get('komerce_shipping_status')) {
            return array();
        }

        // Get shipping weight from cart
        $weight = $this->cart->getWeight();
        if ($weight <= 0) {
            $weight = (float)$this->config->get('komerce_shipping_default_weight');
        }
        if ($weight <= 0) {
            $weight = 1000; // Default to 1kg if still 0
        }

        // Target Subdistrict ID (Customer address code via Custom Fields or standard address matching)
        $destination_subdistrict_id = '';
        
        if (isset($address['subdistrict_id']) && !empty($address['subdistrict_id'])) {
            $destination_subdistrict_id = $address['subdistrict_id'];
        } elseif (isset($address['custom_field'][$this->config->get('komerce_shipping_subdistrict_field_id')]) && !empty($address['custom_field'][$this->config->get('komerce_shipping_subdistrict_field_id')])) {
            $destination_subdistrict_id = $address['custom_field'][$this->config->get('komerce_shipping_subdistrict_field_id')];
        }

        // Extremely robust text-matching fallback if subdistrict ID is empty or not numeric
        if (empty($destination_subdistrict_id) || !is_numeric($destination_subdistrict_id)) {
            $search_term = '';
            if (isset($address['address_1'])) {
                $search_term .= ' ' . $address['address_1'];
            }
            if (isset($address['address_2'])) {
                $search_term .= ' ' . $address['address_2'];
            }
            if (isset($address['city'])) {
                $search_term .= ' ' . $address['city'];
            }

            // Search the local database table if it exists
            $table_query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'");
            if ($table_query->num_rows && !empty(trim($search_term))) {
                // Tokenize and clean search terms
                $words = explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', ' ', strtolower($search_term)));
                $words = array_filter($words, function($w) { return strlen($w) > 3; });
                
                if (!empty($words)) {
                    $like_clauses = array();
                    foreach ($words as $w) {
                        $like_clauses[] = "LOWER(subdistrict_name) LIKE '%" . $this->db->escape($w) . "%'";
                    }
                    $sub_search = $this->db->query("SELECT subdistrict_id FROM " . DB_PREFIX . "komerce_subdistrict WHERE " . implode(" OR ", $like_clauses) . " LIMIT 1");
                    if ($sub_search->num_rows) {
                        $destination_subdistrict_id = $sub_search->row['subdistrict_id'];
                    }
                }
            }
        }

        // If STILL empty, match based on city name directly
        if (empty($destination_subdistrict_id) || !is_numeric($destination_subdistrict_id)) {
            if (isset($address['city']) && !empty($address['city'])) {
                $table_query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'");
                if ($table_query->num_rows) {
                    $city_sub_search = $this->db->query("SELECT s.subdistrict_id FROM " . DB_PREFIX . "komerce_subdistrict s LEFT JOIN " . DB_PREFIX . "komerce_city c ON s.city_id = c.city_id WHERE LOWER(c.city_name) LIKE '%" . $this->db->escape(strtolower($address['city'])) . "%' LIMIT 1");
                    if ($city_sub_search->num_rows) {
                        $destination_subdistrict_id = $city_sub_search->row['subdistrict_id'];
                    }
                }
            }
        }

        // If still empty or not numeric, return empty array gracefully instead of throwing 400 or 500 errors
        if (empty($destination_subdistrict_id) || !is_numeric($destination_subdistrict_id)) {
            return array();
        }

        // --- DATABASE SYNCHRONIZATION & CACHING ENGINE ---
        $origin_id = $this->config->get('komerce_shipping_subdistrict_id');
        if (empty($origin_id)) {
            $origin_id = '40101'; // Default fallback to Danurejan, Yogyakarta to prevent API failure
        }
        $weight_g = (int)round($weight);

        $rates_list = null;
        $is_from_cache = false;
        $table_exists = false;

        // Check table existence first to avoid database error if tables aren't initialized yet
        $table_query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_shipping_cache'");
        if ($table_query->num_rows) {
            $table_exists = true;
            
            // Try retrieving valid, synchronized shipping rates from database cache first (expiry: 24 Hours / 1 Day)
            $cache_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_shipping_cache 
                WHERE origin = '" . $this->db->escape($origin_id) . "' 
                  AND destination = '" . $this->db->escape($destination_subdistrict_id) . "' 
                  AND weight = '" . (int)$weight_g . "' 
                  AND date_added > DATE_SUB(NOW(), INTERVAL 1 DAY) 
                LIMIT 1");

            if ($cache_query->num_rows) {
                $rates_list = json_decode($cache_query->row['rates_json'], true);
                $is_from_cache = true;
            }
        }

        // If no cached/synced rates found, query the live Komerce Shipping API
        if (!$rates_list) {
            // Setup Credentials
            $apikey = $this->config->get('komerce_shipping_apikey');
            if (empty($apikey)) {
                $apikey = $this->config->get('komerce_apikey');
            }
            if (empty($apikey)) {
                $apikey = 'sNMFcxAQcd0036ae75d0c302FdO0zoLX';
            }
            $client_id = $this->config->get('komerce_client_id');
            if (empty($client_id)) {
                $client_id = 'CLIENT-ZORAYA-098';
            }
            $environment = $this->config->get('komerce_environment');
            if (empty($environment)) {
                $environment = 'production';
            }
            
            $api_url = ($environment == 'sandbox') 
                ? "https://sandbox-api.komerce.id/v2/shipping/rates" 
                : "https://api.komerce.id/v2/shipping/rates";

            $payload = array(
                'origin'      => $origin_id,
                'destination' => $destination_subdistrict_id,
                'weight'      => $weight_g,
                'couriers'    => 'jne,sicepat,jnt,pos,tiki'
            );

            // Call Komerce Shipping API
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'X-Komerce-Client-Id: ' . $client_id,
                'Authorization: Bearer ' . $apikey
            ));

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($http_code === 200 && isset($result['data'])) {
                $rates_list = $result['data'];
                
                // Synchronize/Save newly fetched rates into database cache
                if ($table_exists) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_shipping_cache 
                        SET origin = '" . $this->db->escape($origin_id) . "', 
                            destination = '" . $this->db->escape($destination_subdistrict_id) . "', 
                            weight = '" . (int)$weight_g . "', 
                            rates_json = '" . $this->db->escape(json_encode($rates_list)) . "', 
                            date_added = NOW()");
                }
            }
        }

        // Parse rates list and build the OpenCart Quote array
        if (!empty($rates_list)) {
            $store_currency = $this->config->get('config_currency');
            $session_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $store_currency;

            foreach ($rates_list as $rate) {
                $carrier_code = isset($rate['courier_code']) ? strtolower($rate['courier_code']) : (isset($rate['courier']) ? strtolower($rate['courier']) : 'courier');
                $service_code = isset($rate['service']) ? strtolower($rate['service']) : 'reg';
                $service_name = isset($rate['service_name']) ? $rate['service_name'] : (isset($rate['service']) ? $rate['service'] : 'REG');
                $etd = isset($rate['etd']) ? $rate['etd'] : '2-3';
                $cost = isset($rate['cost']) ? (float)$rate['cost'] : 0.0;
                
                $title = strtoupper($carrier_code) . ' (' . $service_name . ') - ' . $etd . ' Hari';
                if ($is_from_cache) {
                    $title .= ' [Database Synced]';
                }
                
                // Safe currency conversion to prevent Division by Zero or missing currency errors
                if ($store_currency != 'IDR') {
                    if (method_exists($this->currency, 'convert')) {
                        // Check if IDR exists in currency table to prevent math errors
                        $curr_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE code = 'IDR' AND status = '1'");
                        if ($curr_query->num_rows) {
                            $cost = $this->currency->convert($cost, 'IDR', $store_currency);
                        }
                    }
                }

                $quote_data[$carrier_code . '_' . $service_code] = array(
                    'code'         => 'komerce_shipping.' . $carrier_code . '_' . $service_code,
                    'title'        => $title,
                    'cost'         => $cost,
                    'tax_class_id' => 0,
                    'text'         => $this->currency->format($cost, $session_currency)
                );
            }
        }

        if (empty($quote_data)) {
            return array();
        }

        $method_data = array(
            'code'       => 'komerce_shipping',
            'title'      => 'Komerce Integrasi RajaOngkir' . ($is_from_cache ? ' (Cached)' : ''),
            'quote'      => $quote_data,
            'sort_order' => $this->config->get('komerce_shipping_sort_order'),
            'error'      => false
        );

        return $method_data;
    }
}