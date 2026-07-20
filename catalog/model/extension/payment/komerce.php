<?php
/**
 * @package     Komerce Payment Gateway (Powered by RajaOngkir API) for OpenCart 2.3.x
 * @author      Developer Zoraya & Komerce Integration
 * @license     MIT
 */
class ModelExtensionPaymentKomerce extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/komerce');

        // Cek apakah modul diaktifkan di admin
        if ($this->config->get('komerce_status')) {
            $status = true;
        } else {
            $status = false;
        }

        // Opsional: Cek Geo Zone jika Anda ingin membatasi pembayaran hanya di wilayah tertentu
        // (Jika fitur Geo Zone ditambahkan di controller admin nantinya)
        /*
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('komerce_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
        if ($this->config->get('komerce_geo_zone_id') && !$query->num_rows) {
            $status = false;
        }
        */

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'komerce',
                'title'      => $this->language->get('text_title'), // Misal: "RajaOngkir Payment (QRIS, VA, Credit Card)"
                'terms'      => '',
                'sort_order' => $this->config->get('komerce_sort_order')
            );
        }

        return $method_data;
    }
}
