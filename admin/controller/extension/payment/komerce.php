<?php
/**
 * @package     Komerce Payment Gateway for OpenCart 2.3.x
 * @author      Developer Zoraya & Komerce Integration
 * @license     MIT
 */
class ControllerExtensionPaymentKomerce extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/komerce');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('komerce', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        // Language variables mapping
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_sandbox'] = $this->language->get('text_sandbox');
        $data['text_production'] = $this->language->get('text_production');
        
        $data['entry_apikey'] = $this->language->get('entry_apikey');
        $data['entry_client_id'] = $this->language->get('entry_client_id');
        $data['entry_client_secret'] = $this->language->get('entry_client_secret');
        $data['entry_environment'] = $this->language->get('entry_environment');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_webhook_token'] = $this->language->get('entry_webhook_token');
        $data['entry_webhook_url'] = $this->language->get('entry_webhook_url');

        // Status Map
        $data['entry_status_pending'] = $this->language->get('entry_status_pending');
        $data['entry_status_paid'] = $this->language->get('entry_status_paid');
        $data['entry_status_shipped'] = $this->language->get('entry_status_shipped');
        $data['entry_status_cancelled'] = $this->language->get('entry_status_cancelled');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Error alerts
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

        // Populate configuration data
        $config_keys = array(
            'komerce_status',
            'komerce_apikey',
            'komerce_client_id',
            'komerce_client_secret',
            'komerce_environment',
            'komerce_webhook_token',
            'komerce_status_pending_id',
            'komerce_status_paid_id',
            'komerce_status_shipped_id',
            'komerce_status_cancelled_id',
            'komerce_sort_order',
            'komerce_qris_status',
            'komerce_bca_va_status',
            'komerce_mandiri_va_status',
            'komerce_bni_va_status',
            'komerce_bri_va_status'
        );

        foreach ($config_keys as $key) {
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }

        // Inject default webhook token if empty
        if (empty($data['komerce_webhook_token'])) {
            $data['komerce_webhook_token'] = 'KMCSIGN_ZORAYA_7781';
        }

        // Webhook URL endpoint for Komerce
        $data['webhook_url'] = HTTP_CATALOG . 'index.php?route=extension/payment/komerce/webhook';

        // Load all order statuses from database
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Template components loading
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/komerce', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/komerce')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->request->post['komerce_apikey']) {
            $this->error['apikey'] = $this->language->get('error_apikey');
        }
        return !$this->error;
    }
}