<?php
/**
 * @package     Komerce Shipping - RajaOngkir Compatibility Router (API v1)
 * @author      Integration Team
 * @url         https://rajaongkir.komerce.id/
 */
class ControllerExtensionShippingRajaongkir extends Controller {
    public function index() {
        $this->response->addHeader('Content-Type: application/json');
        
        $type = isset($this->request->get['type']) ? strtolower($this->request->get['type']) : '';
        $apikey = $this->config->get('komerce_shipping_apikey');
        
        if ($type == 'province' || $type == 'provinces') {
            $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_province'");
            if ($table_check->num_rows) {
                $prov_query = $this->db->query("SELECT province_id, province_name FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
                
                // JIT sync jika tabel kosong
                if (!$prov_query->num_rows) {
                    $this->sync_jit('province');
                    $prov_query = $this->db->query("SELECT province_id, province_name FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
                }
                
                $results = array();
                foreach ($prov_query->rows as $row) {
                    $results[] = array(
                        'province_id'   => $row['province_id'],
                        'province'      => $row['province_name'],
                        'id'            => $row['province_id'],
                        'text'          => $row['province_name']
                    );
                }
                
                $this->output_json($results);
            }
        } elseif ($type == 'city' || $type == 'cities') {
            $province_id = isset($this->request->get['province']) ? (int)$this->request->get['province'] : 0;
            
            $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_city'");
            if ($table_check->num_rows) {
                $sql = "SELECT city_id, province_id, city_name, type, postal_code FROM " . DB_PREFIX . "komerce_city WHERE 1=1";
                if ($province_id) $sql .= " AND province_id = '" . (int)$province_id . "'";
                $sql .= " ORDER BY city_name ASC";
                
                $city_query = $this->db->query($sql);
                
                // JIT Sync jika database lokal kosong
                if (!$city_query->num_rows) {
                    $this->sync_jit('city');
                    $city_query = $this->db->query($sql);
                }
                
                $results = array();
                foreach ($city_query->rows as $row) {
                    $full_name = $row['type'] . ' ' . $row['city_name'];
                    $results[] = array(
                        'city_id'     => $row['city_id'],
                        'province_id' => $row['province_id'],
                        'city_name'   => $row['city_name'],
                        'type'        => $row['type'],
                        'postal_code' => $row['postal_code'],
                        'id'          => $row['city_id'],
                        'text'        => $full_name
                    );
                }
                $this->output_json($results);
            }
        } elseif ($type == 'subdistrict' || $type == 'subdistricts') {
            $city_id = isset($this->request->get['city']) ? (int)$this->request->get['city'] : 0;
            
            if ($city_id) {
                $sub_table = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'");
                if ($sub_table->num_rows) {
                    $sub_query = $this->db->query("SELECT subdistrict_id, subdistrict_name FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' ORDER BY subdistrict_name ASC");
                    
                    // JIT Sync per kota jika data kecamatan kota tsb belum ada
                    if (!$sub_query->num_rows) {
                        $this->sync_jit('subdistrict', $city_id);
                        $sub_query = $this->db->query("SELECT subdistrict_id, subdistrict_name FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' ORDER BY subdistrict_name ASC");
                    }
                    
                    $results = array();
                    foreach ($sub_query->rows as $row) {
                        $results[] = array(
                            'subdistrict_id'   => $row['subdistrict_id'],
                            'subdistrict_name' => $row['subdistrict_name'],
                            'id'               => $row['subdistrict_id'],
                            'text'             => $row['subdistrict_name']
                        );
                    }
                    $this->output_json($results);
                }
            }
        }
    }

    /**
     * Helper Sinkronisasi JIT sesuai struktur RajaOngkir & Endpoint Komerce
     */
    private function sync_jit($endpoint, $city_id = 0) {
        $apikey = $this->config->get('komerce_shipping_apikey');
        $url = "https://rajaongkir.komerce.id/api/v1/destination/" . $endpoint;
        
        $params = array();
        if ($endpoint == 'subdistrict' && $city_id) {
            $params['city'] = $city_id;
        }
        if (!empty($params)) $url .= "?" . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "accept: application/json",
            "key: " . $apikey
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['rajaongkir']['results'])) {
                $results = $data['rajaongkir']['results'];
                
                foreach ($results as $item) {
                    if ($endpoint == 'province') {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_province SET province_id = '" . (int)$item['province_id'] . "', province_name = '" . $this->db->escape($item['province']) . "'");
                    } elseif ($endpoint == 'city') {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_city SET city_id = '" . (int)$item['city_id'] . "', province_id = '" . (int)$item['province_id'] . "', city_name = '" . $this->db->escape($item['city_name']) . "', type = '" . $this->db->escape($item['type']) . "', postal_code = '" . $this->db->escape($item['postal_code']) . "'");
                    } elseif ($endpoint == 'subdistrict') {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_subdistrict SET subdistrict_id = '" . (int)$item['subdistrict_id'] . "', city_id = '" . (int)$city_id . "', subdistrict_name = '" . $this->db->escape($item['subdistrict_name']) . "'");
                    }
                }
            }
        }
    }

    /**
     * Helper Output JSON dengan wrapper RajaOngkir
     */
    private function output_json($results) {
        $output = array(
            'rajaongkir' => array(
                'status'  => array('code' => 200, 'description' => 'OK'),
                'results' => $results
            )
        );
        $this->response->setOutput(json_encode($output));
    }
}
