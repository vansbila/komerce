<?php
/**
 * @package     Komerce Shipping Model - RajaOngkir API v1 Compatibility
 * @author      Integration Team
 */
class ModelExtensionShippingKomerceShipping extends Model {
    public function getQuote($address) {
        $this->load->language('extension/shipping/komerce_shipping');

        $quote_data = array();

        // 1. Cek Status Modul
        if (!$this->config->get('komerce_shipping_status')) {
            return array();
        }

        // 2. Hitung Berat (Gram)
        $weight = $this->cart->getWeight();
        if ($weight <= 0) {
            $weight = (float)$this->config->get('komerce_shipping_default_weight');
        }
        $weight_g = (int)round($weight);
        if ($weight_g <= 0) $weight_g = 1000;

        // 3. Tentukan Destinasi Berdasarkan Tier (Starter/Basic = city, Pro = subdistrict)
        $tier = $this->config->get('komerce_shipping_api_tier');
        if (!$tier) $tier = 'starter';

        $destination_id = 0;
        $destination_type = ($tier == 'pro') ? 'subdistrict' : 'city';

        if ($tier == 'pro') {
            // Cek Subdistrict ID dari Custom Fields atau Address matching
            if (isset($address['subdistrict_id'])) {
                $destination_id = $address['subdistrict_id'];
            }
        } else {
            // Cek City ID
            if (isset($address['city_id'])) {
                $destination_id = $address['city_id'];
            }
        }

        // Fallback: Jika ID tidak ditemukan, coba cari di DB Lokal berdasarkan nama
        if (!$destination_id) {
            $destination_id = $this->findLocationByName($address, $destination_type);
        }

        if (!$destination_id) {
            return array();
        }

        // 4. Setup Origin
        $origin_id = ($tier == 'pro') ? $this->config->get('komerce_shipping_subdistrict_id') : $this->config->get('komerce_shipping_city_id');
        $origin_type = ($tier == 'pro') ? 'subdistrict' : 'city';

        if (!$origin_id) $origin_id = '401'; // Default Yogyakarta City

        // --- CACHING ENGINE ---
        $rates_list = null;
        $is_from_cache = false;
        
        $cache_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_shipping_cache 
            WHERE origin = '" . $this->db->escape($origin_id) . "' 
              AND destination = '" . $this->db->escape($destination_id) . "' 
              AND weight = '" . (int)$weight_g . "' 
              AND date_added > DATE_SUB(NOW(), INTERVAL 1 DAY) 
            LIMIT 1");

        if ($cache_query->num_rows) {
            $rates_list = json_decode($cache_query->row['rates_json'], true);
            $is_from_cache = true;
        }

        // --- API CALL (Jika Cache Kosong) ---
        if (!$rates_list) {
            $apikey = $this->config->get('komerce_shipping_apikey');
            $api_url = "https://rajaongkir.komerce.id/api/v1/cost";

            // Daftar kurir yang didukung
            $couriers = ($tier == 'starter') ? array('jne', 'pos', 'tiki') : array('jne', 'pos', 'tiki', 'jnt', 'sicepat', 'wahana', 'lion');
            
            $all_results = array();

            foreach ($couriers as $courier) {
                $post_data = array(
                    'origin'          => $origin_id,
                    'originType'      => $origin_type,
                    'destination'     => $destination_id,
                    'destinationType' => $destination_type,
                    'weight'          => $weight_g,
                    'courier'         => $courier
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "key: " . $apikey,
                    "content-type: application/x-www-form-urlencoded"
                ));
                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);

                if (isset($result['rajaongkir']['results'][0]['costs'])) {
                    $courier_name = $result['rajaongkir']['results'][0]['name'];
                    $courier_code = $result['rajaongkir']['results'][0]['code'];

                    foreach ($result['rajaongkir']['results'][0]['costs'] as $cost_detail) {
                        $all_results[] = array(
                            'code'    => strtolower($courier_code),
                            'name'    => $courier_name,
                            'service' => $cost_detail['service'],
                            'cost'    => $cost_detail['cost'][0]['value'],
                            'etd'     => $cost_detail['cost'][0]['etd']
                        );
                    }
                }
            }

            if (!empty($all_results)) {
                $rates_list = $all_results;
                // Simpan ke Cache
                $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_shipping_cache 
                    SET origin = '" . $this->db->escape($origin_id) . "', 
                        destination = '" . $this->db->escape($destination_id) . "', 
                        weight = '" . (int)$weight_g . "', 
                        rates_json = '" . $this->db->escape(json_encode($rates_list)) . "', 
                        date_added = NOW()");
            }
        }

        // --- BUILD QUOTE DATA ---
        if (!empty($rates_list)) {
            foreach ($rates_list as $rate) {
                $carrier_code = $rate['code'];
                $service_code = str_replace(' ', '_', strtolower($rate['service']));
                
                $title = $rate['name'] . ' - ' . $rate['service'];
                if ($rate['etd']) $title .= ' (' . $rate['etd'] . ' Hari)';

                $cost = (float)$rate['cost'];

                // Konversi Mata Uang jika bukan IDR
                if ($this->config->get('config_currency') != 'IDR') {
                    $cost = $this->currency->convert($cost, 'IDR', $this->config->get('config_currency'));
                }

                $quote_data[$carrier_code . '_' . $service_code] = array(
                    'code'         => 'komerce_shipping.' . $carrier_code . '_' . $service_code,
                    'title'        => $title,
                    'cost'         => $this->tax->calculate($cost, 0, $this->config->get('config_tax')),
                    'tax_class_id' => 0,
                    'text'         => $this->currency->format($this->tax->calculate($cost, 0, $this->config->get('config_tax')), $this->session->data['currency'])
                );
            }
        }

        if (!$quote_data) return array();

        return array(
            'code'       => 'komerce_shipping',
            'title'      => 'Komerce Integrasi RajaOngkir',
            'quote'      => $quote_data,
            'sort_order' => $this->config->get('komerce_shipping_sort_order'),
            'error'      => false
        );
    }

    /**
     * Helper: Cari ID Lokasi berdasarkan Nama Kota (Fallback jika ID kosong)
     */
    private function findLocationByName($address, $type) {
        $table = ($type == 'subdistrict') ? DB_PREFIX . 'komerce_subdistrict' : DB_PREFIX . 'komerce_city';
        $col_name = ($type == 'subdistrict') ? 'subdistrict_name' : 'city_name';
        $col_id = ($type == 'subdistrict') ? 'subdistrict_id' : 'city_id';

        $search = isset($address['city']) ? $address['city'] : '';
        if (!$search) return 0;

        $query = $this->db->query("SELECT $col_id FROM $table WHERE LOWER($col_name) LIKE '%" . $this->db->escape(strtolower($search)) . "%' LIMIT 1");
        
        return ($query->num_rows) ? $query->row[$col_id] : 0;
    }
}
