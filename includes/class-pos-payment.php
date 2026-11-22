<?php

if (!defined('ABSPATH')) return;

if (!class_exists('TTBP_POS_Payment')) {
    class TTBP_POS_Payment
    {
        const OPTION_KEY = 'ttbp_clv_cloud_options';
        private $public_key;
        private $private_key;
        private $sandbox_url = 'https://scl-sandbox.dev.clover.com/v1/orders';
        private $production_url = 'https://scl.clover.com/v1/orders';
        private $env;
        private $device_id;
        private $pos_id;

        public function __construct()
        {
            $this->public_key = get_option('ttbp_clover_public_key');
            $this->private_key = get_option('ttbp_clover_private_key');
            $this->env = get_option('ttbp_pos_mode', 'sandbox');
            $this->device_id = get_option('ttbp_device_id', 'sandbox');
            $this->pos_id = get_option('ttbp_pos_id', 'sandbox');
        }

        /**
         * Create a payment request via Clover Cloud Pay Display API.
         *
         * @param array $args Optional overrides for capture/final.
         * @return array Response data or error.
         */
        public function cloud_device_payment($args = [])
        {
            $public_key  = $this->public_key;
            $private_key = $this->private_key;
            $environment = $this->env;

            if (empty($public_key) || empty($private_key)) {
                return [
                    'success' => false,
                    'error'   => 'Clover API credentials are missing.',
                ];
            }

            $base_url = ($environment === 'production')
                ? 'https://api.clover.com'
                : 'https://apisandbox.dev.clover.com';

            $endpoint = $base_url . '/connect/v1/payments';

            $amount_in_cents = (float) $args['amount'] * 100;
            $tax_in_cents = 0;
            if (isset($args['tax_amount'])) {
                $tax_in_cents = (float) $args['tax_amount'] * 100;
                $amount_in_cents = $amount_in_cents + (int) $tax_in_cents;
            }

            $body = [
                'capture'       => isset($args['capture']) ? (bool) $args['capture'] : true,
                'final'         => isset($args['final']) ? (bool) $args['final'] : false,
                'amount'        => (int) $amount_in_cents,
                'deviceOptions' => [
                    'disableCashback' => false,
                    'offlineOptions'  => [
                        'allowOfflinePayment'                => false,
                        'approveOfflinePaymentWithoutPrompt' => false,
                        'forceOfflinePayment'                => false,
                    ],
                    'cardEntryMethods' => [
                        'MAG_STRIPE',
                        'EMV',
                        'NFC',
                        'MANUAL',
                        'DEFAULT',
                    ],
                    'cardNotPresent' => false,
                ],
            ];

            if ($tax_in_cents > 0) {
                $body['taxAmount'] = (int) $tax_in_cents;
            }

            $response = wp_remote_post($endpoint, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'X-Clover-Device-Id' => $this->device_id,
                    'X-POS-Id' => $this->pos_id,
                    'Idempotency-Key' => $this->uuidv4()
                ],
                'body'    => wp_json_encode($body),
                'timeout' => 60,
            ]);

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'error'   => $response->get_error_message(),
                ];
            }

            $code = wp_remote_retrieve_response_code($response);
            $data = json_decode(wp_remote_retrieve_body($response), true);

            if ($code >= 200 && $code < 300) {
                return [
                    'success' => true,
                    'data'    => $data,
                ];
            }

            return [
                'success' => false,
                'error'   => isset($data['message']) ? $data['message'] : 'Unknown Clover API error.',
                'data'    => $data,
            ];
        }

        private function uuidv4()
        {
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }


        public function create_order($request_param)
        {
            $url = get_option('ttbp_pos_mode', 'sandbox') == 'sandbox' ? $this->sandbox_url : $this->production_url;
            $response = wp_remote_post($url, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->private_key, // <-- Add your token here
                ],
                'body'    => json_encode($request_param)
            ]);

            if (is_wp_error($response)) {
                $body = $response->get_error_message();
                $data = json_decode($body);
                return $data;
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
                return $data;
            }
        }
    }
}
