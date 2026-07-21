<?php
class ControllerExtensionPaymentRajaOngkirPay extends Controller {
    public function index() {
        $this->load->language('extension/payment/rajaongkir_pay');

        $data['text_instruction'] = $this->language->get('text_instruction');
        $data['text_payable'] = $this->language->get('text_payable');
        $data['text_address'] = $this->language->get('text_address');
        $data['text_payment'] = $this->language->get('text_payment');
        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $bank_details = $this->config->get('rajaongkir_pay_bank_' . $this->config->get('config_language_id'));
        if (!$bank_details) {
            $bank_details = "Bank BCA\nNo. Rekening: 1234567890\nA.N. PT Toko Online Mandiri";
        }
        $data['bank_details'] = nl2br($bank_details);
        $data['continue'] = $this->url->link('checkout/success');

        return $this->load->view('extension/payment/rajaongkir_pay', $data);
    }

    public function confirm() {
        if ($this->session->data['payment_method']['code'] == 'rajaongkir_pay') {
            $this->load->language('extension/payment/rajaongkir_pay');

            $this->load->model('checkout/order');
            
            $bank_details = $this->config->get('rajaongkir_pay_bank_' . $this->config->get('config_language_id'));
            if (!$bank_details) {
                $bank_details = "Bank BCA\nNo. Rekening: 1234567890\nA.N. PT Toko Online Mandiri";
            }
            $comment  = $this->language->get('text_instruction') . "\n\n";
            $comment .= $bank_details . "\n\n";
            $comment .= $this->language->get('text_payment');

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('rajaongkir_pay_order_status_id'), $comment, true);
        }
    }
}
?>