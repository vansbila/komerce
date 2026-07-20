<?php
/**
 * @package     Komerce Payment & Webhook Controller (V2 OpenAPI)
 * @author      Zoraya Developer Team
 */
class ControllerExtensionPaymentKomerce extends Controller {
    
    // Renders checkout payment button / container in checkout page (including Journal3 integration)
    public function index() {
        $this->load->language('extension/payment/komerce');
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['button_confirm'] = 'Bayar Sekarang';
        $data['order_id'] = $order_info['order_id'];
        $data['total'] = $order_info['total'];

        // Pass payment options configuration to checkout view
        $data['qris_enabled'] = $this->config->get('komerce_qris_status');
        $data['bca_va_enabled'] = $this->config->get('komerce_bca_va_status');
        $data['mandiri_va_enabled'] = $this->config->get('komerce_mandiri_va_status');
        $data['bni_va_enabled'] = $this->config->get('komerce_bni_va_status');
        $data['bri_va_enabled'] = $this->config->get('komerce_bri_va_status');

        $data['action_checkout'] = $this->url->link('extension/payment/komerce/create_payment', '', true);

        // Render template with fallback for custom themes like Journal3
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/komerce.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/komerce.tpl', $data);
        } else {
            return $this->load->view('extension/payment/komerce', $data);
        }
    }

    // Connects to Komerce OpenAPI V2 to generate the QRIS / Virtual Account invoice
    public function create_payment() {
        $this->load->model('checkout/order');
        $this->load->language('extension/payment/komerce');

        $order_id = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!$order_info) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_decode(array('error' => 'Data pesanan tidak ditemukan')));
            return;
        }

        $payment_method = isset($this->request->post['payment_method']) ? $this->request->post['payment_method'] : 'qris';
        
        // Setup API Headers
        $apikey = $this->config->get('komerce_apikey');
        $client_id = $this->config->get('komerce_client_id');
        $environment = $this->config->get('komerce_environment');
        
        $api_url = ($environment == 'sandbox') 
            ? "https://sandbox-api.komerce.id/v2/payments/create" 
            : "https://api.komerce.id/v2/payments/create";

        // Build Payload according to Komerce OpenAPI specs
        // Reference: https://komerceapi.readme.io/reference/welcome-to-komerce-openapi
        $payload = array(
            'external_id'    => 'OC-' . $order_id . '-' . time(),
            'amount'         => (int)round($order_info['total']),
            'payment_method' => strtoupper($payment_method),
            'payer_email'    => $order_info['email'],
            'payer_name'     => $order_info['firstname'] . ' ' . $order_info['lastname'],
            'payer_phone'    => $order_info['telephone'],
            'description'    => 'Pembayaran Pesanan #' . $order_id . ' di ' . $this->config->get('config_name'),
            'callback_url'   => $this->url->link('extension/payment/komerce/webhook', '', true),
            'success_redirect_url' => $this->url->link('checkout/success', '', true),
            'failure_redirect_url' => $this->url->link('checkout/failure', '', true),
        );

        // Execute API Post Curl
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Komerce-Client-Id: ' . $client_id,
            'Authorization: Bearer ' . $apikey
        ));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($http_code === 200 || $http_code === 201) {
            // Save initial payment logs or set order status as Pending
            $this->model_checkout_order->addOrderHistory(
                $order_id, 
                $this->config->get('komerce_status_pending_id'), 
                'Invoice Komerce berhasil dibuat. Metode: ' . strtoupper($payment_method), 
                true
            );

            // Return dynamic checkout details
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array(
                'success' => true,
                'redirect' => isset($result['data']['payment_url']) ? $result['data']['payment_url'] : '',
                'payment_data' => $result['data'] ?? array()
            )));
        } else {
            // Log Komerce errors inside standard OpenCart error logs
            $this->log->write('Komerce Payment Generation Failed. Status Code: ' . $http_code . ' | Payload: ' . $response);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array(
                'success' => false,
                'message' => 'Gagal memproses pembayaran Komerce API. Silakan pilih metode lain atau hubungi admin.'
            )));
        }
    }

    // Real-time automatic order status synchronization via secure Webhook callback
    public function webhook() {
        $this->load->model('checkout/order');

        // Extract and validate incoming Headers
        $headers = getallheaders();
        $signature_received = isset($headers['X-Komerce-Signature']) ? $headers['X-Komerce-Signature'] : '';
        $configured_token = $this->config->get('komerce_webhook_token');

        // Capture raw input body
        $raw_body = file_get_contents('php://input');
        $payload = json_decode($raw_body, true);

        // Security Validation (ensure hook authenticity)
        if (!empty($configured_token) && $signature_received !== $configured_token) {
            $this->response->addHeader('HTTP/1.1 401 Unauthorized');
            $this->response->setOutput('Akses ditolak: Token Webhook tidak cocok');
            $this->log->write('Komerce Webhook Unauthorized Access: signature mismatch.');
            return;
        }

        if (!$payload || !isset($payload['external_id']) || !isset($payload['status'])) {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            $this->response->setOutput('Payload tidak valid');
            return;
        }

        // Parse Order ID from External ID (e.g., "OC-154-16293910")
        $external_parts = explode('-', $payload['external_id']);
        if (count($external_parts) < 2) {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            $this->response->setOutput('External ID tidak valid');
            return;
        }
        
        $order_id = (int)$external_parts[1];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!$order_info) {
            $this->response->addHeader('HTTP/1.1 404 Not Found');
            $this->response->setOutput('Pesanan tidak ditemukan');
            return;
        }

        $komerce_status = strtolower($payload['status']);
        $new_status_id = null;
        $comment = 'Sinkronisasi Otomatis Webhook Komerce V2. Status: ' . strtoupper($komerce_status);

        // Dynamic State mapping
        // Mapping Komerce statuses into OpenCart configured equivalents
        switch ($komerce_status) {
            case 'paid':
            case 'success':
                $new_status_id = $this->config->get('komerce_status_paid_id');
                $comment .= '. ID Pembayaran: ' . ($payload['payment_id'] ?? 'N/A');
                break;
            case 'shipped':
            case 'delivered':
                $new_status_id = $this->config->get('komerce_status_shipped_id');
                if (isset($payload['tracking_number'])) {
                    $comment .= '. Nomor Resi Pengiriman: ' . $payload['tracking_number'];
                    // Automatically record tracking number to order table
                    $this->db->query("UPDATE `" . DB_PREFIX . "order` SET tracking = '" . $this->db->escape($payload['tracking_number']) . "' WHERE order_id = '" . (int)$order_id . "'");
                }
                break;
            case 'cancelled':
            case 'expired':
                $new_status_id = $this->config->get('komerce_status_cancelled_id');
                $comment .= '. Alasan: ' . ($payload['cancel_reason'] ?? 'Kadaluarsa / Pembatalan');
                break;
        }

        if ($new_status_id !== null) {
            // Update order status and notify customer in real-time
            $this->model_checkout_order->addOrderHistory($order_id, $new_status_id, $comment, true);
            
            $this->response->addHeader('HTTP/1.1 200 OK');
            $this->response->setOutput(json_encode(array('success' => true, 'message' => 'Status pesanan berhasil disinkronisasi')));
        } else {
            $this->response->addHeader('HTTP/1.1 200 OK');
            $this->response->setOutput(json_encode(array('success' => true, 'message' => 'Webhook diterima, tidak ada perubahan status')));
        }
    }
}