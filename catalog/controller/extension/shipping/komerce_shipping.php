<?php
class ControllerExtensionShippingKomerceShipping extends Controller {
    // 1. Get provinces dynamic list (used by checkout dropdowns)
    public function get_provinces_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        
        $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_province'");
        if (!$table_check->num_rows) {
            $this->response->setOutput(json_encode(array()));
            return;
        }
        
        $prov_query = $this->db->query("SELECT province_id, province_name FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
        
        // If empty, sync first from API (JIT sync)
        if (!$prov_query->num_rows) {
            $this->sync_regional_tables_jit('provinces');
            $prov_query = $this->db->query("SELECT province_id, province_name FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
        }
        
        $this->response->setOutput(json_encode($prov_query->rows));
    }

    // 2. Get cities dynamic list by Province ID
    public function get_cities_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        $province_id = isset($this->request->get['province_id']) ? (int)$this->request->get['province_id'] : 0;
        
        $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_city'");
        if (!$table_check->num_rows) {
            $this->response->setOutput(json_encode(array()));
            return;
        }
        
        // JIT check
        $city_query = $this->db->query("SELECT city_id, city_name FROM " . DB_PREFIX . "komerce_city WHERE province_id = '" . (int)$province_id . "' ORDER BY city_name ASC");
        if (!$city_query->num_rows && $province_id) {
            $this->sync_regional_tables_jit('cities');
            $city_query = $this->db->query("SELECT city_id, city_name FROM " . DB_PREFIX . "komerce_city WHERE province_id = '" . (int)$province_id . "' ORDER BY city_name ASC");
        }
        
        $this->response->setOutput(json_encode($city_query->rows));
    }

    // 3. Search Province endpoint (aligns with RajaOngkir search_province)
    public function search_province() {
        $this->response->addHeader('Content-Type: application/json');
        $search = isset($this->request->get['search']) ? trim($this->request->get['search']) : (isset($this->request->get['q']) ? trim($this->request->get['q']) : '');
        
        $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_province'");
        if (!$table_check->num_rows) {
            $this->response->setOutput(json_encode(array('results' => array())));
            return;
        }
        
        $sql = "SELECT province_id as id, province_name as text FROM " . DB_PREFIX . "komerce_province";
        if (!empty($search)) {
            $sql .= " WHERE LOWER(province_name) LIKE '%" . $this->db->escape(strtolower($search)) . "%'";
        }
        $sql .= " ORDER BY province_name ASC LIMIT 30";
        
        $query = $this->db->query($sql);
        
        // JIT Sync if table is entirely empty
        if (!$query->num_rows && empty($search)) {
            $this->sync_regional_tables_jit('provinces');
            $query = $this->db->query($sql);
        }
        
        $this->response->setOutput(json_encode(array('results' => $query->rows)));
    }

    // 4. Search City endpoint (aligns with RajaOngkir search_city)
    public function search_city() {
        $this->response->addHeader('Content-Type: application/json');
        $search = isset($this->request->get['search']) ? trim($this->request->get['search']) : (isset($this->request->get['q']) ? trim($this->request->get['q']) : '');
        $province_id = isset($this->request->get['province_id']) ? (int)$this->request->get['province_id'] : 0;
        
        $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_city'");
        if (!$table_check->num_rows) {
            $this->response->setOutput(json_encode(array('results' => array())));
            return;
        }
        
        $sql = "SELECT city_id as id, city_name as text FROM " . DB_PREFIX . "komerce_city WHERE 1=1";
        if ($province_id) {
            $sql .= " AND province_id = '" . (int)$province_id . "'";
        }
        if (!empty($search)) {
            $sql .= " AND LOWER(city_name) LIKE '%" . $this->db->escape(strtolower($search)) . "%'";
        }
        $sql .= " ORDER BY city_name ASC LIMIT 30";
        
        $query = $this->db->query($sql);
        
        // JIT Sync if table is entirely empty
        if (!$query->num_rows && empty($search)) {
            $this->sync_regional_tables_jit('cities');
            $query = $this->db->query($sql);
        }
        
        $this->response->setOutput(json_encode(array('results' => $query->rows)));
    }

