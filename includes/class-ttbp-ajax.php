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
                            <li><i class="fa-regular fa-circle"></i> <span><?php echo $holes; ?> hole(s)</span></li>
                            <li><i class="fa-solid fa-people-group"></i> <span><?php echo $players; ?> player(s)</span></li>
                            <li><i class="fa-regular fa-calendar"></i> <span><?php echo date('F j, Y', strtotime($date)); ?></span></li>
                            <li><i class="fa-regular fa-clock"></i> <span><?php echo date('g:i A', strtotime($time)); ?></span></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-amount">
                    <th>Total Payable</th>
                    <td><?php echo TTBP__price($total_amount) ?></td>
                </tr>
                <tr class="booking-amount">
                    <th>Booking Deposit</th>
                    <td><?php echo TTBP__price($booking_amount) ?></td>
                </tr>
                <tr class="due-amount">
                    <th>Total Due at Course</th>
                    <td><?php echo TTBP__price($due_pay) ?></td>
                </tr>
            </tfoot>
        </table>
    <?php
        wp_die();
    }

    public function ttbp_update_booking()
    {
        parse_str($_POST['formData'], $data);
        $player = sanitize_text_field($data['ttbp_updated_player']);
        $status = sanitize_text_field($data['ttbp_payment_status']);
        $booking_id = sanitize_text_field($data['ttbp_bookingID']);
        if (!$booking_id) {
            wp_send_json_error(__('Something went wrong.', 'teetime-booking-pro'));
        }

        for ($i = 1; $i <= 4; $i++) {
            update_post_meta($booking_id, 'ttbp_payment_status_' . $i, sanitize_text_field(isset($data['ttbp_payment_status_' . $i]) ? $data['ttbp_payment_status_' . $i] : ''));
            update_post_meta($booking_id, 'ttbp_player_name_' . $i, sanitize_text_field(isset($data['ttbp_player_name_' . $i]) ? $data['ttbp_player_name_' . $i] : ''));
        }

        update_post_meta($booking_id, 'ttbp_players', $player);
        update_post_meta($booking_id, 'ttbp_payment_status', $status);

        TTBP_Database::update_slot($booking_id, null, null, $player);
        $email_term = get_term_by('term_id', get_post_meta($booking_id, 'ttbp_golf_course', true), 'golf_course');
        $email_data = [
            'name' => get_post_meta($booking_id, 'ttbp_name', true),
            'email' => get_post_meta($booking_id, 'ttbp_email', true),
            'phone' => get_post_meta($booking_id, 'ttbp_name', true),
            'date' => get_post_meta($booking_id, 'ttbp_date', true),
            'time' => get_post_meta($booking_id, 'ttbp_time', true),
            'players' => get_post_meta($booking_id, 'ttbp_players', true),
            'holes' => get_post_meta($booking_id, 'ttbp_holes', true),
            'golf_course' => $email_term->name,
            'total_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_total_amount', true)),
            'booking_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_booking_amount', true)),
            'due_amount' => TTBP__price((float) get_post_meta($booking_id, 'ttbp_total_amount', true) - (float) get_post_meta($booking_id, 'ttbp_booking_amount', true)),
            'support_email' => get_option('ttbp_email_from', ''),
        ];

        $email_sent = TTBP__send_email($email_data['email'], $email_data, 'update');
        error_log($email_sent);

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

        $email_data = [
            'name' => get_post_meta($booking_id, 'ttbp_name', true),
            'email' => get_post_meta($booking_id, 'ttbp_email', true),
            'phone' => get_post_meta($booking_id, 'ttbp_name', true),
            'date' => get_post_meta($booking_id, 'ttbp_date', true),
            'time' => get_post_meta($booking_id, 'ttbp_time', true),
            'players' => get_post_meta($booking_id, 'ttbp_players', true),
            'holes' => get_post_meta($booking_id, 'ttbp_holes', true),
            'golf_course' => $email_term->name,
            'total_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_total_amount', true)),
            'booking_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_booking_amount', true)),
            'due_amount' => TTBP__price((float) get_post_meta($booking_id, 'ttbp_total_amount', true) - (float) get_post_meta($booking_id, 'ttbp_booking_amount', true)),
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

        $title = $form['ttbp_name'] . ' - ' . $form['ttbp_date'] . ' @ ' . $form['ttbp_time'];

        $booking_id = wp_insert_post([
            'post_type'   => 'ttbp_booking',
            'post_title'  => sanitize_text_field($title),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($booking_id)) {
            wp_send_json_error(__('Something went wrong.', 'teetime-booking-pro'));
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
            'phone' => $form['ttbp_name'],
            'date' => $form['ttbp_date'],
            'time' => $form['ttbp_time'],
            'players' => $form['ttbp_players'],
            'holes' => $form['ttbp_holes'],
            'golf_course' => $courseName,
            'total_amount' => TTBP__price($form['ttbp_total_amount']),
            'booking_amount' => TTBP__price($form['ttbp_booking_amount']),
            'due_amount' => TTBP__price((float) $form['ttbp_total_amount'] - (float) $form['ttbp_booking_amount']),
            'support_email' => get_option('ttbp_email_from', ''),
        ];        

        $email_sent = TTBP__send_email($form['ttbp_email'], $email_data);
        error_log($email_sent);
        
        update_post_meta($booking_id, 'ttbp_email_sent', $email_sent);

        $form['ttbp_time'] = date('g:ia', strtotime($form['ttbp_time']));
        foreach ($form as $field_key => $field_value) {
            update_post_meta($booking_id, $field_key, sanitize_text_field($field_value));
        }

        wp_set_post_terms($booking_id, [$form['ttbp_golf_course']], 'golf_course');

        wp_send_json_success(__('Form Submitted successfully', 'teetime-booking-pro'));

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
        $total_pay_9 = get_option('ttbp_total_amount_9', 0);
        $total_pay_18 = get_option('ttbp_total_amount_18', 0);

        $available_slots = render_slots($startTime, $endTime, $duration);
        $filtered_slots = array_values(array_diff($available_slots, $dismiss));
        $lead_time = get_option('ttbp_lead_time');

        $pricing_rules = get_option('ttbp_custom_pricing') ? get_option('ttbp_custom_pricing') : [];
        $rule = [];
        $weekDay = date('N', strtotime($date));

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
                    $window_start_timestamp = strtotime($rule['window_start']);
                    $window_end_timestamp = strtotime($rule['window_end']);
                    if (($item_value >= $window_start_timestamp) && ($item_value <= $window_end_timestamp)) {
                        $booking_amount = $rule['booking_amount'];
                        $total_pay_9 = $rule['total_amount_9'];
                        $total_pay_18 = $rule['total_amount_18'];
                    } else {
                        $booking_amount = get_option('ttbp_booking_amount', 0);
                        $total_pay_9 = get_option('ttbp_total_amount_9', 0);
                        $total_pay_18 = get_option('ttbp_total_amount_18', 0);
                    }
                }
                if ($date_today === $date) {
                    if (($time_now < $item_value) && ($difference >= $lead_time)) {
                        echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18);
                    }
                } else {
                    echo $this->render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18);
                }
            }
        } else {
            echo __('No slots available', 'teetime-booking-pro');
        }
        wp_die();
    }

    private function render_form_booking_slot($item_key, $item_value, $holes, $golf_course, $player_count, $booking_amount, $total_pay_9, $total_pay_18)
    {
        ob_start();
    ?>
        <label for="ttbp_slot_<?php echo $item_key; ?>">
            <input type="radio" name="ttbp_time" value="<?php echo date('g:ia', $item_value) ?>" id="ttbp_slot_<?php echo $item_key; ?>" data-total_pay_9="<?php echo $total_pay_9; ?>" data-total_pay_18="<?php echo $total_pay_18; ?>" data-booking_amount="<?php echo $booking_amount; ?>">
            <div class="ttbp_slot">
                <div class="ttbp_slot_hole">
                    <?php echo $holes; ?>
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
                    <span><?php echo TTBP__price($holes == 9 ? $total_pay_9 : $total_pay_18); ?></span>
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

        if ($payment_method == 'offline') {
            $process_booking = true;
        } else {
            $amount = sanitize_text_field($form['ttbp_booking_amount']);
            $token = sanitize_text_field($form['ttbp_clover_token']);

            require_once TTBP_PATH . 'includes/class-clover-payment.php';

            $clover_instance = new TTBP_Clover_Pay();

            $charge = $clover_instance->create_charge((float)$amount * 100, $token);

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
        $title = sanitize_text_field($form['ttbp_name']) . ' - ' . sanitize_text_field($form['ttbp_date']) . ' @ ' . sanitize_text_field($form['ttbp_time']);

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

        $email_term = get_term_by('term_id', get_post_meta($booking_id, 'ttbp_golf_course', true), 'golf_course');

        $email_data = [
            'name' => get_post_meta($booking_id, 'ttbp_name', true),
            'email' => get_post_meta($booking_id, 'ttbp_email', true),
            'phone' => get_post_meta($booking_id, 'ttbp_name', true),
            'date' => get_post_meta($booking_id, 'ttbp_date', true),
            'time' => get_post_meta($booking_id, 'ttbp_time', true),
            'players' => get_post_meta($booking_id, 'ttbp_players', true),
            'holes' => get_post_meta($booking_id, 'ttbp_holes', true),
            'golf_course' => $email_term->name,
            'total_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_total_amount', true)),
            'booking_amount' => TTBP__price(get_post_meta($booking_id, 'ttbp_booking_amount', true)),
            'due_amount' => TTBP__price((float) get_post_meta($booking_id, 'ttbp_total_amount', true) - (float) get_post_meta($booking_id, 'ttbp_booking_amount', true)),
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
