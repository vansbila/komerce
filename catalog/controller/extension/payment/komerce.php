<?php
/**
 * @package     Komerce Payment Gateway (Powered by RajaOngkir API) for OpenCart 2.3.x
 * @author      Developer Zoraya & Komerce Integration
 * @license     MIT
 */
class ControllerExtensionPaymentKomerce extends Controller {
    
    // Menampilkan tombol konfirmasi di halaman checkout
    public function index() {
        $this->load->language('extension/payment/komerce');
        
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        
        // Endpoint internal untuk memicu proses ke RajaOngkir
        $data['action_checkout'] = $this->url->link('extension/payment/komerce/checkout', '', true);

        // Dukungan untuk Journal3 atau Tema Custom
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/komerce.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/komerce.tpl', $data);
        } else {
            return $this->load->view('extension/payment/komerce', $data);
        }
    }

    // Fungsi utama: Kirim data ke RajaOngkir dan dapatkan Redirect URL
    public function checkout() {
        $this->load->model('checkout/order');
        $this->load->language('extension/payment/komerce');

        if (!isset($this->session->data['order_id'])) {
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        $order_id = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        // Ambil konfigurasi Admin
        $api_key = $this->config->get('komerce_apikey');
        $account_type = $this->config->get('komerce_account_type'); // starter, basic, atau pro

        // Persiapan Payload sesuai Dokumentasi RajaOngkir Payment API
        // Ref: https://rajaongkir.com/docs/payment-api/getting-started/request-payment
        $payload = array(
            'transaction_details' => array(
                'order_id'     => (string)$order_id,
                'gross_amount' => (int)round($order_info['total'])
            ),
            'customer_details' => array(
                'first_name' => $order_info['firstname'],
                'last_name'  => $order_info['lastname'],
                'email'      => $order_info['email'],
                'phone'      => $order_info['telephone']
            ),
            // RajaOngkir secara otomatis menangani pemilihan bank di landing page mereka
        );

        // Eksekusi CURL ke Endpoint RajaOngkir
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/" . $account_type . "/payment",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "key: " . $api_key
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $this->log->write('RajaOngkir Payment Error: ' . $err);
            echo "Gagal menghubungkan ke server pembayaran. Silakan coba lagi.";
        } else {
            $result = json_decode($response, true);
            
            if (isset($result['rajaongkir']['result']['checkout_url'])) {
                // Update status awal ke "Pending" (atau sesuai pengaturan admin)
                $this->model_checkout_order->addOrderHistory(
                    $order_id, 
                    $this->config->get('komerce_order_status_id'), 
                    'Menunggu pembayaran via RajaOngkir.', 
                    true
                );

                // Redirect user ke halaman pembayaran RajaOngkir
                $this->response->redirect($result['rajaongkir']['result']['checkout_url']);
            } else {
                $error_msg = isset($result['rajaongkir']['status']['description']) ? $result['rajaongkir']['status']['description'] : 'Unknown Error';
                $this->log->write('RajaOngkir API Error: ' . $error_msg);
                echo "Error dari RajaOngkir: " . $error_msg;
            }
        }
    }

    /**
     * Webhook/Callback: RajaOngkir akan mengirimkan POST ke URL ini 
     * saat status pembayaran berubah (Success/Expired).
     */
    public function callback() {
        // RajaOngkir mengirim data dalam format JSON POST
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            return;
        }

        // Ambil data penting dari payload RajaOngkir
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        $status = isset($data['status']) ? strtolower($data['status']) : '';

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            switch ($status) {
                case 'settlement':
                case 'capture':
                    // Pembayaran Berhasil
                    $this->model_checkout_order->addOrderHistory(
                        $order_id, 
                        $this->config->get('komerce_order_success_status_id'), 
                        'Pembayaran diterima via RajaOngkir. Transaction ID: ' . $data['transaction_id'], 
                        true
                    );
                    break;

                case 'pending':
                    // User sudah buka invoice tapi belum bayar
                    $this->model_checkout_order->addOrderHistory(
                        $order_id, 
                        $this->config->get('komerce_order_status_id'), 
                        'Pelanggan sedang melakukan pembayaran.', 
                        false
                    );
                    break;

                case 'deny':
                case 'expire':
                case 'cancel':
                    // Pembayaran Gagal/Kadaluarsa
                    $this->model_checkout_order->addOrderHistory(
                        $order_id, 
                        $this->config->get('komerce_order_failed_status_id'), 
                        'Pembayaran gagal atau kadaluarsa. Status: ' . $status, 
                        true
                    );
                    break;
            }
            
            // Memberikan respon 200 OK ke RajaOngkir
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array('status' => 'ok')));
        }
    }
}