    // 5. Search Subdistrict endpoint (aligns with RajaOngkir search_subdistrict)
    public function search_subdistrict() {
        $this->response->addHeader('Content-Type: application/json');
        $search = isset($this->request->get['search']) ? trim($this->request->get['search']) : (isset($this->request->get['q']) ? trim($this->request->get['q']) : '');
        $city_id = isset($this->request->get['city_id']) ? (int)$this->request->get['city_id'] : 0;
        
        if (!$city_id && !empty($search)) {
            // Find city ID first if name is provided instead of ID
            $city_search = $this->db->query("SELECT city_id FROM " . DB_PREFIX . "komerce_city WHERE LOWER(city_name) LIKE '%" . $this->db->escape(strtolower($search)) . "%' LIMIT 1");
            if ($city_search->num_rows) {
                $city_id = (int)$city_search->row['city_id'];
            }
        }
        
        $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'");
        if (!$table_check->num_rows) {
            $this->response->setOutput(json_encode(array('results' => array())));
            return;
        }
        
        // JIT sync subdistricts for this city if we have city_id and local is empty
        if ($city_id) {
            $check_sub = $this->db->query("SELECT subdistrict_id FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' LIMIT 1");
            if (!$check_sub->num_rows) {
                $this->sync_subdistricts_jit_for_city($city_id);
            }
        }
        
        $sql = "SELECT subdistrict_id as id, subdistrict_name as text FROM " . DB_PREFIX . "komerce_subdistrict WHERE 1=1";
        if ($city_id) {
            $sql .= " AND city_id = '" . (int)$city_id . "'";
        }
        if (!empty($search)) {
            $sql .= " AND LOWER(subdistrict_name) LIKE '%" . $this->db->escape(strtolower($search)) . "%'";
        }
        $sql .= " ORDER BY subdistrict_name ASC LIMIT 30";
        
        $query = $this->db->query($sql);
        $this->response->setOutput(json_encode(array('results' => $query->rows)));
    }

