<?php
class ControllerExtensionShippingKomerceShipping extends Controller {
    private $error = array();

    public function install() {
        $this->createTables();
    }

    private function createTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_province (province_id INT PRIMARY KEY, province_name VARCHAR(100)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_city (city_id INT PRIMARY KEY, province_id INT, city_name VARCHAR(100)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_subdistrict (subdistrict_id INT PRIMARY KEY, city_id INT, subdistrict_name VARCHAR(100)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_shipping_cache (origin VARCHAR(50), destination VARCHAR(50), weight INT, rates_json LONGTEXT, date_added DATETIME, PRIMARY KEY (origin, destination, weight)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    public function index() {
        $this->load->language('extension/shipping/komerce_shipping');
        $this->document->setTitle("RajaOngkir Shipping Setup");
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('komerce_shipping', $this->request->post);
            $this->session->data['success'] = "Sukses update pengaturan RajaOngkir!";
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true));
        }

        $data['heading_title'] = "RajaOngkir Shipping (Delivery Order)";
        $data['entry_apikey'] = 'RajaOngkir API Key';
        $data['entry_api_tier'] = 'Akun Tier (starter/basic/pro)';
        $data['entry_origin_province'] = 'Provinsi Asal';
        $data['entry_origin_city'] = 'Kota Asal';
        $data['entry_origin_subdistrict'] = 'Kecamatan Asal (Hanya Akun Pro)';
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array('text' => 'Home', 'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true));
        $data['breadcrumbs'][] = array('text' => 'Shipping', 'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true));
        $data['breadcrumbs'][] = array('text' => 'RajaOngkir', 'href' => $this->url->link('extension/shipping/komerce_shipping', 'token=' . $this->session->data['token'], true));

        $data['action'] = $this->url->link('extension/shipping/komerce_shipping', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true);

        $config_keys = array(
            'komerce_shipping_status', 'komerce_shipping_apikey', 'komerce_shipping_api_tier',
            'komerce_shipping_province_id', 'komerce_shipping_province_name', 'komerce_shipping_city_id',
            'komerce_shipping_city_name', 'komerce_shipping_subdistrict_id', 'komerce_shipping_subdistrict_name',
            'komerce_shipping_default_weight', 'komerce_shipping_sort_order'
        );

        foreach ($config_keys as $key) {
            $data[$key] = isset($this->request->post[$key]) ? $this->request->post[$key] : $this->config->get($key);
        }

        // Default tier RajaOngkir
        if (empty($data['komerce_shipping_api_tier'])) $data['komerce_shipping_api_tier'] = 'starter';

        $data['token'] = $this->session->data['token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/komerce_shipping', $data));
    }

    public function sync_db() {
        $this->response->addHeader('Content-Type: application/json');
        set_time_limit(0); 

        if (!$this->user->hasPermission('modify', 'extension/shipping/komerce_shipping')) {
            $this->response->setOutput(json_encode(array('success' => false, 'message' => 'Izin ditolak.')));
            return;
        }

        $this->createTables();

        $apikey = $this->config->get('komerce_shipping_apikey');
        $tier = strtolower($this->config->get('komerce_shipping_api_tier')); // starter, basic, pro

        if (empty($apikey)) {
            $this->response->setOutput(json_encode(array('success' => false, 'message' => 'API Key RajaOngkir belum diisi dan disimpan!')));
            return;
        }

        // Base URL RajaOngkir berdasarkan Tier
        $base_url = "https://api.rajaongkir.com/starter";
        if ($tier == 'basic') $base_url = "https://api.rajaongkir.com/basic";
        if ($tier == 'pro') $base_url = "https://pro.rajaongkir.com/api";

        $headers = array("key: " . $apikey);

        // 1. SYNC PROVINSI
        $prov_resp = $this->callRajaOngkir($base_url . "/province", $headers);
        if (isset($prov_resp['rajaongkir']['results'])) {
            foreach ($prov_resp['rajaongkir']['results'] as $prov) {
                $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_province SET province_id = '" . (int)$prov['province_id'] . "', province_name = '" . $this->db->escape($prov['province']) . "'");
            }
        } else {
            $this->response->setOutput(json_encode(array('success' => false, 'message' => 'Gagal terhubung ke RajaOngkir. Cek API Key dan Tier.')));
            return;
        }

        // 2. SYNC KOTA
        $city_resp = $this->callRajaOngkir($base_url . "/city", $headers);
        $city_count = 0;
        if (isset($city_resp['rajaongkir']['results'])) {
            foreach ($city_resp['rajaongkir']['results'] as $city) {
                $name = $city['type'] . ' ' . $city['city_name'];
                $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_city SET city_id = '" . (int)$city['city_id'] . "', province_id = '" . (int)$city['province_id'] . "', city_name = '" . $this->db->escape($name) . "'");
                $city_count++;
            }
        }

        // 3. SYNC KECAMATAN (Hanya Tier PRO)
        $sub_count = 0;
        if ($tier == 'pro') {
            $query_cities = $this->db->query("SELECT city_id FROM " . DB_PREFIX . "komerce_city");
            foreach ($query_cities->rows as $city) {
                $sub_resp = $this->callRajaOngkir($base_url . "/subdistrict?city=" . $city['city_id'], $headers);
                if (isset($sub_resp['rajaongkir']['results'])) {
                    foreach ($sub_resp['rajaongkir']['results'] as $sub) {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_subdistrict SET subdistrict_id = '" . (int)$sub['subdistrict_id'] . "', city_id = '" . (int)$city['city_id'] . "', subdistrict_name = '" . $this->db->escape($sub['subdistrict_name']) . "'");
                        $sub_count++;
                    }
                }
                usleep(5000); 
            }
        }

        $this->response->setOutput(json_encode(array(
            'success' => true,
            'message' => "Sync RajaOngkir Berhasil! Tersimpan $city_count Kota " . ($tier == 'pro' ? "dan $sub_count Kecamatan." : "(Tier $tier tidak mendukung sinkronisasi kecamatan secara massal).")
        )));
    }

    private function callRajaOngkir($url, $headers) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    // Dropdown Handlers
    public function get_provinces_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
        $this->response->setOutput(json_encode($query->rows));
    }

    public function get_cities_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        $province_id = (int)$this->request->get['province_id'];
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_city WHERE province_id = '$province_id' ORDER BY city_name ASC");
        $this->response->setOutput(json_encode($query->rows));
    }

    public function get_subdistricts_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        $city_id = (int)$this->request->get['city_id'];
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '$city_id' ORDER BY subdistrict_name ASC");
        $this->response->setOutput(json_encode($query->rows));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/shipping/komerce_shipping')) {
            $this->error['warning'] = 'No Permission';
        }
        return !$this->error;
    }
}
