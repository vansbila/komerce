<?php
class ControllerExtensionShippingKomerceShipping extends Controller {
    private $error = array();

    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_province (
            province_id INT NOT NULL,
            province_name VARCHAR(100) NOT NULL,
            PRIMARY KEY (province_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_city (
            city_id INT NOT NULL,
            province_id INT NOT NULL,
            city_name VARCHAR(100) NOT NULL,
            type VARCHAR(50) NOT NULL,
            postal_code VARCHAR(10) NOT NULL,
            PRIMARY KEY (city_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "komerce_subdistrict (
            subdistrict_id INT NOT NULL,
            city_id INT NOT NULL,
            subdistrict_name VARCHAR(100) NOT NULL,
            PRIMARY KEY (subdistrict_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    public function index() {
        $this->load->language('extension/shipping/komerce_shipping');
        $this->document->setTitle("Komerce RajaOngkir Setup");
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('komerce_shipping', $this->request->post);
            $this->session->data['success'] = "Sukses: Konfigurasi telah disimpan!";
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true));
        }

        $data['heading_title'] = "Komerce RajaOngkir (OC 2.3)";
        $data['token'] = $this->session->data['token'];

        $config_keys = array(
            'komerce_shipping_status',
            'komerce_shipping_apikey',
            'komerce_shipping_api_tier',
            'komerce_shipping_province_id',
            'komerce_shipping_city_id',
            'komerce_shipping_subdistrict_id',
            'komerce_shipping_sort_order'
        );

        foreach ($config_keys as $key) {
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }

        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array('text' => 'Home', 'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true));
        $data['breadcrumbs'][] = array('text' => 'Shipping', 'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true));
        $data['breadcrumbs'][] = array('text' => $data['heading_title'], 'href' => $this->url->link('extension/shipping/komerce_shipping', 'token=' . $this->session->data['token'], true));

        $data['action'] = $this->url->link('extension/shipping/komerce_shipping', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/komerce_shipping', $data));
    }

    private function call_api($endpoint, $params = array()) {
        $api_key = $this->config->get('komerce_shipping_apikey');
        $url = "https://rajaongkir.komerce.id/api/v1/" . $endpoint;
        if (!empty($params)) $url .= "?" . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("accept: application/json", "key: " . $api_key));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function sync_db() {
        $this->response->addHeader('Content-Type: application/json');
        if (!$this->user->hasPermission('modify', 'extension/shipping/komerce_shipping')) {
            $this->response->setOutput(json_encode(array('success' => false, 'message' => 'No permission')));
            return;
        }

        $this->install();
        $prov_res = $this->call_api('destination/province');

        if (isset($prov_res['rajaongkir']['results'])) {
            foreach ($prov_res['rajaongkir']['results'] as $prov) {
                $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_province SET province_id = '" . (int)$prov['province_id'] . "', province_name = '" . $this->db->escape($prov['province']) . "'");
            }

            $city_res = $this->call_api('destination/city');
            if (isset($city_res['rajaongkir']['results'])) {
                foreach ($city_res['rajaongkir']['results'] as $city) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_city SET city_id = '" . (int)$city['city_id'] . "', province_id = '" . (int)$city['province_id'] . "', city_name = '" . $this->db->escape($city['city_name']) . "', type = '" . $this->db->escape($city['type']) . "', postal_code = '" . $this->db->escape($city['postal_code']) . "'");
                }
            }
            $this->response->setOutput(json_encode(array('success' => true, 'message' => 'Sync Sukses! Data Provinsi & Kota berhasil diperbarui.')));
        } else {
            $this->response->setOutput(json_encode(array('success' => false, 'message' => 'API Error: Periksa API Key Anda.')));
        }
    }

    public function get_provinces_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_province ORDER BY province_name ASC");
        $this->response->setOutput(json_encode($query->rows));
    }

    public function get_cities_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        $province_id = isset($this->request->get['province_id']) ? (int)$this->request->get['province_id'] : 0;
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_city WHERE province_id = '" . (int)$province_id . "' ORDER BY city_name ASC");
        $this->response->setOutput(json_encode($query->rows));
    }

    public function get_subdistricts_ajax() {
        $this->response->addHeader('Content-Type: application/json');
        $city_id = isset($this->request->get['city_id']) ? (int)$this->request->get['city_id'] : 0;
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' ORDER BY subdistrict_name ASC");
        
        if ($query->num_rows) {
            $this->response->setOutput(json_encode($query->rows));
        } else {
            $result = $this->call_api('destination/subdistrict', array('city' => $city_id));
            if (!empty($result['rajaongkir']['results'])) {
                foreach ($result['rajaongkir']['results'] as $sub) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "komerce_subdistrict SET subdistrict_id = '" . (int)$sub['subdistrict_id'] . "', city_id = '" . (int)$city_id . "', subdistrict_name = '" . $this->db->escape($sub['subdistrict_name']) . "'");
                }
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "komerce_subdistrict WHERE city_id = '" . (int)$city_id . "' ORDER BY subdistrict_name ASC");
                $this->response->setOutput(json_encode($query->rows));
            } else {
                $this->response->setOutput(json_encode(array()));
            }
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/shipping/komerce_shipping')) {
            $this->error['warning'] = "Permission Denied!";
        }
        return !$this->error;
    }
}
