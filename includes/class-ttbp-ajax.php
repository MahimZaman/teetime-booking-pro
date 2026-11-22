<?php

if (!defined('ABSPATH')) exit;

class TTBP_Ajax
{

    public function __construct()
    {
        // Register AJAX handlers
        add_action('wp_ajax_ttbp_fetch_slots', [$this, 'fetch_slots']);
        add_action('wp_ajax_nopriv_ttbp_fetch_slots', [$this, 'fetch_slots']);

        add_action('wp_ajax_ttbp_submit_booking', [$this, 'submit_booking']);
        add_action('wp_ajax_nopriv_ttbp_submit_booking', [$this, 'submit_booking']);

        add_action('wp_ajax_render_week_dates', [$this, 'render_week_dates']);
        add_action('wp_ajax_nopriv_render_week_dates', [$this, 'render_week_dates']);

        add_action('wp_ajax_ttbp_render_bookings', [$this, 'ttbp_render_bookings']);
        add_action('wp_ajax_nopriv_ttbp_render_bookings', [$this, 'ttbp_render_bookings']);

        add_action('wp_ajax_ttbp_update_entry', [$this, 'ttbp_update_entry']);
        add_action('wp_ajax_nopriv_ttbp_update_entry', [$this, 'ttbp_update_entry']);

        add_action('wp_ajax_ttbp_delete_booking', [$this, 'ttbp_delete_booking']);
        add_action('wp_ajax_nopriv_ttbp_delete_booking', [$this, 'ttbp_delete_booking']);

        add_action('wp_ajax_ttbp_update_booking', [$this, 'ttbp_update_booking']);
        add_action('wp_ajax_nopriv_ttbp_update_booking', [$this, 'ttbp_update_booking']);

        add_action('wp_ajax_ttbp_checkout_review', [$this, 'ttbp_checkout_review']);
        add_action('wp_ajax_nopriv_ttbp_checkout_review', [$this, 'ttbp_checkout_review']);

        add_action('wp_ajax_ttbp_date_change', [$this, 'ttbp_date_change']);
        add_action('wp_ajax_nopriv_ttbp_date_change', [$this, 'ttbp_date_change']);

        add_action('wp_ajax_ttbp_bulk_booking', [$this, 'ttbp_bulk_booking']);
        add_action('wp_ajax_nopriv_ttbp_bulk_booking', [$this, 'ttbp_bulk_booking']);

        add_action('wp_ajax_ttbp_redeem_coupon', [$this, 'ttbp_redeem_coupon']);
        add_action('wp_ajax_nopriv_ttbp_redeem_coupon', [$this, 'ttbp_redeem_coupon']);

        add_action('wp_ajax_ttbp_update_starter_times', [$this, 'ttbp_update_starter_times']);
        add_action('wp_ajax_nopriv_ttbp_update_starter_times', [$this, 'ttbp_update_starter_times']);

        add_action('wp_ajax_ttbp_update_cart_serial', [$this, 'ttbp_update_cart_serial']);
        add_action('wp_ajax_nopriv_ttbp_update_cart_serial', [$this, 'ttbp_update_cart_serial']);

        add_action('wp_ajax_ttbp_update_analytics', [$this, 'ttbp_update_analytics']);
        add_action('wp_ajax_nopriv_ttbp_update_analytics', [$this, 'ttbp_update_analytics']);

        add_action('wp_ajax_ttbp_customer_details', [$this, 'ttbp_customer_details']);
        add_action('wp_ajax_nopriv_ttbp_customer_details', [$this, 'ttbp_customer_details']);

        add_action('wp_ajax_ttbp_update_payment_gateway', [$this, 'ttbp_update_payment_gateway']);
        add_action('wp_ajax_nopriv_ttbp_update_payment_gateway', [$this, 'ttbp_update_payment_gateway']);
    }

    public function ttbp_update_payment_gateway()
    {
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        if (!$date) return __('Select a date to proceed.', 'teetime-booking-pro');
        $today_weekday = date('l', strtotime($date));
        $advStart = strtotime(get_option('ttbp_advPay_restrict_start_' . $today_weekday));
        $advEnd = strtotime(get_option('ttbp_advPay_restrict_end_' . $today_weekday));
        $advRule = get_option('ttbp_advPay_rule_' . $today_weekday);
        $today_currentTime = strtotime(date('H:i', strtotime($time)));
        $forceAdv = false;
        if ($today_currentTime >= $advStart && $today_currentTime <= $advEnd) {
            if ($advRule == 'force-advPay') {
                $forceAdv = true;
            }
        }

        wp_send_json_success(array(
            'forceAdv' => $forceAdv,
            'timeStamps' => [$advStart, $advEnd, $today_currentTime]
        ));
    }

    public function ttbp_customer_details()
    {
        global $wpdb;
        $keyword = sanitize_text_field($_POST['keyword']);
        $id = sanitize_text_field($_POST['id']);
        $like_keyword = '%' . $wpdb->esc_like($keyword) . '%';
        $postmeta = $wpdb->prefix . 'postmeta';

        $sql = "
    SELECT DISTINCT name, email, phone FROM (
        SELECT
            p1.post_id,
            MAX(CASE WHEN p1.meta_key = 'ttbp_name' THEN p1.meta_value END) AS name,
            MAX(CASE WHEN p1.meta_key = 'ttbp_email' THEN p1.meta_value END) AS email,
            MAX(CASE WHEN p1.meta_key = 'ttbp_phone' THEN p1.meta_value END) AS phone
        FROM {$postmeta} p1
        WHERE p1.post_id IN (
            SELECT post_id
            FROM {$postmeta}
            WHERE meta_key IN ('ttbp_email', 'ttbp_phone')
            AND meta_value LIKE %s
        )
        AND p1.meta_key IN ('ttbp_name', 'ttbp_email', 'ttbp_phone')
        GROUP BY p1.post_id
    ) AS grouped
    GROUP BY grouped.name, grouped.email, grouped.phone
";

        $results = $wpdb->get_results($wpdb->prepare($sql, $like_keyword), ARRAY_A);
        $output = '';
        if (!empty($results)) {
            foreach ($results as $result) {
                $output .= '<li class="ttbp_acResult" data-name="' . $result['name'] . '" data-email="' . $result['email'] . '" data-phone="' . $result['phone'] . '">
                    <span><strong>' . $result['name'] . '</strong></span>
                    <span><i class="fa-solid fa-envelope"></i> ' . $result['email'] . '</span>
                    <span><i class="fa-solid fa-phone"></i> ' . $result['phone'] . '</span>
                </li>';
            }
        }

        wp_send_json_success(array(
            'output' => $output,
            'id' => $id,
        ));
        wp_die();
    }

