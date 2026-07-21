<?php
class ControllerExtensionPaymentRajaOngkirQris extends Controller {
    public function index() {
        $this->load->language('extension/payment/rajaongkir_qris');

        $data['text_instruction'] = 'Pembayaran Instant QRIS';
        $data['text_scan'] = 'Silakan scan kode QRIS di bawah ini menggunakan aplikasi e-wallet pilihan Anda (GoPay, OVO, Dana, LinkAja, BCA Mobile, ShopeePay, dll).';
        $data['button_confirm'] = 'Konfirmasi Pembayaran';

        $data['merchant_name'] = $this->config->get('rajaongkir_qris_merchant') ? $this->config->get('rajaongkir_qris_merchant') : 'PT Toko Online Mandiri';
        $data['nmid'] = $this->config->get('rajaongkir_qris_nmid') ? $this->config->get('rajaongkir_qris_nmid') : 'ID102030405060';
        
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
        
        $data['continue'] = $this->url->link('checkout/success');

        return $this->load->view('extension/payment/rajaongkir_qris', $data);
    }

    public function confirm() {
        if ($this->session->data['payment_method']['code'] == 'rajaongkir_qris') {
            $this->load->model('checkout/order');
            
            $comment  = 'Pembayaran via RajaOngkir QRIS Instant\n';
            $comment .= 'Merchant: ' . ($this->config->get('rajaongkir_qris_merchant') ? $this->config->get('rajaongkir_qris_merchant') : 'PT Toko Online Mandiri') . '\n';
            $comment .= 'NMID: ' . ($this->config->get('rajaongkir_qris_nmid') ? $this->config->get('rajaongkir_qris_nmid') : 'ID102030405060') . '\n';
            $comment .= 'Status: Terbayar Instan';

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 2, $comment, true); // Status 2 = Processing / paid
        }
    }
}
?>