    // 6. Existing method to fetch subdistricts for Journal3 / Checkout AJAX dropdowns
    public function get_subdistricts_ajax() {
        $city_input = isset($this->request->get['city_id']) ? trim($this->request->get['city_id']) : '';
        if (empty($city_input)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array('data' => array())));
            return;
        }
        
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
        
        $city_id = 0;
        if (is_numeric($city_input)) {
            $city_id = (int)$city_input;
        } else {
            // It's a text name city (standard OpenCart/Journal3 behavior). Search locally first
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_city'");
            if ($query->num_rows) {
                $db_city = $this->db->query("SELECT city_id FROM " . DB_PREFIX . "komerce_city WHERE LOWER(city_name) LIKE '%" . $this->db->escape(strtolower($city_input)) . "%' LIMIT 1");
                if ($db_city->num_rows) {
                    $city_id = (int)$db_city->row['city_id'];
                }
            }
            
            // Fallback: search live via Komerce cities list
            if (!$city_id && !empty($apikey) && !empty($client_id)) {
                $cities_url = ($environment == 'sandbox') 
                    ? "https://sandbox-api.komerce.id/v2/regional/cities" 
                    : "https://api.komerce.id/v2/regional/cities";
                
                $ch = curl_init($cities_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'X-Komerce-Client-Id: ' . $client_id,
                    'Authorization: Bearer ' . $apikey
                ));
                $cities_resp = curl_exec($ch);
                curl_close($ch);
                
                if ($cities_resp) {
                    $cities_arr = json_decode($cities_resp, true);
                    if (!empty($cities_arr['data'])) {
                        foreach ($cities_arr['data'] as $c) {
                            if (stripos($c['city_name'], $city_input) !== false || stripos($city_input, $c['city_name']) !== false) {
                                $city_id = (int)$c['city_id'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (!$city_id) {
            $city_id = 401; // Fallback to Yogyakarta
        }

        // Try local database query first
        $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'");
        if ($table_check->num_rows) {
            $sub_query = $this->db->query("SELECT subdistrict_id, subdistrict_name FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' ORDER BY subdistrict_name ASC");
            if ($sub_query->num_rows) {
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode(array('data' => $sub_query->rows)));
                return;
            }
        }

        // Fallback to live Komerce API if local query didn't return rows (JIT caching)
        if (!empty($apikey) && !empty($client_id)) {
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
                    // Populate local database table
                    if ($table_check->num_rows) {
                        foreach ($sub_data['data'] as $sub) {
                            $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_subdistrict SET subdistrict_id = '" . (int)$sub['subdistrict_id'] . "', city_id = '" . (int)$city_id . "', subdistrict_name = '" . $this->db->escape($sub['subdistrict_name']) . "'");
                        }
                    }
                    
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput($response);
                    return;
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(array('data' => array())));
    }

    // Helper: JIT sync of provinces and cities
    private function sync_regional_tables_jit($type) {
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

        if (empty($apikey) || empty($client_id)) return;

        if ($type === 'provinces') {
            $url = ($environment == 'sandbox') 
                ? "https://sandbox-api.komerce.id/v2/regional/provinces" 
                : "https://api.komerce.id/v2/regional/provinces";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Komerce-Client-Id: ' . $client_id,
                'Authorization: Bearer ' . $apikey
            ));
            $resp = curl_exec($ch);
            curl_close($ch);
            if ($resp) {
                $data = json_decode($resp, true);
                if (!empty($data['data'])) {
                    $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_province (province_id INT NOT NULL, province_name VARCHAR(100) NOT NULL, PRIMARY KEY (province_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                    foreach ($data['data'] as $prov) {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_province SET province_id = '" . (int)$prov['province_id'] . "', province_name = '" . $this->db->escape($prov['province_name']) . "'");
                    }
                }
            }
        } elseif ($type === 'cities') {
            $url = ($environment == 'sandbox') 
                ? "https://sandbox-api.komerce.id/v2/regional/cities" 
                : "https://api.komerce.id/v2/regional/cities";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Komerce-Client-Id: ' . $client_id,
                'Authorization: Bearer ' . $apikey
            ));
            $resp = curl_exec($ch);
            curl_close($ch);
            if ($resp) {
                $data = json_decode($resp, true);
                if (!empty($data['data'])) {
                    $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_city (city_id INT NOT NULL, province_id INT NOT NULL, city_name VARCHAR(100) NOT NULL, PRIMARY KEY (city_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                    foreach ($data['data'] as $city) {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_city SET city_id = '" . (int)$city['city_id'] . "', province_id = '" . (int)$city['province_id'] . "', city_name = '" . $this->db->escape($city['city_name']) . "'");
                    }
                }
            }
        }
    }

    // Helper: JIT sync subdistricts for a specific city ID
    private function sync_subdistricts_jit_for_city($city_id) {
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

        if (empty($apikey) || empty($client_id) || !$city_id) return;

        $url = ($environment == 'sandbox') 
            ? "https://sandbox-api.komerce.id/v2/regional/subdistricts?city_id=" . (int)$city_id 
            : "https://api.komerce.id/v2/regional/subdistricts?city_id=" . (int)$city_id;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Komerce-Client-Id: ' . $client_id,
            'Authorization: Bearer ' . $apikey
        ));
        $resp = curl_exec($ch);
        curl_close($ch);

        if ($resp) {
            $data = json_decode($resp, true);
            if (!empty($data['data'])) {
                $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_subdistrict (subdistrict_id INT NOT NULL, city_id INT NOT NULL, subdistrict_name VARCHAR(100) NOT NULL, PRIMARY KEY (subdistrict_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                foreach ($data['data'] as $sub) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_subdistrict SET subdistrict_id = '" . (int)$sub['subdistrict_id'] . "', city_id = '" . (int)$city_id . "', subdistrict_name = '" . $this->db->escape($sub['subdistrict_name']) . "'");
                }
            }
        }
    }
}