    public function ttbp_update_analytics()
    {
        $date = sanitize_text_field($_POST['date']);
        $ins = new TTBP_Analytics();
        $analytics = $ins->get_analytics($date);

        wp_send_json_success(array(
            'bookings' => $analytics['bookings'],
            'noshows' => $analytics['no_shows'],
            'checked_in' => $analytics['checked_in'],
            'revenue' => TTBP__price($analytics['revenue']),
            'occupancy' => $analytics['occupancy'] . '%',
        ));
        wp_die();
    }

    public function ttbp_update_cart_serial()
    {
        $booking_id = sanitize_text_field($_POST['booking_id']);
        $serial = sanitize_text_field($_POST['serial']);
        $key = sanitize_text_field($_POST['key']);

        update_post_meta($booking_id, $key, $serial);
        wp_send_json_success(__('Updated', 'teetime-booking-pro'));
        wp_die();
    }

    public function ttbp_update_starter_times()
    {
        $key = sanitize_text_field($_POST['key']);
        $el = sanitize_text_field($_POST['el']);
        $booking_id = sanitize_text_field($_POST['booking_id']);
        $time = time();
        update_post_meta($booking_id, 'ttbp_' . $key, $time);

        $output = array(
            'el' => $el,
            'time' => date('g:ia', $time),
            'id' => $booking_id,
        );

        $teed_off = get_post_meta($booking_id, 'ttbp_teed_off', true);
        $turn_time = get_post_meta($booking_id, 'ttbp_turn_time', true);
        $end_time = get_post_meta($booking_id, 'ttbp_end_time', true);

        if ($teed_off !== '' && $turn_time !== '') {
            $front_nine = TTBP_timediff($teed_off, $turn_time);
            $output['front_nine'] = $front_nine;
            update_post_meta($booking_id, 'ttbp_front_nine', $front_nine);
        }

        if ($turn_time !== '' && $end_time !== '') {
            $back_nine = TTBP_timediff($turn_time, $end_time);
            $output['back_nine'] = $back_nine;
            update_post_meta($booking_id, 'ttbp_back_nine', $back_nine);
        }

        if (isset($output['front_nine']) && isset($output['back_nine'])) {
            $time_total = TTBP_timesum(TTBP_normalize_time_string($front_nine), TTBP_normalize_time_string($back_nine));
            $output['time_total'] = $time_total;
            update_post_meta($booking_id, 'ttbp_time_total', $time_total);
        }

        wp_send_json_success($output);

        wp_die();
    }

