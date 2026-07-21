<?php
class ControllerExtensionShippingRajaOngkir extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/shipping/rajaongkir');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('rajaongkir', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_none'] = $this->language->get('text_none');

        $data['entry_apikey'] = $this->language->get('entry_apikey');
        $data['entry_api_type'] = $this->language->get('entry_api_type');
        $data['entry_origin'] = $this->language->get('entry_origin');
        $data['entry_courier'] = $this->language->get('entry_courier');
        $data['entry_weight_class'] = $this->language->get('entry_weight_class');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['apikey'])) {
            $data['error_apikey'] = $this->error['apikey'];
        } else {
            $data['error_apikey'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->heading_title,
            'href' => $this->url->link('extension/shipping/rajaongkir', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/shipping/rajaongkir', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true);
        $data['token'] = $this->session->data['token'];

        // API Key
        if (isset($this->request->post['rajaongkir_apikey'])) {
            $data['rajaongkir_apikey'] = $this->request->post['rajaongkir_apikey'];
        } else {
            $data['rajaongkir_apikey'] = $this->config->get('rajaongkir_apikey') ? $this->config->get('rajaongkir_apikey') : '9a2e8f7c1d0b3c4e5f6g7h8i9j0k1l2m';
        }

        // API Type (Starter / Basic / Pro)
        if (isset($this->request->post['rajaongkir_api_type'])) {
            $data['rajaongkir_api_type'] = $this->request->post['rajaongkir_api_type'];
        } else {
            $data['rajaongkir_api_type'] = $this->config->get('rajaongkir_api_type') ? $this->config->get('rajaongkir_api_type') : 'pro';
        }

        // Origin details (Subdistrict/City/Province)
        if (isset($this->request->post['rajaongkir_origin_province'])) {
            $data['rajaongkir_origin_province'] = $this->request->post['rajaongkir_origin_province'];
        } else {
            $data['rajaongkir_origin_province'] = $this->config->get('rajaongkir_origin_province') ? $this->config->get('rajaongkir_origin_province') : '9';
        }

        if (isset($this->request->post['rajaongkir_origin_city'])) {
            $data['rajaongkir_origin_city'] = $this->request->post['rajaongkir_origin_city'];
        } else {
            $data['rajaongkir_origin_city'] = $this->config->get('rajaongkir_origin_city') ? $this->config->get('rajaongkir_origin_city') : '23';
        }

        if (isset($this->request->post['rajaongkir_origin_subdistrict'])) {
            $data['rajaongkir_origin_subdistrict'] = $this->request->post['rajaongkir_origin_subdistrict'];
        } else {
            $data['rajaongkir_origin_subdistrict'] = $this->config->get('rajaongkir_origin_subdistrict') ? $this->config->get('rajaongkir_origin_subdistrict') : '302';
        }

        // Allowed Couriers
        if (isset($this->request->post['rajaongkir_couriers'])) {
            $data['rajaongkir_couriers'] = $this->request->post['rajaongkir_couriers'];
        } else {
            $data['rajaongkir_couriers'] = $this->config->get('rajaongkir_couriers') ? $this->config->get('rajaongkir_couriers') : array('jne', 'pos', 'tiki');
        }

        // Weight Class (Default KG)
        if (isset($this->request->post['rajaongkir_weight_class_id'])) {
            $data['rajaongkir_weight_class_id'] = $this->request->post['rajaongkir_weight_class_id'];
        } else {
            $data['rajaongkir_weight_class_id'] = $this->config->get('rajaongkir_weight_class_id') ? $this->config->get('rajaongkir_weight_class_id') : '1';
        }

        // Status
        if (isset($this->request->post['rajaongkir_status'])) {
            $data['rajaongkir_status'] = $this->request->post['rajaongkir_status'];
        } else {
            $data['rajaongkir_status'] = $this->config->get('rajaongkir_status');
        }

        // Sort order
        if (isset($this->request->post['rajaongkir_sort_order'])) {
            $data['rajaongkir_sort_order'] = $this->request->post['rajaongkir_sort_order'];
        } else {
            $data['rajaongkir_sort_order'] = $this->config->get('rajaongkir_sort_order') ? $this->config->get('rajaongkir_sort_order') : '1';
        }

        $this->load->model('localisation/weight_class');
        $data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/rajaongkir', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/shipping/rajaongkir')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['rajaongkir_apikey']) {
            $this->error['apikey'] = $this->language->get('error_apikey');
        }

        return !$this->error;
    }

    public function install() {
        // Create Province local table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "komerce_province` (
            `province_id` INT(11) NOT NULL,
            `province_name` VARCHAR(150) NOT NULL,
            PRIMARY KEY (`province_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        // Create City local table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "komerce_city` (
            `city_id` INT(11) NOT NULL,
            `province_id` INT(11) NOT NULL,
            `city_name` VARCHAR(150) NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `postal_code` VARCHAR(10) NOT NULL,
            PRIMARY KEY (`city_id`),
            KEY `province_id` (`province_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        // Create Subdistrict local table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "komerce_subdistrict` (
            `subdistrict_id` INT(11) NOT NULL,
            `city_id` INT(11) NOT NULL,
            `subdistrict_name` VARCHAR(150) NOT NULL,
            PRIMARY KEY (`subdistrict_id`),
            KEY `city_id` (`city_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        // Create Shipping Cache local table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "komerce_shipping_cache` (
            `cache_id` INT(11) NOT NULL AUTO_INCREMENT,
            `origin` VARCHAR(50) NOT NULL,
            `origin_type` VARCHAR(50) NOT NULL,
            `destination` VARCHAR(50) NOT NULL,
            `destination_type` VARCHAR(50) NOT NULL,
            `weight` INT(11) NOT NULL,
            `courier` VARCHAR(50) NOT NULL,
            `response` TEXT NOT NULL,
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`cache_id`),
            KEY `lookup` (`origin`, `origin_type`, `destination`, `destination_type`, `weight`, `courier`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    public function uninstall() {
        // Optional: drop tables when module is uninstalled
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "komerce_province`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "komerce_city`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "komerce_subdistrict`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "komerce_shipping_cache`");
    }

    public function sync() {
        $json = array();
        $this->load->language('extension/shipping/rajaongkir');
        
        $action = isset($this->request->get['action']) ? $this->request->get['action'] : '';
        $apiKey = $this->config->get('rajaongkir_apikey') ? $this->config->get('rajaongkir_apikey') : '9a2e8f7c1d0b3c4e5f6g7h8i9j0k1l2m';
        $api_type = $this->config->get('rajaongkir_api_type') ? $this->config->get('rajaongkir_api_type') : 'starter';
        
        if (!$apiKey) {
            $json['error'] = 'Gagal: API Key RajaOngkir belum dikonfigurasi di General Settings!';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if ($action == 'province') {
            $url = ($api_type == 'pro') ? 'https://pro.rajaongkir.com/api/province' : (($api_type == 'basic') ? 'https://api.rajaongkir.com/basic/province' : 'https://api.rajaongkir.com/starter/province');
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array("key: " . $apiKey),
                CURLOPT_SSL_VERIFYPEER => false
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                $json['error'] = 'CURL Error: ' . $err;
            } else {
                $res = json_decode($response, true);
                if (isset($res['rajaongkir']['status']['code']) && $res['rajaongkir']['status']['code'] == 200) {
                    $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "komerce_province`");
                    foreach ($res['rajaongkir']['results'] as $prov) {
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "komerce_province` SET province_id = '" . (int)$prov['province_id'] . "', province_name = '" . $this->db->escape($prov['province']) . "'");
                    }
                    $json['success'] = 'Berhasil mensinkronkan ' . count($res['rajaongkir']['results']) . ' Provinsi ke database komerce_province.';
                } else {
                    $json['error'] = isset($res['rajaongkir']['status']['description']) ? $res['rajaongkir']['status']['description'] : 'Gagal mengambil data provinsi.';
                }
            }
        } elseif ($action == 'city') {
            $url = ($api_type == 'pro') ? 'https://pro.rajaongkir.com/api/city' : (($api_type == 'basic') ? 'https://api.rajaongkir.com/basic/city' : 'https://api.rajaongkir.com/starter/city');
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array("key: " . $apiKey),
                CURLOPT_SSL_VERIFYPEER => false
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                $json['error'] = 'CURL Error: ' . $err;
            } else {
                $res = json_decode($response, true);
                if (isset($res['rajaongkir']['status']['code']) && $res['rajaongkir']['status']['code'] == 200) {
                    $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "komerce_city`");
                    foreach ($res['rajaongkir']['results'] as $city) {
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "komerce_city` SET city_id = '" . (int)$city['city_id'] . "', province_id = '" . (int)$city['province_id'] . "', city_name = '" . $this->db->escape($city['city_name']) . "', type = '" . $this->db->escape($city['type']) . "', postal_code = '" . $this->db->escape($city['postal_code']) . "'");
                    }
                    $json['success'] = 'Berhasil mensinkronkan ' . count($res['rajaongkir']['results']) . ' Kota/Kabupaten ke database komerce_city.';
                } else {
                    $json['error'] = isset($res['rajaongkir']['status']['description']) ? $res['rajaongkir']['status']['description'] : 'Gagal mengambil data kota.';
                }
            }
        } elseif ($action == 'subdistrict') {
            if ($api_type != 'pro') {
                $json['error'] = 'Sinkronisasi Kecamatan hanya didukung untuk tipe akun RajaOngkir Pro!';
            } else {
                $city_id = isset($this->request->get['city_id']) ? (int)$this->request->get['city_id'] : 0;
                
                if ($city_id) {
                    $url = 'https://pro.rajaongkir.com/api/subdistrict?city=' . $city_id;
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => array("key: " . $apiKey),
                        CURLOPT_SSL_VERIFYPEER => false
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    
                    if ($err) {
                        $json['error'] = 'CURL Error: ' . $err;
                    } else {
                        $res = json_decode($response, true);
                        if (isset($res['rajaongkir']['status']['code']) && $res['rajaongkir']['status']['code'] == 200) {
                            $this->db->query("DELETE FROM `" . DB_PREFIX . "komerce_subdistrict` WHERE city_id = '" . $city_id . "'");
                            foreach ($res['rajaongkir']['results'] as $sub) {
                                $this->db->query("INSERT INTO `" . DB_PREFIX . "komerce_subdistrict` SET subdistrict_id = '" . (int)$sub['subdistrict_id'] . "', city_id = '" . (int)$sub['city_id'] . "', subdistrict_name = '" . $this->db->escape($sub['subdistrict_name']) . "'");
                            }
                            $json['success'] = 'Kecamatan berhasil disimpan.';
                        } else {
                            $json['error'] = isset($res['rajaongkir']['status']['description']) ? $res['rajaongkir']['status']['description'] : 'Gagal.';
                        }
                    }
                } else {
                    $cities_query = $this->db->query("SELECT city_id, city_name FROM `" . DB_PREFIX . "komerce_city` ORDER BY city_id ASC");
                    $json['cities'] = $cities_query->rows;
                    $json['success'] = 'Memuat list kota untuk incremental syncing.';
                }
            }
        } elseif ($action == 'stats') {
            // Check if tables exist first
            $prov_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_province'")->num_rows;
            $city_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_city'")->num_rows;
            $sub_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_subdistrict'")->num_rows;
            $cache_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "komerce_shipping_cache'")->num_rows;
            
            // Re-create tables if missing
            if (!$prov_check || !$city_check || !$sub_check || !$cache_check) {
                $this->install();
            }

            $prov_count = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "komerce_province`")->row['total'];
            $city_count = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "komerce_city`")->row['total'];
            $sub_count = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "komerce_subdistrict`")->row['total'];
            $cache_count = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "komerce_shipping_cache`")->row['total'];
            
            $json['stats'] = array(
                'province' => (int)$prov_count,
                'city' => (int)$city_count,
                'subdistrict' => (int)$sub_count,
                'cache' => (int)$cache_count
            );
            $json['success'] = true;
        } elseif ($action == 'clear_cache') {
            $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "komerce_shipping_cache`");
            $json['success'] = 'Berhasil membersihkan cache pengiriman (komerce_shipping_cache).';
        } else {
            $json['error'] = 'Invalid Action';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
?>