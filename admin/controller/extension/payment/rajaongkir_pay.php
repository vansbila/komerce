<?php
class ControllerExtensionPaymentRajaOngkirPay extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/rajaongkir_pay');
        $this->document->setTitle('RajaOngkir Transfer Bank');
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('rajaongkir_pay', $this->request->post);
            $this->session->data['success'] = 'Success: You have modified RajaOngkir Bank Transfer!';
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['heading_title'] = 'RajaOngkir Bank Transfer';
        $data['text_edit'] = 'Edit RajaOngkir Bank Transfer';
        $data['text_enabled'] = 'Enabled';
        $data['text_disabled'] = 'Disabled';

        $data['entry_bank'] = 'Bank Transfer Instructions';
        $data['entry_status'] = 'Status';
        $data['entry_sort_order'] = 'Sort Order';

        $data['button_save'] = 'Save';
        $data['button_cancel'] = 'Cancel';

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Home',
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => 'Extensions',
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => 'RajaOngkir Bank Transfer',
            'href' => $this->url->link('extension/payment/rajaongkir_pay', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/rajaongkir_pay', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

        // Bank instructions input
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $data['languages'] = $languages;

        foreach ($languages as $language) {
            if (isset($this->request->post['rajaongkir_pay_bank_' . $language['language_id']])) {
                $data['rajaongkir_pay_bank_' . $language['language_id']] = $this->request->post['rajaongkir_pay_bank_' . $language['language_id']];
            } else {
                $data['rajaongkir_pay_bank_' . $language['language_id']] = $this->config->get('rajaongkir_pay_bank_' . $language['language_id']) ? $this->config->get('rajaongkir_pay_bank_' . $language['language_id']) : "Bank BCA\nNo. Rekening: 1234567890\nA.N. PT Toko Online Mandiri";
            }
        }

        // Status
        if (isset($this->request->post['rajaongkir_pay_status'])) {
            $data['rajaongkir_pay_status'] = $this->request->post['rajaongkir_pay_status'];
        } else {
            $data['rajaongkir_pay_status'] = $this->config->get('rajaongkir_pay_status') ? $this->config->get('rajaongkir_pay_status') : '1';
        }

        // Sort order
        if (isset($this->request->post['rajaongkir_pay_sort_order'])) {
            $data['rajaongkir_pay_sort_order'] = $this->request->post['rajaongkir_pay_sort_order'];
        } else {
            $data['rajaongkir_pay_sort_order'] = $this->config->get('rajaongkir_pay_sort_order') ? $this->config->get('rajaongkir_pay_sort_order') : '1';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/rajaongkir_pay', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/rajaongkir_pay')) {
            $this->error['warning'] = 'Warning: You do not have permission to modify this payment!';
        }
        return !$this->error;
    }
}
?>