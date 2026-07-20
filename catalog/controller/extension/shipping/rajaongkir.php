<?php
/**
 * @package     Komerce Shipping - RajaOngkir Compatibility Router
 * @author      Developer Zoraya & Komerce Integration
 * @license     MIT
 */
class ControllerExtensionShippingRajaongkir extends Controller {
    public function index() {
        $this->response->addHeader('Content-Type: application/json');
        
        $type = isset($this->request->get['type']) ? strtolower($this->request->get['type']) : '';
        
        if ($type == 'province' || $type == 'provinces') {
            $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_province'");
            if ($table_check->num_rows) {
                $prov_query = $this->db->query("SELECT province_id, province_name FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
                
                // JIT cache sync if entirely empty
                if (!$prov_query->num_rows) {
                    $this->sync_regional_tables_jit('provinces');
                    $prov_query = $this->db->query("SELECT province_id, province_name FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
                }
                
                $results = array();
                foreach ($prov_query->rows as $row) {
                    $results[] = array(
                        'province_id'   => $row['province_id'],
                        'province_name' => $row['province_name'],
                        'province'      => $row['province_name'],
                        'id'            => $row['province_id'],
                        'text'          => $row['province_name'],
                        'name'          => $row['province_name']
                    );
                }
                
                // Also provide standard RajaOngkir structure wrapper in case some scripts expect it
                $output = $results;
                if (isset($this->request->get['wrapper']) || strpos($_SERVER['REQUEST_URI'], 'wrapper') !== false) {
                    $output = array(
                        'rajaongkir' => array(
                            'status'  => array('code' => 200, 'description' => 'OK'),
                            'results' => $results
                        )
                    );
                }
                $this->response->setOutput(json_encode($output));
            } else {
                $this->response->setOutput(json_encode(array()));
            }
        } elseif ($type == 'city' || $type == 'cities') {
            $province_id = 0;
            if (isset($this->request->get['province_id'])) {
                $province_id = (int)$this->request->get['province_id'];
            } elseif (isset($this->request->get['provinceId'])) {
                $province_id = (int)$this->request->get['provinceId'];
            } elseif (isset($this->request->get['province'])) {
                $province_id = (int)$this->request->get['province'];
            }
            
            $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_city'");
            if ($table_check->num_rows) {
                $sql = "SELECT city_id, city_name, type FROM " . DB_PREFIX . "komerce_city WHERE 1=1";
                if ($province_id) {
                    $sql .= " AND province_id = '" . (int)$province_id . "'";
                }
                $sql .= " ORDER BY city_name ASC";
                $city_query = $this->db->query($sql);
                
                // JIT Sync if empty
                if (!$city_query->num_rows && $province_id) {
                    $this->sync_regional_tables_jit('cities');
                    $city_query = $this->db->query($sql);
                }
                
                $results = array();
                foreach ($city_query->rows as $row) {
                    $prefix = !empty($row['type']) ? $row['type'] . ' ' : '';
                    $name = $prefix . $row['city_name'];
                    $results[] = array(
                        'city_id'     => $row['city_id'],
                        'city_name'   => $row['city_name'],
                        'city'        => $row['city_name'],
                        'province_id' => $province_id,
                        'type'        => !empty($row['type']) ? $row['type'] : '',
                        'name'        => $name,
                        'id'          => $row['city_id'],
                        'text'        => $name
                    );
                }
                
                $output = $results;
                if (isset($this->request->get['wrapper']) || strpos($_SERVER['REQUEST_URI'], 'wrapper') !== false) {
                    $output = array(
                        'rajaongkir' => array(
                            'status'  => array('code' => 200, 'description' => 'OK'),
                            'results' => $results
                        )
                    );
                }
                $this->response->setOutput(json_encode($output));
            } else {
                $this->response->setOutput(json_encode(array()));
            }
        } elseif ($type == 'subdistrict' || $type == 'subdistricts') {
            $city_id = 0;
            if (isset($this->request->get['city_id'])) {
                $city_id = (int)$this->request->get['city_id'];
            } elseif (isset($this->request->get['cityId'])) {
                $city_id = (int)$this->request->get['cityId'];
            } elseif (isset($this->request->get['city'])) {
                $city_id = (int)$this->request->get['city'];
            }
            
            // JIT check for subdistricts
            if ($city_id) {
                $sub_table = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'");
                if ($sub_table->num_rows) {
                    $check_sub = $this->db->query("SELECT subdistrict_id FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' LIMIT 1");
                    if (!$check_sub->num_rows) {
                        $this->sync_subdistricts_for_city($city_id);
                    }
                }
            }
            
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'");
            if ($query->num_rows && $city_id) {
                $sub_query = $this->db->query("SELECT subdistrict_id, subdistrict_name FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' ORDER BY subdistrict_name ASC");
                $results = array();
                foreach ($sub_query->rows as $row) {
                    $results[] = array(
                        'subdistrict_id'   => $row['subdistrict_id'],
                        'subdistrict_name' => $row['subdistrict_name'],
                        'subdistrict'      => $row['subdistrict_name'],
                        'id'               => $row['subdistrict_id'],
                        'text'             => $row['subdistrict_name'],
                        'name'             => $row['subdistrict_name']
                    );
                }
                
                $output = $results;
                if (isset($this->request->get['wrapper']) || strpos($_SERVER['REQUEST_URI'], 'wrapper') !== false) {
                    $output = array(
                        'rajaongkir' => array(
                            'status'  => array('code' => 200, 'description' => 'OK'),
                            'results' => $results
                        )
                    );
                }
                $this->response->setOutput(json_encode($output));
            } else {
                $this->response->setOutput(json_encode(array()));
            }
        } else {
            $this->response->setOutput(json_encode(array('error' => 'Unknown type parameter: ' . $type)));
        }
    }

    private function sync_regional_tables_jit($type) {
        $apikey = $this->config->get('komerce_shipping_apikey');
        if (empty($apikey)) $apikey = $this->config->get('komerce_apikey');
        if (empty($apikey)) $apikey = 'sNMFcxAQcd0036ae75d0c302FdO0zoLX';
        
        $client_id = $this->config->get('komerce_client_id');
        if (empty($client_id)) $client_id = 'CLIENT-ZORAYA-098';
        
        $environment = $this->config->get('komerce_environment');
        if (empty($environment)) $environment = 'production';

        $api_url = ($environment == 'sandbox') 
            ? "https://sandbox-api.komerce.id/v2/regional/" . $type
            : "https://api.komerce.id/v2/regional/" . $type;

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Komerce-Client-Id: ' . $client_id,
            'Authorization: Bearer ' . $apikey
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $decoded = json_decode($response, true);
            if (!empty($decoded['data'])) {
                if ($type == 'provinces') {
                    foreach ($decoded['data'] as $prov) {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_province SET province_id = '" . (int)$prov['province_id'] . "', province_name = '" . $this->db->escape($prov['province_name']) . "'");
                    }
                } elseif ($type == 'cities') {
                    foreach ($decoded['data'] as $city) {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_city SET city_id = '" . (int)$city['city_id'] . "', province_id = '" . (int)$city['province_id'] . "', city_name = '" . $this->db->escape($city['city_name']) . "', postal_code = '" . $this->db->escape($city['postal_code']) . "', type = '" . $this->db->escape($city['type']) . "'");
                    }
                }
            }
        }
    }

    private function sync_subdistricts_for_city($city_id) {
        $apikey = $this->config->get('komerce_shipping_apikey');
        if (empty($apikey)) $apikey = $this->config->get('komerce_apikey');
        if (empty($apikey)) $apikey = 'sNMFcxAQcd0036ae75d0c302FdO0zoLX';
        
        $client_id = $this->config->get('komerce_client_id');
        if (empty($client_id)) $client_id = 'CLIENT-ZORAYA-098';
        
        $environment = $this->config->get('komerce_environment');
        if (empty($environment)) $environment = 'production';
        
        $api_url = ($environment == 'sandbox') 
            ? "https://sandbox-api.komerce.id/v2/regional/subdistricts?city_id=" . $city_id 
            : "https://api.komerce.id/v2/regional/subdistricts?city_id=" . $city_id;

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Komerce-Client-Id: ' . $client_id,
            'Authorization: Bearer ' . $apikey
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $sub_data = json_decode($response, true);
            if (!empty($sub_data['data'])) {
                foreach ($sub_data['data'] as $sub) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_subdistrict SET subdistrict_id = '" . (int)$sub['subdistrict_id'] . "', city_id = '" . (int)$city_id . "', subdistrict_name = '" . $this->db->escape($sub['subdistrict_name']) . "'");
                }
            }
        }
    }
}
