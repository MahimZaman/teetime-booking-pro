<?php

if (!defined('ABSPATH')) return;

if (!class_exists('TTBP_Analytics')) {
    class TTBP_Analytics
    {
        private $analytics = [];
        private $postmeta_table;
        private $post_table;

        public function __construct()
        {
            global $wpdb;
            $this->post_table = $wpdb->prefix . 'posts';
            $this->postmeta_table = $wpdb->prefix . 'postmeta';
        }

        private function set_analytics($date)
        {
            global $wpdb;
            $posts     = $this->post_table;
            $postmeta  = $this->postmeta_table;

            // Sanitize date input
            $date = sanitize_text_field($date);

            // 1. Get all post IDs for bookings on the given date
            $sql = "
                SELECT p.ID
                FROM {$posts} AS p
                INNER JOIN {$postmeta} AS pm_date ON p.ID = pm_date.post_id
                WHERE p.post_type = 'ttbp_booking'
                AND p.post_status = 'publish'
                AND pm_date.meta_key = 'ttbp_date'
                AND DATE(pm_date.meta_value) = %s
            ";
            $post_ids = $wpdb->get_col($wpdb->prepare($sql, $date));

            if (empty($post_ids)) {
                $this->analytics = [
                    'bookings'   => 0,
                    'no_shows'   => 0,
                    'checked_in' => 0,
                    'revenue'    => 0,
                    'occupancy'    => 0,
                ];
                return;
            }

            $post_ids_sql = implode(',', array_map('absint', $post_ids)); // secure for SQL IN()

            // 2. Count total bookings
            $total_bookings_sql = "
                SELECT SUM(CAST(pm_players.meta_value AS UNSIGNED)) AS total_players
                FROM {$posts} AS p
                INNER JOIN {$postmeta} AS pm_date 
                    ON p.ID = pm_date.post_id AND pm_date.meta_key = 'ttbp_date'
                LEFT JOIN {$postmeta} AS pm_players 
                    ON p.ID = pm_players.post_id AND pm_players.meta_key = 'ttbp_players'
                WHERE p.post_type = 'ttbp_booking'
                AND p.post_status = 'publish'
                AND DATE(pm_date.meta_value) = %s
            ";

            $total_bookings = $wpdb->get_var($wpdb->prepare($total_bookings_sql, $date));

            // 3. Count no-shows via ttbp_delete_reason
            $no_show_sql = "
                SELECT COUNT(*) FROM {$postmeta}
                WHERE meta_key = 'ttbp_delete_reason'
                AND meta_value = 'no-show'
                AND post_id IN ({$post_ids_sql})
            ";
            $no_shows = (int) $wpdb->get_var($no_show_sql);

            // 4. Count checked-in via ttbp_teed_off
            $checked_in_sql = "
                SELECT 
                    SUM(
                        (meta_key = 'ttbp_payment_status_1' AND meta_value = 'paid') +
                        (meta_key = 'ttbp_payment_status_2' AND meta_value = 'paid') +
                        (meta_key = 'ttbp_payment_status_3' AND meta_value = 'paid') +
                        (meta_key = 'ttbp_payment_status_4' AND meta_value = 'paid')
                    ) AS total_paid
                FROM {$postmeta}
                WHERE post_id IN ({$post_ids_sql})
            ";

            $checked_in = (int) $wpdb->get_var($checked_in_sql);

            // 5. Sum total revenue via ttbp_total_amount
            $revenue = 0;

            foreach ($post_ids as $post_id) {
                $total_amount = (float) get_post_meta($post_id, 'ttbp_total_amount', true);
                $players      = (int) get_post_meta($post_id, 'ttbp_players', true);

                if ($players <= 0 || $total_amount <= 0) {
                    continue;
                }

                $per_player_cost = $total_amount / $players;

                for ($i = 1; $i <= $players; $i++) {
                    $status   = get_post_meta($post_id, "ttbp_payment_status_{$i}", true);
                    if (strtolower($status) !== 'paid') {
                        continue; // skip unpaid players
                    }

                    $discount = (float) get_post_meta($post_id, "ttbp_discount_player_{$i}", true);
                    $final_player_cost = max(0, $per_player_cost - $discount);

                    $revenue += $final_player_cost;
                }
            }

            // 6. Calculate occupancy
            $custom_slots = get_option('ttbp_custom_slot_rules') ? get_option('ttbp_custom_slot_rules') : [];
            $custom_slot = isset($custom_slots[$date]) ? $custom_slots[$date] : [];
            $startTime = isset($custom_slot['start_morning']) ? $custom_slot['start_morning'] : get_option('ttbp_default_start_morning');
            $endTime = isset($custom_slot['end']) ? $custom_slot['end'] : get_option('ttbp_default_end');
            $duration = isset($custom_slot['duration']) ? $custom_slot['duration'] : get_option('ttbp_default_duration');
            $possible_booking = render_slots($startTime, $endTime, $duration);
            $possible_booking = is_array($possible_booking) ? count($possible_booking) * 4 : 0;

            // Max capacity: 4 players per booking
            $max_slots = $total_bookings;

            // Avoid divide by zero
            $occupancy = $max_slots > 0 ? round(($max_slots / $possible_booking) * 100, 2) : 0;

            // 7. Store results
            $this->analytics = [
                'bookings'   => $total_bookings,
                'no_shows'   => $no_shows,
                'checked_in' => $checked_in,
                'revenue'    => $revenue,
                'occupancy'    => $occupancy,
            ];
        }


        public function get_analytics($date)
        {
            $this->set_analytics($date);
            return $this->analytics;
        }
    }
}
