<?php
class ModelExtensionPaymentRajaOngkirQris extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/rajaongkir_qris');

        $status = true;

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'rajaongkir_qris',
                'title'      => 'RajaOngkir QRIS (QR Code Indonesian Standard)',
                'terms'      => '',
                'sort_order' => $this->config->get('rajaongkir_qris_sort_order')
            );
        }

        return $method_data;
    }
}
?>