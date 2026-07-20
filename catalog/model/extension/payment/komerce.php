<?php
class ModelExtensionPaymentKomerce extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/komerce');

        $status = true;
        
        // Ensure module is enabled
        if (!$this->config->get('komerce_status')) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'komerce',
                'title'      => 'Komerce Pembayaran Instan (QRIS, VA Bank)',
                'terms'      => '',
                'sort_order' => $this->config->get('komerce_sort_order')
            );
        }

        return $method_data;
    }
}