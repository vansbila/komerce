<?php
class ControllerExtensionPaymentRajaOngkirQris extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/rajaongkir_qris');
        $this->document->setTitle('RajaOngkir QRIS Configuration');
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('rajaongkir_qris', $this->request->post);
            $this->session->data['success'] = 'Success: You have modified RajaOngkir QRIS settings!';
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['heading_title'] = 'RajaOngkir QRIS';
        $data['text_edit'] = 'Edit RajaOngkir QRIS';
        $data['text_enabled'] = 'Enabled';
        $data['text_disabled'] = 'Disabled';

        $data['entry_merchant'] = 'Merchant Name';
        $data['entry_nmid'] = 'National Merchant ID (NMID)';
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
            'text' => 'RajaOngkir QRIS',
            'href' => $this->url->link('extension/payment/rajaongkir_qris', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/rajaongkir_qris', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

        // Merchant Name
        if (isset($this->request->post['rajaongkir_qris_merchant'])) {
            $data['rajaongkir_qris_merchant'] = $this->request->post['rajaongkir_qris_merchant'];
        } else {
            $data['rajaongkir_qris_merchant'] = $this->config->get('rajaongkir_qris_merchant') ? $this->config->get('rajaongkir_qris_merchant') : 'PT Toko Online Mandiri';
        }

        // NMID
        if (isset($this->request->post['rajaongkir_qris_nmid'])) {
            $data['rajaongkir_qris_nmid'] = $this->request->post['rajaongkir_qris_nmid'];
        } else {
            $data['rajaongkir_qris_nmid'] = $this->config->get('rajaongkir_qris_nmid') ? $this->config->get('rajaongkir_qris_nmid') : 'ID102030405060';
        }

        // Status
        if (isset($this->request->post['rajaongkir_qris_status'])) {
            $data['rajaongkir_qris_status'] = $this->request->post['rajaongkir_qris_status'];
        } else {
            $data['rajaongkir_qris_status'] = $this->config->get('rajaongkir_qris_status') ? $this->config->get('rajaongkir_qris_status') : '1';
        }

        // Sort order
        if (isset($this->request->post['rajaongkir_qris_sort_order'])) {
            $data['rajaongkir_qris_sort_order'] = $this->request->post['rajaongkir_qris_sort_order'];
        } else {
            $data['rajaongkir_qris_sort_order'] = $this->config->get('rajaongkir_qris_sort_order') ? $this->config->get('rajaongkir_qris_sort_order') : '2';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/rajaongkir_qris', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/rajaongkir_qris')) {
            $this->error['warning'] = 'Warning: You do not have permission to modify RajaOngkir QRIS payment!';
        }
        return !$this->error;
    }
}
?>