    public function ttbp_redeem_coupon()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'postmeta';
        $coupon = sanitize_text_field($_POST['coupon']);
        $discount_for = sanitize_text_field($_POST['discount_for']);
        $per_player_cost = sanitize_text_field($_POST['per_player_cost']);
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM $table WHERE meta_value = %s AND meta_key = %s",
            $coupon,
            '_ttbp_coupon_code'
        ));
        if (count($results) > 0) {
            $coupon_id = $results[0]->post_id;
            if (get_post_status($coupon_id) !== 'publish') {
                wp_send_json_error(__('Enter a valid coupon code.', 'teetime-booking-bro'));
            }
            $use_limit       = (int) get_post_meta($coupon_id, '_ttbp_coupon_use_limit', true);
            $discount_type   = get_post_meta($coupon_id, '_ttbp_coupon_discount_type', true);
            $discount_amount = (float) get_post_meta($coupon_id, '_ttbp_coupon_discount_amount', true);
            if ($discount_type == 'percentage') {
                $discount_amount = $per_player_cost * ($discount_amount / 100);
            }

            if ($use_limit > 0) {
                wp_send_json_success(array(
                    'discount' => $discount_amount,
                    'coupon_code' => $coupon,
                    'coupon_id' => $coupon_id,
                    'player' => $discount_for,
                ));
            } elseif ($use_limit == '') {
                wp_send_json_success(array(
                    'discount' => $discount_amount,
                    'coupon_code' => $coupon,
                    'coupon_id' => $coupon_id,
                    'player' => $discount_for,
                ));
            } else {
                wp_send_json_error(__('Coupon code use limit reached.', 'teetime-booking-bro'));
            }
        } else {
            wp_send_json_error(__('Enter a valid coupon code.', 'teetime-booking-bro'));
        }
    }

    public function ttbp_bulk_booking()
    {
        parse_str($_POST['data'], $form);
        $metadata = $form;
        unset($metadata['ttbp_block_slots']);
        unset($metadata['ttbp_per_book_price']);
        unset($metadata['ttbp_booking_amount']);
        unset($metadata['ttbp_total_amount']);

        $slots = json_decode($form['ttbp_block_slots'], true);
        $price_per_booking = json_decode($form['ttbp_per_book_price'], true);

        $subtotal = (float) sanitize_text_field($form['ttbp_total_amount']) - (float) sanitize_text_field($form['ttbp_discount']);

        $response = $this->initiate_bulk_book($slots, $price_per_booking, $metadata, $form, '');
        wp_send_json_success(array(
            'errors' => implode(' | ', $response)
        ));

        wp_die();
    }

    private function initiate_bulk_book($slots, $price_per_booking, $metadata, $form, $data)
    {
        $errors = [];
        foreach ($slots as $date => $times) {
            foreach ($times as $time => $players) {
                $title = sanitize_text_field($metadata['ttbp_name']) . ' - ' . sanitize_text_field($date) . ' @ ' . sanitize_text_field(date('g:ia', strtotime($time)));
                $booking_id = wp_insert_post([
                    'post_type'   => 'ttbp_booking',
                    'post_title'  => sanitize_text_field($title),
                    'post_status' => 'publish',
                ]);

                if (is_wp_error($booking_id)) {
                    $errors[] = $title;
                    error_log('Something went wrong.');
                } else {
                    update_post_meta($booking_id, 'ttbp_players', $players);
                    update_post_meta($booking_id, 'ttbp_date', $date);
                    update_post_meta($booking_id, 'ttbp_time', date('g:ia', strtotime($time)));
                    update_post_meta($booking_id, 'ttbp_total_amount', isset($price_per_booking[$time]) ? $price_per_booking[$time] : 0);
                    update_post_meta($booking_id, 'ttbp_pos_info', $data);
                    $use_limit  = (int) get_post_meta(sanitize_text_field($form['ttbp_coupon_id']), '_ttbp_coupon_use_limit', true);
                    update_post_meta(sanitize_text_field($form['ttbp_coupon_id']), '_ttbp_coupon_use_limit', ($use_limit > 0 ? $use_limit - 1 : 0));
                    foreach ($metadata as $key => $value) {
                        update_post_meta($booking_id, $key, sanitize_text_field($value));
                    }
                    wp_set_post_terms($booking_id, [$form['ttbp_golf_course']], 'golf_course');
                    TTBP_Database::insert_slot($date, strtotime($time), $booking_id, $players, 4);

                    $courseName = 'Golf Course Name - Not Mentioned';

                    $term = get_term_by('id', $form['ttbp_golf_course'], 'golf_course');

                    if ($term) {
                        $courseName = $term->name;
                    }

                    $tax_per_person = (float) get_post_meta($booking_id, 'ttbp_per_person_tax', true);
                    $booked_participants = (int) $players;
                    $booked_tax_amount = $tax_per_person * $booked_participants;

                    $email_data = [
                        'name' => $form['ttbp_name'],
                        'email' => $form['ttbp_email'],
                        'phone' => $form['ttbp_phone'],
                        'date' => $date,
                        'time' => $time,
                        'players' => $players,
                        'holes' => $form['ttbp_holes'],
                        'ride' => ucwords(sanitize_text_field($form['ttbp_ride'])),
                        'golf_course' => $courseName,
                        'total_amount' => isset($price_per_booking[$time]) ? $price_per_booking[$time] + $booked_tax_amount : 0,
                        'booking_amount' => TTBP__price(0),
                        'due_amount' => TTBP__price(0),
                        'support_email' => get_option('ttbp_email_from', ''),
                    ];

                    $email_sent = TTBP__send_email($form['ttbp_email'], $email_data);
                    error_log($email_sent);

                    update_post_meta($booking_id, 'ttbp_email_sent', $email_sent);
                }
            }
        }

        return $errors;
    }

    public function ttbp_date_change()
    {
        $date = sanitize_text_field($_POST['date']);
        $formatted_date = date('Y-m-d', strtotime($date));

        $date = date('d', strtotime($formatted_date));
        $day = date('l', strtotime($formatted_date));
        $month = date('M', strtotime($formatted_date));
        $year = date('Y', strtotime($formatted_date));
        $output = '';
        $output .= "<span class='calendar-date'>$date</span>";
        $output .= "<span class='calendar-content'><span>$day</span><span>$month $year</span></span>`";

        wp_send_json_success([
            'output' => $output,
            'date' => $formatted_date,
        ]);

        wp_die();
    }

    public function ttbp_checkout_review()
    {
        $booking_amount = sanitize_text_field($_POST['booking_amount']);
        $holes = sanitize_text_field($_POST['holes']);
        $total_amount = sanitize_text_field($_POST['total_amount']);
        $due_pay = sanitize_text_field($_POST['due_pay']);
        $players = sanitize_text_field($_POST['players']);
        $course = sanitize_text_field($_POST['course']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $ride = sanitize_text_field($_POST['ride']);
        $advPay = sanitize_text_field($_POST['advPay']);
        $method = sanitize_text_field($_POST['method']);
        $taxRate = sanitize_text_field($_POST['taxRate']);
        $taxAmount = sanitize_text_field($_POST['taxAmount']);
?>

        <table class="ttbp_table">
            <thead>
                <tr>
                    <th colspan="2">
                        <h3><?php _e('Booking Summary', 'teetime-booking-pro'); ?></h3>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="booking-info">
                    <td colspan="2">
                        <h2><?php echo $course ?></h2>
                        <ul>
                            <li><i class="fa-regular fa-circle"></i> <span><?php echo $holes; ?> <?php _e('hole(s)', 'teetime-booking-pro'); ?></span></li>
                            <li><i class="fa-solid fa-people-group"></i> <span><?php echo $players; ?> <?php _e('player(s)', 'teetime-booking-pro'); ?></span></li>
                            <?php if ($ride === 'cart'): ?>
                                <li><?php TTBP_get_cart_icon(); ?> <span><?php echo $players; ?> <?php _e('Cart', 'teetime-booking-pro'); ?></span></li>
                            <?php else: ?>
                                <li><i class="fa-solid fa-person-walking"></i> <span><?php _e('Walking', 'teetime-booking-pro'); ?></span></li>
                            <?php endif; ?>
                            <li><i class="fa-regular fa-calendar"></i> <span><?php echo date('F j, Y', strtotime($date)); ?></span></li>
                            <li><i class="fa-regular fa-clock"></i> <span><?php echo date('g:i A', strtotime($time)); ?></span></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-amount">
                    <th><?php _e('Total Payable', 'teetime-booking-pro'); ?></th>
                    <td><?php echo TTBP__price($total_amount) ?></td>
                </tr>
                <?php if ($advPay !== '' && $method == 'clover'):
                    $discount = (float) $total_amount * ((float) $advPay / 100);
                ?>
                    <tr class="booking-amount">
                        <th><?php _e('Adv. Pay Discount', 'teetime-booking-pro'); ?></th>
                        <td>
                            <?php echo '-' . TTBP__price($discount); ?>
                            <input type="hidden" name="ttbp_booking_discount" value="<?php echo $discount; ?>">
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if ((float) $taxAmount > 0): ?>
                    <tr class="tax-amount">
                        <th><?php _e('Tax <small>(' . $taxRate . '%)</small>', 'teetime-booking-pro'); ?></th>
                        <td>
                            <?php echo TTBP__price($taxAmount); ?>
                            <input type="hidden" name="ttbp_tax_amount" value="<?php echo $taxAmount; ?>">
                        </td>
                    </tr>
                <?php endif; ?>
                <tr class="booking-amount">
                    <th><?php _e('Booking Deposit', 'teetime-booking-pro'); ?></th>
                    <td><?php echo TTBP__price($booking_amount) ?></td>
                </tr>
                <tr class="due-amount">
                    <th><?php _e('Total Due at Course', 'teetime-booking-pro'); ?></th>
                    <td><?php echo TTBP__price($due_pay) ?></td>
                </tr>
            </tfoot>
        </table>
    <?php
        wp_die();
    }

    public function ttbp_update_booking()
    {
        parse_str($_POST['formData'], $form);
        $data = $form;

        $player = sanitize_text_field($data['ttbp_slot_players']);
        $status = sanitize_text_field($data['ttbp_payment_status']);
        $booking_id = sanitize_text_field($data['ttbp_bookingID']);
        $delete_reason = sanitize_text_field($data['ttbp_delete_reason']);
        $delete_reason_explain = sanitize_text_field($data['ttbp_delete_explain']);
        $total_discount = (float) sanitize_text_field($data['ttbp_discount_player_1']) + (float) sanitize_text_field($data['ttbp_discount_player_2']) + (float) sanitize_text_field($data['ttbp_discount_player_3']) + (float) sanitize_text_field($data['ttbp_discount_player_4']);
        $pos_obj = !empty(sanitize_text_field($data['ttbp_pos_object'])) ? explode(',', sanitize_text_field($data['ttbp_pos_object'])) : [];
        $pos_total = 0;

        $ex_players = get_post_meta($booking_id, 'ttbp_players', true);
        $ex_total_amount = get_post_meta($booking_id, 'ttbp_total_amount', true);
        $per_person_tax = (float) get_post_meta($booking_id, 'ttbp_per_person_tax', true);
        $per_player = (float) $ex_total_amount / (int) $ex_players;
        $new_total = $per_player * (int) $player;
        $new_total = $new_total;
        $line_items = [];

        foreach ($pos_obj as $item) {
            $pos_total += ($per_player - (float) $data["ttbp_discount_player_$item"]);
            $description = 'Paid by player - ' . $item;
            if (isset($data['ttbp_player_name_' . $item]) && !empty($data['ttbp_player_name_' . $item])) {
                $description = 'Paid by - ' . $data['ttbp_player_name_' . $item];
            }
            $line_item_data = [
                'amount'       => ($per_player - (float) $data["ttbp_discount_player_$item"] + $per_person_tax) * 100,
                'currency'     => get_option('ttbp_currency', 'USD'),
                'description'  => $description,
                'quantity'     => 1,
            ];


            // $line_item_data['tax_rates'] = array(
            //     array(
            //         'name'       => 'Tax amount paid by player ' . $item,
            //         'tax_amount' => $per_person_tax * 100
            //     ),
            // );

            $line_items[] = $line_item_data;
        }

        if (!$booking_id) {
            wp_send_json_error(__('Something went wrong.', 'teetime-booking-pro'));
        }

        for ($i = 1; $i <= 4; $i++) {
            // update_post_meta($booking_id, 'ttbp_payment_status_' . $i, sanitize_text_field(isset($data['ttbp_payment_status_' . $i]) ? $data['ttbp_payment_status_' . $i] : ''));
            update_post_meta($booking_id, 'ttbp_player_name_' . $i, sanitize_text_field(isset($data['ttbp_player_name_' . $i]) ? $data['ttbp_player_name_' . $i] : ''));
            update_post_meta($booking_id, 'ttbp_player_email_' . $i, sanitize_text_field(isset($data['ttbp_player_email_' . $i]) ? $data['ttbp_player_email_' . $i] : ''));
            update_post_meta($booking_id, 'ttbp_player_phone_' . $i, sanitize_text_field(isset($data['ttbp_player_phone_' . $i]) ? $data['ttbp_player_phone_' . $i] : ''));
        }

        update_post_meta($booking_id, 'ttbp_players', $player);
        update_post_meta($booking_id, 'ttbp_total_amount', $new_total);
        update_post_meta($booking_id, 'ttbp_total_discount', $total_discount);
        update_post_meta($booking_id, 'ttbp_payment_status', $status);
        update_post_meta($booking_id, 'ttbp_delete_reason', $delete_reason);
        update_post_meta($booking_id, 'ttbp_delete_explain', $delete_reason_explain);
        update_post_meta($booking_id, 'ttbp_discount_player_1', sanitize_text_field($data['ttbp_discount_player_1']));
        update_post_meta($booking_id, 'ttbp_discount_player_2', sanitize_text_field($data['ttbp_discount_player_2']));
        update_post_meta($booking_id, 'ttbp_discount_player_3', sanitize_text_field($data['ttbp_discount_player_3']));
        update_post_meta($booking_id, 'ttbp_discount_player_4', sanitize_text_field($data['ttbp_discount_player_4']));


        $due_amount = (float) get_post_meta($booking_id, 'ttbp_total_amount', true) - (float) get_post_meta($booking_id, 'ttbp_booking_amount', true);
        $tax_per_person = (float) get_post_meta($booking_id, 'ttbp_per_person_tax', true);
        $booked_participants = (int) get_post_meta($booking_id, 'ttbp_players', true);
        $booked_tax_amount = $tax_per_person * $booked_participants;
        $booked_total_amount = (float) get_post_meta($booking_id, 'ttbp_total_amount', true);

        TTBP_Database::update_slot($booking_id, null, null, $player);
        $email_term = get_term_by('term_id', get_post_meta($booking_id, 'ttbp_golf_course', true), 'golf_course');
        $email_data = [
            'name' => get_post_meta($booking_id, 'ttbp_name', true),
            'email' => get_post_meta($booking_id, 'ttbp_email', true),
            'phone' => get_post_meta($booking_id, 'ttbp_phone', true),
            'date' => get_post_meta($booking_id, 'ttbp_date', true),
            'time' => get_post_meta($booking_id, 'ttbp_time', true),
            'players' => get_post_meta($booking_id, 'ttbp_players', true),
            'holes' => get_post_meta($booking_id, 'ttbp_holes', true),
            'ride' => ucwords(esc_html(get_post_meta($booking_id, 'ttbp_ride', true))),
            'golf_course' => $email_term->name,
            'total_amount' => TTBP__price($booked_total_amount + $booked_tax_amount),
            'booking_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_booking_amount', true)),
            'due_amount' => TTBP__price($due_amount + $booked_tax_amount),
            'support_email' => get_option('ttbp_email_from', ''),
        ];

        $email_sent = TTBP__send_email($email_data['email'], $email_data, 'update');
        error_log($email_sent);

        if (sanitize_text_field($data['ttbp_payment_method']) == 'pos' && !empty($pos_obj)) {
            $pos_request = [
                'currency' => get_option('ttbp_currency', 'USD'),
                'email'    => sanitize_email(get_post_meta($booking_id, 'ttbp_email', true)),
                'items'    => $line_items,
            ];

            $pos_ins = new TTBP_POS_Payment();

            try {
                $pos_res = $pos_ins->create_order($pos_request);
                error_log(json_encode($pos_res));
                if ($pos_res->status && $pos_res->status == 'created') {
                    foreach ($pos_obj as $item) {
                        update_post_meta($booking_id, 'ttbp_pos_info_' . $item, $pos_res);
                        update_post_meta($booking_id, 'ttbp_payment_status_' . $item, 'paid');
                    }
                } else {
                    foreach ($pos_obj as $item) {
                        update_post_meta($booking_id, 'ttbp_pos_info_' . $item, false);
                        update_post_meta($booking_id, 'ttbp_payment_status_' . $item, 'due');
                    }
                    wp_send_json_error(__('Error placing purchase order, Try again.', 'teetime-booking-pro'));
                }
            } catch (Exception $e) {
                foreach ($pos_obj as $item) {
                    update_post_meta($booking_id, 'ttbp_pos_info_' . $item, false);
                    update_post_meta($booking_id, 'ttbp_payment_status_' . $item, 'due');
                }
                wp_send_json_error(__($e->getMessage(), 'teetime-booking-pro'));
            }

            $device_args = [
                'amount' => $new_total,
                'taxAmount' => $booked_tax_amount * 100
            ];

            try {
                $device_response = $pos_ins->cloud_device_payment($device_args);
                if ($device_response['success']) {
                    update_post_meta($booking_id, 'ttbp_device_response', $device_response['data']);
                } else {
                    update_post_meta($booking_id, 'ttbp_device_response', $device_response['data']);
                    wp_send_json_error(__($device_response['error'], 'teetime-booking-pro'));
                }
            } catch (Exception $e) {
                wp_send_json_error(__($e->getMessage(), 'teetime-booking-pro'));
            }
        }

        wp_send_json_success(__('Booking slots updated successfull!', 'teetime-booking-pro'));
        wp_die();
    }

    public function ttbp_delete_booking()
    {

        parse_str($_POST['formData'], $data);

        $booking_id = sanitize_text_field($data['ttbp_bookingID']);

        if (!$booking_id) {
            wp_die();
        }

        $email_term = get_term_by('term_id', get_post_meta($booking_id, 'ttbp_golf_course', true), 'golf_course');

        $due_amount = (float) get_post_meta($booking_id, 'ttbp_total_amount', true) - (float) get_post_meta($booking_id, 'ttbp_booking_amount', true);
        $tax_per_person = (float) get_post_meta($booking_id, 'ttbp_per_person_tax', true);
        $booked_participants = (int) get_post_meta($booking_id, 'ttbp_players', true);
        $booked_tax_amount = $tax_per_person * $booked_participants;
        $booked_total_amount = (float) get_post_meta($booking_id, 'ttbp_total_amount', true);

        $email_data = [
            'name' => get_post_meta($booking_id, 'ttbp_name', true),
            'email' => get_post_meta($booking_id, 'ttbp_email', true),
            'phone' => get_post_meta($booking_id, 'ttbp_phone', true),
            'date' => get_post_meta($booking_id, 'ttbp_date', true),
            'time' => get_post_meta($booking_id, 'ttbp_time', true),
            'players' => get_post_meta($booking_id, 'ttbp_players', true),
            'holes' => get_post_meta($booking_id, 'ttbp_holes', true),
            'ride' => ucwords(esc_html(get_post_meta($booking_id, 'ttbp_ride', true))),
            'golf_course' => $email_term->name,
            'total_amount' => TTBP__price($booked_total_amount + $booked_tax_amount),
            'booking_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_booking_amount', true)),
            'due_amount' => TTBP__price($due_amount + $booked_tax_amount),
            'support_email' => get_option('ttbp_email_from', ''),
        ];

        $delete = wp_delete_post($booking_id, true);

        if (!is_wp_error($delete)) {
            TTBP_Database::delete_slot($booking_id);
            $canceled_email_sent = TTBP__send_email($email_data['email'], $email_data, 'canceled');
            error_log($canceled_email_sent);
        }

        wp_die();
    }

    public function ttbp_update_entry()
    {
        parse_str($_POST['formData'], $form);

        if (empty($form)) wp_send_json_error(__('No data submitted', 'teetime-booking-pro'));

        $form['ttbp_booking_amount'] = 0;
        $formattedTime = date('g:ia', strtotime($form['ttbp_time']));
        $form['ttbp_time'] = $formattedTime;
        $title = $form['ttbp_name'] . ' - ' . $form['ttbp_date'] . ' @ ' . $form['ttbp_time'];

        $total_booking = $form['ttbp_total_amount'];
        $form['ttbp_total_amount'] = (int) $form['ttbp_players'] * (float) $total_booking;
        $taxable_amount = floatval($form['ttbp_taxable_amount']) > 0 ? floatval($form['ttbp_taxable_amount']) * (int) $form['ttbp_players'] : (float) $form['ttbp_total_amount'];
        $tax_rate = (float) $form['ttbp_tax_rate'];

        $tax_amount = $taxable_amount * ($tax_rate / 100);
        $tax_per_person = $tax_amount / (int) $form['ttbp_players'];

        if (sanitize_text_field($form['ttbp_ride']) != 'cart') {
            $tax_amount = 0;
        }

        $form['ttbp_tax_amount'] = $tax_amount;
        $form['ttbp_per_person_tax'] = $tax_per_person;
        unset($form['ttbp_taxable_amount']); //These should not be saved on the metadata
        unset($form['ttbp_tax_rate']); //These should not be saved on the metadata

        $existing_bookings = get_posts([
            'post_type'      => 'ttbp_booking',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'   => 'ttbp_date',
                    'value' => sanitize_text_field($form['ttbp_date']),
                ],
                [
                    'key'   => 'ttbp_time',
                    'value' => sanitize_text_field($form['ttbp_time']),
                ],
            ],
        ]);

        $booked = 0;
        if (!empty($existing_bookings)) {
            foreach ($existing_bookings as $ex_booking) {
                $booked += (int) get_post_meta($ex_booking->ID, 'ttbp_players', true);
            }
        }

        $available = 4 - (int) $booked;
        $needed = (int) $form['ttbp_players'];

        if ($needed > $available) {
            wp_send_json_error(__('The tee time is already booked or not enough player slots available. Reload the page to get latest updates.', 'teetime-booking-pro'));
            wp_die();
        }

        $proceed_booking = false;
        $payment_method = sanitize_text_field($form['ttbp_payment_method']) ?? 'pos';

        $proceed_booking = true;

        if ($proceed_booking) {
            $booking_id = wp_insert_post([
                'post_type'   => 'ttbp_booking',
                'post_title'  => sanitize_text_field($title),
                'post_status' => 'publish',
            ]);

            if (is_wp_error($booking_id)) {
                wp_send_json_error(__('Something went wrong.', 'teetime-booking-pro'));
                wp_die();
            }

            TTBP_Database::insert_slot(sanitize_text_field($form['ttbp_date']), strtotime($form['ttbp_time']), intval($booking_id), intval($form['ttbp_players']), 4);

            $courseName = 'Golf Course Name - Not Mentioned';

            $term = get_term_by('id', $form['ttbp_golf_course'], 'golf_course');

            if ($term) {
                $courseName = $term->name;
            }

            $email_data = [
                'name' => $form['ttbp_name'],
                'email' => $form['ttbp_email'],
                'phone' => $form['ttbp_phone'],
                'date' => $form['ttbp_date'],
                'time' => $form['ttbp_time'],
                'players' => $form['ttbp_players'],
                'holes' => $form['ttbp_holes'],
                'ride' => ucwords(sanitize_text_field($form['ttbp_ride'])),
                'golf_course' => $courseName,
                'total_amount' => TTBP__price((float) $form['ttbp_total_amount'] + $tax_amount),
                'booking_amount' => TTBP__price($form['ttbp_booking_amount']),
                'due_amount' => TTBP__price((float) $form['ttbp_total_amount'] + $tax_amount - (float) $form['ttbp_booking_amount']),
                'support_email' => get_option('ttbp_email_from', ''),
            ];

            $email_sent = TTBP__send_email($form['ttbp_email'], $email_data);
            error_log($email_sent);

            update_post_meta($booking_id, 'ttbp_email_sent', $email_sent);

            foreach ($form as $field_key => $field_value) {
                update_post_meta($booking_id, $field_key, sanitize_text_field($field_value));
            }

            wp_set_post_terms($booking_id, [$form['ttbp_golf_course']], 'golf_course');

            wp_send_json_success(__('Form Submitted successfully', 'teetime-booking-pro'));
        } else {
            wp_send_json_error(__('Something went wrong.', 'teetime-booking-pro'));
        }

        wp_die();
    }

    public function ttbp_render_bookings()
    {
        $date = sanitize_text_field($_POST['date']);
        $custom_slots = get_option('ttbp_custom_slot_rules') ? get_option('ttbp_custom_slot_rules') : [];
        $custom_slot = isset($custom_slots[$date]) ? $custom_slots[$date] : [];
        $startTime = isset($custom_slot['start_morning']) ? $custom_slot['start_morning'] : get_option('ttbp_default_start_morning');
        $endTime = isset($custom_slot['end']) ? $custom_slot['end'] : get_option('ttbp_default_end');

        $duration = isset($custom_slot['duration']) ? $custom_slot['duration'] : get_option('ttbp_default_duration');
        $shortcodesInit = new TTBP_Shortcodes();

        $shortcodesInit->render_time_rows($startTime, $endTime, $duration, $date);
        wp_die();
    }

    public function render_week_dates()
    {
        $current = sanitize_text_field($_POST['current']);
        $dateStr = strtotime($current);
        $lead_days = get_option('ttbp_max_booking_window');
    ?>
        <button type="button" data-date="<?php echo date('Y-m-d', $dateStr) ?>">
            <?php
            $today = strtotime(date('Y-m-d'));
            if ($today == $dateStr) {
                echo __('Today', 'teetime-booking-pro');
            } else {
                echo date('D d', $dateStr);
            }
            ?>
        </button>
        <button type="button" class="<?php echo (strtotime("+{$lead_days} days") < strtotime('+1 days', $dateStr)) ? 'ttbp_disallow' : '' ?>" data-date="<?php echo date('Y-m-d', strtotime('+1 days', $dateStr)) ?>"><?php echo date('D j', strtotime('+1 days', $dateStr)) ?></button>
        <button type="button" class="<?php echo (strtotime("+{$lead_days} days") < strtotime('+2 days', $dateStr)) ? 'ttbp_disallow' : '' ?>" data-date="<?php echo date('Y-m-d', strtotime('+2 days', $dateStr)) ?>"><?php echo date('D j', strtotime('+2 days', $dateStr)) ?></button>
        <button type="button" class="<?php echo (strtotime("+{$lead_days} days") < strtotime('+3 days', $dateStr)) ? 'ttbp_disallow' : '' ?>" data-date="<?php echo date('Y-m-d', strtotime('+3 days', $dateStr)) ?>"><?php echo date('D j', strtotime('+3 days', $dateStr)) ?></button>
        <button type="button" class="<?php echo (strtotime("+{$lead_days} days") < strtotime('+4 days', $dateStr)) ? 'ttbp_disallow' : '' ?>" data-date="<?php echo date('Y-m-d', strtotime('+4 days', $dateStr)) ?>"><?php echo date('D j', strtotime('+4 days', $dateStr)) ?></button>
        <button type="button" class="<?php echo (strtotime("+{$lead_days} days") < strtotime('+5 days', $dateStr)) ? 'ttbp_disallow' : '' ?>" data-date="<?php echo date('Y-m-d', strtotime('+5 days', $dateStr)) ?>"><?php echo date('D j', strtotime('+5 days', $dateStr)) ?></button>
        <button type="button" class="<?php echo (strtotime("+{$lead_days} days") < strtotime('+6 days', $dateStr)) ? 'ttbp_disallow' : '' ?>" data-date="<?php echo date('Y-m-d', strtotime('+6 days', $dateStr)) ?>"><?php echo date('D j', strtotime('+6 days', $dateStr)) ?></button>
    <?php

        wp_die();
    }

    // Fetch available time slots (dummy data for now)
    public function fetch_slots()
    {
        check_ajax_referer('ttbp_nonce', 'nonce');

        $date = sanitize_text_field($_POST['date']);
        $slots = sanitize_text_field($_POST['slots']);
        $holes = sanitize_text_field($_POST['holes']);
        $tod = sanitize_text_field($_POST['tod']);
        $ride = sanitize_text_field($_POST['ride']);
        $golf_course = sanitize_text_field($_POST['golf_course']);

        $bookings = get_posts([
            'post_type' => 'ttbp_booking',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'ttbp_date',
                    'value' => $date,
                    'compare' => '='
                )
            ),
        ]);

        $dismiss_player = [];
        $dismiss = [];
        $allow = [];

        if (!empty($bookings)) {
            foreach ($bookings as $booking) {
                $booking_id = $booking->ID;
                $time = get_post_meta($booking_id, 'ttbp_time', true);
                $player = get_post_meta($booking_id, 'ttbp_players', true);
                $condition_count = isset(TTBP_Database::get_available_slots($date, strtotime($time))[0]['total_booked']) ? TTBP_Database::get_available_slots($date, strtotime($time))[0]['total_booked'] : 0;

                if ($slots > (4 - $condition_count)) {
                    $dismiss[] = strtotime($time);
                } else {
                    $allow[strtotime($time)] = (4 - $condition_count);
                }
            }
        }
        $custom_slots = get_option('ttbp_custom_slot_rules') ? get_option('ttbp_custom_slot_rules') : [];
        $custom_slot = isset($custom_slots[$date]) ? $custom_slots[$date] : [];

        $startTime = '';

        if ($tod == 'morning') {
            $startTime = isset($custom_slot['start_morning']) ? $custom_slot['start_morning'] : get_option('ttbp_default_start_morning');
            $endTime = isset($custom_slot['start_midday']) ? $custom_slot['start_midday'] : get_option('ttbp_default_start_midday');
        } elseif ($tod == 'midday') {
            $startTime = isset($custom_slot['start_midday']) ? $custom_slot['start_midday'] : get_option('ttbp_default_start_midday');
            $endTime = isset($custom_slot['start_evening']) ? $custom_slot['start_evening'] : get_option('ttbp_default_start_evening');
        } elseif ($tod == 'evening') {
            $startTime = isset($custom_slot['start_evening']) ? $custom_slot['start_evening'] : get_option('ttbp_default_start_evening');
            $endTime = isset($custom_slot['end']) ? $custom_slot['end'] : get_option('ttbp_default_end');
        }

        $duration = isset($custom_slot['duration']) ? $custom_slot['duration'] : get_option('ttbp_default_duration');
        $booking_amount = get_option('ttbp_booking_amount', 0);
        if ($ride == 'cart') {
            $total_pay_9 = get_option('ttbp_total_amount_9_cart', 0);
            $total_pay_18 = get_option('ttbp_total_amount_18_cart', 0);
        } else {
            $total_pay_9 = get_option('ttbp_total_amount_9_walking', 0);
            $total_pay_18 = get_option('ttbp_total_amount_18_walking', 0);
        }

        $available_slots = render_slots($startTime, $endTime, $duration);
        $filtered_slots = array_values(array_diff($available_slots, $dismiss));
        $lead_time = get_option('ttbp_lead_time');

        $pricing_rules = get_option('ttbp_custom_pricing') ? get_option('ttbp_custom_pricing') : [];
        $rule = [];
        $weekDay = date('N', strtotime($date));
        $selectedWeekday = date('l', strtotime($date));
        $restricted_start = get_option('ttbp_restrict_start_' . $selectedWeekday, false);
        $restricted_end = get_option('ttbp_restrict_end_' . $selectedWeekday, false);
        $restricted_hole = get_option('ttbp_restrict_holes_' . $selectedWeekday, '');
        $restricted_ride = get_option('ttbp_restrict_ride_' . $selectedWeekday, '');

        if (isset($pricing_rules[$weekDay])) {
            $rule = $pricing_rules[$weekDay];
        }


        if (!empty($filtered_slots)) {
            foreach ($filtered_slots as $item_key => $item_value) {
                $player_count = isset($allow[$item_value]) ? $allow[$item_value] : 4;
                $time_now = time();
                $date_today = date('Y-m-d');
                $difference = abs($item_value - $time_now) / 60;

                if (!empty($rule)) {
                    if (!empty($rule['window_start'])) {
                        foreach ($rule['window_start'] as $key => $value) {
                            $window_start_timestamp = strtotime($rule['window_start'][$key]);
                            $window_end_timestamp = strtotime($rule['window_end'][$key]);
                            if (($item_value >= $window_start_timestamp) && ($item_value <= $window_end_timestamp)) {
                                $booking_amount = $rule['booking_amount'][$key];
                                if ($ride == 'cart') {
                                    $total_pay_9 = $rule['total_amount_9_cart'][$key];
                                    $total_pay_18 = $rule['total_amount_18_cart'][$key];
                                } else {
                                    $total_pay_9 = $rule['total_amount_9_walking'][$key];
                                    $total_pay_18 = $rule['total_amount_18_walking'][$key];
                                }
                                break;
                            } else {
                                $booking_amount = get_option('ttbp_booking_amount', 0);
                                if ($ride == 'cart') {
                                    $total_pay_9 = get_option('ttbp_total_amount_9_cart', 0);
                                    $total_pay_18 = get_option('ttbp_total_amount_18_cart', 0);
                                } else {
                                    $total_pay_9 = get_option('ttbp_total_amount_9_walking', 0);
                                    $total_pay_18 = get_option('ttbp_total_amount_18_walking', 0);
                                }
                            }
                        }
                    }
                }

                if ($date_today === $date) {
                    if (($time_now < $item_value) && ($difference >= $lead_time)) {
                        if (!$restricted_start || !$restricted_end) {
                            echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18, $ride);
                        } else {
                            if ($holes === $restricted_hole || $ride === $restricted_ride) {
                                if (($item_value < strtotime($restricted_start)) || ($item_value > strtotime($restricted_end))) {
                                    echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18, $ride);
                                }
                            } else {
                                echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18, $ride);
                            }
                        }
                    }
                } else {
                    if (!$restricted_start || !$restricted_end) {
                        echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18, $ride);
                    } else {
                        if ($holes == $restricted_hole || $ride == $restricted_ride) {
                            if (($item_value < strtotime($restricted_start)) || ($item_value > strtotime($restricted_end))) {
                                echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18, $ride);
                            }
                        } else {
                            echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18, $ride);
                        }
                    }
                }
            }
        } else {
            echo __('No slots available', 'teetime-booking-pro');
        }

        wp_die();
    }

    private function render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18, $ride)
    {
        ob_start();
    ?>
        <label for="ttbp_slot_<?php echo $item_key; ?>">
            <input type="radio" name="ttbp_time" value="<?php echo date('g:ia', $item_value) ?>" id="ttbp_slot_<?php echo $item_key; ?>" data-ride="<?php echo $ride; ?>" data-total_pay_9="<?php echo $total_pay_9; ?>" data-total_pay_18="<?php echo $total_pay_18; ?>" data-booking_amount="<?php echo $booking_amount; ?>">
            <div class="ttbp_slot">
                <div class="ttbp_slot_hole">
                    <?php echo $holes; ?>
                    <span class="ttbp_ride_type">
                        (
                        <?php
                        if ($ride == 'cart') {
                            TTBP_get_cart_icon();
                        } else {
                        ?>
                            <i class="fa-solid fa-person-walking"></i>
                        <?php
                        }
                        ?>
                        )
                    </span>
                </div>
                <div class="ttbp_slot_time">
                    <h2><?php echo date('g:ia', $item_value); ?></h2>
                    <p>
                        <?php
                        $term = get_term_by('id', $golf_course, 'golf_course');
                        if (!empty($term)) {
                            echo $term->name . ' <span class="ttbp_player_count"><i class="fa-regular fa-user"></i> ' . $player_count . ' Player(s)</span>';
                        }
                        ?>
                    </p>
                    <span>
                        <?php
                        if (!empty($term)) {
                            echo $term->description;
                        }
                        ?>
                    </span>
                </div>
                <div class="ttbp_slot_pricing">
                    <i class="fa-solid fa-golf-ball-tee"></i>
                    <span><?php echo TTBP__price($holes == 9 ? $total_pay_9 : $total_pay_18); ?> <small>/Per Person</small> </span>
                </div>
            </div>
        </label>
<?php
        return ob_get_clean();
    }

    // Save booking data
    public function submit_booking()
    {
        check_ajax_referer('ttbp_nonce', 'nonce');

        parse_str($_POST['form'], $form);
        $payment_method = sanitize_text_field($form['ttbp_payment_method']);
        $process_booking = false;
        $payment_data = '';
        $title = sanitize_text_field($form['ttbp_name']) . ' - ' . sanitize_text_field($form['ttbp_date']) . ' @ ' . sanitize_text_field($form['ttbp_time']);
        $taxAmount = 0;
        if (isset($form['ttbp_tax_amount'])) {
            $taxAmount = sanitize_text_field($form['ttbp_tax_amount']);
        }

        // Get the number of player and find out the per person tax
        $adv_payment = isset($form['ttbp_adv_pay']) ? sanitize_text_field($form['ttbp_adv_pay']) : false;
        $participants = sanitize_text_field($form['ttbp_players']);
        $per_person_tax = (float) $taxAmount / (int) $participants;

        $form['ttbp_per_person_tax'] = $per_person_tax; // Set the per person tax as formData to push into the post meta.

        if ($adv_payment) {
            $taxAmount = $per_person_tax;
        }

        if ($payment_method == 'offline') {
            $process_booking = true;
            $form['ttbp_booking_amount'] = 0;
            unset($form['ttbp_adv_pay']);
        } else {
            $amount = sanitize_text_field($form['ttbp_booking_amount']);
            $token = sanitize_text_field($form['ttbp_clover_token']);
            require_once TTBP_PATH . 'includes/class-clover-payment.php';

            $clover_instance = new TTBP_Clover_Pay();

            $payment_data = array(
                'description' => $title,
            );

            if ((float) $taxAmount > 0 && $adv_payment) {
                $payment_data['tax_amount'] = $taxAmount;
            }

            $charge = $clover_instance->create_charge((float)$amount * 100, $token, $payment_data);

            if (is_wp_error($charge)) {
                wp_send_json_error(esc_html__($charge->get_error_message(), 'teetime-booking-pro'));
                wp_die();
            } else {
                $payment_data = $charge;
                $process_booking = true;
            }
        }


        if ($process_booking === false) {
            wp_send_json_error(__('Could not process booking, something went wrong', 'teetime-booking-pro'));
            wp_die();
        }

        // Basic validation

        $existing_bookings = get_posts([
            'post_type'      => 'ttbp_booking',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'   => 'ttbp_date',
                    'value' => sanitize_text_field($form['ttbp_date']),
                ],
                [
                    'key'   => 'ttbp_time',
                    'value' => sanitize_text_field($form['ttbp_time']),
                ],
            ],
        ]);

        $booked = 0;
        if (!empty($existing_bookings)) {
            foreach ($existing_bookings as $ex_booking) {
                $booked += (int) get_post_meta($ex_booking->ID, 'ttbp_players', true);
            }
        }

        $available = 4 - (int) $booked;
        $needed = (int) $form['ttbp_players'];

        if ($needed > $available) {
            wp_send_json_error(__('The tee time is already booked or not enough player slots available. Reload the page to get latest updates.', 'teetime-booking-pro'));
            wp_die();
        }

        $booking_id = wp_insert_post([
            'post_type'   => 'ttbp_booking',
            'post_title'  => sanitize_text_field($title),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($booking_id)) {
            wp_send_json_error(__('Error: Booking could not be made.', 'teetime-booking-pro'));
            wp_die();
        }

        wp_set_post_terms($booking_id, [sanitize_text_field($form['ttbp_golf_course'])], 'golf_course');

        TTBP_Database::insert_slot(esc_html($form['ttbp_date']), strtotime($form['ttbp_time']), $booking_id, esc_html($form['ttbp_players']), 4);

        foreach ($form as $field_key => $field_value) {
            $value = $field_value ? sanitize_text_field($field_value) : '';
            update_post_meta($booking_id, $field_key, $value);
        }

        if (!empty($payment_data)) {
            update_post_meta($booking_id, 'ttbp_payment_data', $payment_data);
        }

        $is_adv_pay = get_post_meta($booking_id, 'ttbp_adv_pay', true) ? get_post_meta($booking_id, 'ttbp_adv_pay', true) : false;

        if ($is_adv_pay) {
            for ($i = 1; $i <= (int) get_post_meta($booking_id, 'ttbp_players', true); $i++) {
                update_post_meta($booking_id, 'ttbp_payment_status_' . $i, 'paid');
            }
        }

        $email_term = get_term_by('term_id', get_post_meta($booking_id, 'ttbp_golf_course', true), 'golf_course');

        $due_amount = $is_adv_pay ? 0 : (float) get_post_meta($booking_id, 'ttbp_total_amount', true) - (float) get_post_meta($booking_id, 'ttbp_booking_amount', true);
        $total_amount = (float) get_post_meta($booking_id, 'ttbp_total_amount', true);
        $booking_discount = (float) get_post_meta($booking_id, 'ttbp_booking_discount', true) ?? 0;
        $total_amount = $total_amount - $booking_discount;
        $tax_per_person = (float) get_post_meta($booking_id, 'ttbp_per_person_tax', true);
        $booked_participants = (int) get_post_meta($booking_id, 'ttbp_players', true);
        $booked_tax_amount = $tax_per_person * $booked_participants;

        $email_data = [
            'name' => get_post_meta($booking_id, 'ttbp_name', true),
            'email' => get_post_meta($booking_id, 'ttbp_email', true),
            'phone' => get_post_meta($booking_id, 'ttbp_phone', true),
            'date' => get_post_meta($booking_id, 'ttbp_date', true),
            'time' => get_post_meta($booking_id, 'ttbp_time', true),
            'players' => get_post_meta($booking_id, 'ttbp_players', true),
            'holes' => get_post_meta($booking_id, 'ttbp_holes', true),
            'ride' => get_post_meta($booking_id, 'ttbp_ride', true) == 'cart' ? 'Cart' : 'Walking',
            'golf_course' => $email_term->name,
            'total_amount' => TTBP__price($total_amount + $booked_tax_amount),
            'booking_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_booking_amount', true)),
            'due_amount' => TTBP__price($due_amount + $booked_tax_amount),
            'support_email' => get_option('ttbp_email_from', ''),
        ];
        $booking_email = TTBP__send_email($email_data['email'], $email_data);
        error_log($booking_email);

        wp_send_json_success(array(
            'booking_id' => $booking_id,
        ));

        wp_die();
    }
}
