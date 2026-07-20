<?php
/**
 * @package     Komerce Payment Gateway (Powered by RajaOngkir API) for OpenCart 2.3.x
 * @author      Developer Zoraya & Komerce Integration
 * @license     MIT
 */
class ControllerExtensionPaymentKomerce extends Controller {
    private $error = array();

    public function index() {
        // Load bahasa
        $this->load->language('extension/payment/komerce');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        // Simpan konfigurasi jika ada request POST
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('komerce', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        // Mapping variabel bahasa ke View
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        
        // RajaOngkir Account Types
        $data['text_starter'] = 'Starter';
        $data['text_basic'] = 'Basic';
        $data['text_pro'] = 'Pro';

        $data['entry_apikey'] = $this->language->get('entry_apikey'); // Diisi API Key RajaOngkir
        $data['entry_account_type'] = $this->language->get('entry_account_type'); // starter/basic/pro
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        
        // Status Mapping (Sesuai dokumentasi RajaOngkir Payment)
        $data['entry_order_status'] = $this->language->get('entry_order_status'); // Status awal saat checkout
        $data['entry_order_success_status'] = $this->language->get('entry_order_success_status'); // Saat 'settlement'
        $data['entry_order_failed_status'] = $this->language->get('entry_order_failed_status'); // Saat 'expire/cancel'

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Alert Error
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

        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/komerce', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/komerce', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

        // Daftar kunci konfigurasi untuk looping
        $config_keys = array(
            'komerce_status',
            'komerce_apikey',
            'komerce_account_type',
            'komerce_order_status_id',
            'komerce_order_success_status_id',
            'komerce_order_failed_status_id',
            'komerce_sort_order'
        );

        foreach ($config_keys as $key) {
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }

        // URL Webhook untuk didaftarkan di Dashboard RajaOngkir
        $data['webhook_url'] = HTTP_CATALOG . 'index.php?route=extension/payment/komerce/callback';

        // Load semua status pesanan untuk dropdown
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Template components
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/komerce', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/komerce')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        // API Key RajaOngkir wajib diisi
        if (!$this->request->post['komerce_apikey']) {
            $this->error['apikey'] = $this->language->get('error_apikey');
        }

        return !$this->error;
    }
}
