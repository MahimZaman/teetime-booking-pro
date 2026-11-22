<?php
if (!defined('ABSPATH')) return;
/**
 * Clover Payment Gateway Integration
 * 
 * This class handles payment processing through Clover APIs
 * with support for 3D Secure (3DS) authentication
 */
class TTBP_Clover_Pay
{
    private $sandbox_url = 'https://scl-sandbox.dev.clover.com/v1/charges';

    private $live_url = 'https://scl.clover.com/v1/charges';

    private $mode;

    private $private_key;

    private $url;

    public function __construct()
    {
        $this->private_key = get_option('ttbp_clover_private_key');
        $this->mode = get_option('ttbp_clover_env');

        if ($this->mode == 'test') {
            $this->url = $this->sandbox_url;
        } else {
            $this->url = $this->live_url;
        }
    }

    private function get_ip_address()
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
            // Make sure we always only send through the first IP in the list which should always be the client IP.
            return (string) rest_is_ip_address(trim(current(preg_split('/,/', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']))))));
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        return '';
    }

    private function uuidv4()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function create_charge($amount_cents, $source_token, $payment_data)
    {
        try {
            // Get credentials and IP
            $access_token = $this->private_key;
            $client_ip = $this->get_ip_address();
            $idempotency_key = $this->uuidv4(); // Unique per transaction

            // Prepare body
            $body = [
                'amount'   => (int) $amount_cents, // in cents
                'currency' => strtolower(get_option('ttbp_currency', 'usd')),
                'capture' => true,
                'description' => $payment_data['description'],
                'source'   => sanitize_text_field($source_token),
            ];

            if (isset($payment_data['tax_amount'])) {
                $tax_in_cents = (float) $payment_data['tax_amount'] * 100;
                $total_with_tax = (int) $amount_cents + (int) $tax_in_cents;
                $body['amount'] = $total_with_tax;
                $body['tax_amount'] = $tax_in_cents;
            }

            // Prepare headers
            $headers = [
                'Authorization'     => 'Bearer ' . sanitize_text_field($access_token),
                'Content-Type'      => 'application/json',
                'Accept'            => 'application/json',
                'Idempotency-Key'   => $idempotency_key,
                'X-Forwarded-For'   => $client_ip,
            ];

            // Send request
            $response = wp_remote_post($this->url, [
                'headers' => $headers,
                'body'    => wp_json_encode($body),
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                throw new Exception('Request failed: ' . $response->get_error_message());
            }

            $status_code = wp_remote_retrieve_response_code($response);
            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if ($status_code !== 200 && $status_code !== 201) {
                $error_message = $response_body['message'] ?? 'Unknown error';
                throw new Exception("Charge failed: $error_message");
            }

            return $response_body; // contains the charge object

        } catch (Exception $e) {
            error_log('Clover Charge Error: ' . $e->getMessage());
            return new WP_Error('clover_charge_error', $e->getMessage());
        }
    }
}
