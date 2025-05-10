<?php

if (!defined('ABSPATH')) exit;

class TTBP_Admin
{
    private $default_duration;
    private $default_start_morning;
    private $default_start_midday;
    private $default_start_evening;
    private $booking_amount;
    private $total_amount_9;
    private $total_amount_18;
    private $custom_slot_rules;
    private $custom_pricing;
    private $default_end;
    private $currency;
    private $currency_position;
    private $decimal_separator;
    private $thousand_separator;
    private $decimal_points;
    private $max_booking_window;
    private $leadtime;
    private $default_timezone;
    private $payment_notice;
    private $opt_in_label;

    public function __construct()
    {
        $this->default_duration = get_option('ttbp_default_duration');
        $this->default_start_morning = get_option('ttbp_default_start_morning');
        $this->default_start_midday = get_option('ttbp_default_start_midday');
        $this->default_start_evening = get_option('ttbp_default_start_evening');
        $this->default_end = get_option('ttbp_default_end');
        $this->booking_amount = get_option('ttbp_booking_amount');
        $this->total_amount_9 = get_option('ttbp_total_amount_9');
        $this->total_amount_18 = get_option('ttbp_total_amount_18');
        $this->custom_slot_rules = get_option('ttbp_custom_slot_rules') ? get_option('ttbp_custom_slot_rules') : [];
        $this->custom_pricing = get_option('ttbp_custom_pricing') ? get_option('ttbp_custom_pricing') : [];
        $this->currency = get_option('ttbp_currency', 'USD');
        $this->currency_position = get_option('ttbp_currency_position');
        $this->decimal_separator = get_option('ttbp_decimal_separator');
        $this->thousand_separator = get_option('ttbp_thousand_separator');
        $this->decimal_points = get_option('ttbp_decimals');
        $this->max_booking_window = get_option('ttbp_max_booking_window');
        $this->leadtime = get_option('ttbp_lead_time');
        $this->default_timezone = get_option('ttbp_default_timezone');
        $this->payment_notice = get_option('ttbp_payment_notice');
        $this->opt_in_label = get_option('ttbp_opt_in_label');

        add_filter('manage_ttbp_booking_posts_columns', [$this, 'custom_columns']);
        add_action('manage_ttbp_booking_posts_custom_column', [$this, 'render_columns'], 10, 2);
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post', [$this, 'save_booking_meta']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        // Adding settings page
        add_action('admin_menu', [$this, 'ttbp_menu_pages']);

        //Save settings 
        add_action('admin_post_ttbp_add_rule', [$this, 'handle_add_rule']);
        add_action('admin_post_ttbp_add_pricing', [$this, 'handle_add_pricing']);
        add_action('admin_post_ttbp_save_settings', [$this, 'ttbp_save_settings']);
        add_action('admin_post_ttbp_payment_methods', [$this, 'ttbp_payment_methods']);
        add_action('wp_ajax_ttbp_delete_rule', [$this, 'ttbp_delete_rule']);

        // Add user field in admin
        add_action('show_user_profile', [$this, 'add_staff_role_checkbox']);
        add_action('edit_user_profile', [$this, 'add_staff_role_checkbox']);
        add_action('edit_user_profile_update', [$this, 'save_staff_role_checkbox']);
        add_action('personal_options_update', [$this, 'save_staff_role_checkbox']);
        add_action('wp_trash_post', [$this, 'ttbp_on_post_trashed'], 10, 1);
    }

    public function ttbp_on_post_trashed($post_id)
    {
        // Get the post object
        $post = get_post($post_id);

        // Optional: Limit to certain post types
        if ($post->post_type !== 'ttbp_booking') {
            return;
        }
        TTBP_Database::delete_slot($post_id);
    }

    // === Add Custom User Field ===
    public function add_staff_role_checkbox($user)
    {
        $is_staff = get_user_meta($user->ID, 'ttbp_is_staff', true);
?>
        <h2><?php _e('Course Staff Settings', 'ttbp'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="ttbp_make_staff"><?php _e('Make user the coach', 'ttbp'); ?></label></th>
                <td>
                    <input type="checkbox" name="ttbp_make_staff" id="ttbp_make_staff" value="1"
                        <?php checked($is_staff, 1, true); ?> />
                    <p class="description"><?php _e('Check this to make the user a course staff (coach).', 'ttbp'); ?></p>
                </td>
            </tr>
        </table>
    <?php
    }

    public function save_staff_role_checkbox($user_id)
    {
        // Only allow users who can edit other users
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        update_user_meta($user_id, 'ttbp_test', $_POST['ttbp_make_staff']);
        // If checked: add role
        if (isset($_POST['ttbp_make_staff'])) {
            update_user_meta($user_id, 'ttbp_is_staff', $_POST['ttbp_make_staff']);
        } else {
            update_user_meta($user_id, 'ttbp_is_staff', '');
        }
    }

    public function ttbp_delete_rule()
    {
        $key = sanitize_text_field($_POST['key']);

        $rules = $this->custom_slot_rules;

        if (isset($rules[$key])) {
            unset($rules[$key]);
            update_option('ttbp_custom_slot_rules', $rules);
        }

        wp_die();
    }

    public function admin_scripts()
    {
        wp_enqueue_style('ttbp-admin-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), time(), 'all');
        wp_enqueue_style('ttbp-admin', TTBP_URL . 'css/admin.css', array(), time(), 'all');
        wp_enqueue_script('ttbp-admin', TTBP_URL . 'js/admin.js', array(), time(), true);
        wp_localize_script('ttbp-admin', 'ttbpLocal', array(
            'ajax' => admin_url('admin-ajax.php'),
        ));
    }

    public function ttbp_payment_methods()
    {
        $form_data = !empty($_POST) ? $_POST : [];

        unset($form_data['ttbp_payment_nonce']);
        unset($form_data['_wp_http_referer']);
        unset($form_data['action']);
        unset($form_data['submit']);

        if (empty($form_data)) {
            wp_die(__('Form data not available', 'teetime-booking-pro'));
        }

        // Check permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action', 'teetime-booking-pro'));
        }

        // Verify nonce
        if (!isset($_POST['ttbp_payment_nonce']) || !wp_verify_nonce($_POST['ttbp_payment_nonce'], 'ttbp_payment_settings')) {
            wp_redirect(add_query_arg('settings-updated', 'false', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_payment_methods')));
            exit;
        }

        foreach ($form_data as $key => $value) {
            update_option($key, sanitize_text_field($value));
        }

        // Redirect back with success message
        wp_redirect(add_query_arg('settings-updated', 'true', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_payment_methods')));
        exit;
    }

    public function ttbp_save_settings()
    {
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'teetime-booking-pro'));
        }

        // Check nonce
        if (!isset($_POST['ttbp_settings_nonce']) || !wp_verify_nonce($_POST['ttbp_settings_nonce'], 'ttbp_save_settings')) {
            wp_redirect(add_query_arg('settings-updated', 'false', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_settings')));
            exit;
        }

        // Sanitize and save option
        $start_morning = isset($_POST['ttbp_default_start_morning']) ? sanitize_text_field($_POST['ttbp_default_start_morning']) : '';
        $start_midday = isset($_POST['ttbp_default_start_midday']) ? sanitize_text_field($_POST['ttbp_default_start_midday']) : '';
        $start_evening = isset($_POST['ttbp_default_start_evening']) ? sanitize_text_field($_POST['ttbp_default_start_evening']) : '';
        $end = isset($_POST['ttbp_default_end']) ? sanitize_text_field($_POST['ttbp_default_end']) : '';
        $duration = isset($_POST['ttbp_default_duration']) ? sanitize_text_field($_POST['ttbp_default_duration']) : '';
        $booking_amount = isset($_POST['ttbp_booking_amount']) ? sanitize_text_field($_POST['ttbp_booking_amount']) : '';
        $total_amount_9 = isset($_POST['ttbp_total_amount_9']) ? sanitize_text_field($_POST['ttbp_total_amount_9']) : '';
        $total_amount_18 = isset($_POST['ttbp_total_amount_18']) ? sanitize_text_field($_POST['ttbp_total_amount_18']) : '';
        $ttbp_currency = isset($_POST['ttbp_currency']) ? sanitize_text_field($_POST['ttbp_currency']) : '';
        $currency_position = isset($_POST['ttbp_currency_position']) ? sanitize_text_field($_POST['ttbp_currency_position']) : '';
        $decimal_separator = isset($_POST['ttbp_decimal_separator']) ? sanitize_text_field($_POST['ttbp_decimal_separator']) : '';
        $thousand_separator = isset($_POST['ttbp_thousand_separator']) ? sanitize_text_field($_POST['ttbp_thousand_separator']) : '';
        $decimal_points = isset($_POST['ttbp_decimals']) ? sanitize_text_field($_POST['ttbp_decimals']) : '';
        $max_booking_window = isset($_POST['ttbp_max_booking_window']) ? sanitize_text_field($_POST['ttbp_max_booking_window']) : '';
        $leadtime = isset($_POST['ttbp_lead_time']) ? sanitize_text_field($_POST['ttbp_lead_time']) : '';
        $default_timezone = isset($_POST['ttbp_default_timezone']) ? sanitize_text_field($_POST['ttbp_default_timezone']) : '';
        $payment_notice = isset($_POST['ttbp_payment_notice']) ? wp_kses_post($_POST['ttbp_payment_notice']) : '';
        $opt_in_label = isset($_POST['ttbp_opt_in_label']) ? wp_kses_post($_POST['ttbp_opt_in_label']) : '';

        update_option('ttbp_default_start_morning', $start_morning);
        update_option('ttbp_default_start_midday', $start_midday);
        update_option('ttbp_default_start_evening', $start_evening);
        update_option('ttbp_default_end', $end);
        update_option('ttbp_default_duration', $duration);
        update_option('ttbp_booking_amount', $booking_amount);
        update_option('ttbp_total_amount_9', $total_amount_9);
        update_option('ttbp_total_amount_18', $total_amount_18);
        update_option('ttbp_currency', $ttbp_currency);
        update_option('ttbp_currency_position', $currency_position);
        update_option('ttbp_decimal_separator', $decimal_separator);
        update_option('ttbp_thousand_separator', $thousand_separator);
        update_option('ttbp_decimals', $decimal_points);
        update_option('ttbp_max_booking_window', $max_booking_window);
        update_option('ttbp_lead_time', $leadtime);
        update_option('ttbp_default_timezone', $default_timezone);
        update_option('ttbp_payment_notice', $payment_notice);
        update_option('ttbp_opt_in_label', $opt_in_label);

        // Redirect back with success message
        wp_redirect(add_query_arg('settings-updated', 'true', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_settings')));
        exit;
    }

    public function handle_add_rule()
    {
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'teetime-booking-pro'));
        }

        // Check nonce
        if (!isset($_POST['ttbp_add_rule_nonce']) || !wp_verify_nonce($_POST['ttbp_add_rule_nonce'], 'ttbp_add_rule')) {
            wp_redirect(add_query_arg('settings-updated', 'false', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_settings')));
            exit;
        }

        // Sanitize and save option
        $start_morning = isset($_POST['custom_start_morning']) ? sanitize_text_field($_POST['custom_start_morning']) : '';
        $start_midday = isset($_POST['custom_start_midday']) ? sanitize_text_field($_POST['custom_start_midday']) : '';
        $start_evening = isset($_POST['custom_start_evening']) ? sanitize_text_field($_POST['custom_start_evening']) : '';
        $end = isset($_POST['custom_end']) ? sanitize_text_field($_POST['custom_end']) : '';
        $duration = isset($_POST['custom_duration']) ? sanitize_text_field($_POST['custom_duration']) : '';
        $date = isset($_POST['custom_date']) ? date('Y-m-d', strtotime(sanitize_text_field($_POST['custom_date']))) : '';

        $rules = $this->custom_slot_rules ? $this->custom_slot_rules : [];

        $rules[$date] = array(
            'start_morning' => $start_morning,
            'start_midday' => $start_midday,
            'start_evening' => $start_evening,
            'end' => $end,
            'duration' => $duration,
        );

        update_option('ttbp_custom_slot_rules', $rules);

        // Redirect back with success message
        wp_redirect(add_query_arg('settings-updated', 'true', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_settings')));
        exit;
    }

    public function handle_add_pricing()
    {
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'teetime-booking-pro'));
        }

        // Check nonce
        if (!isset($_POST['ttbp_add_pricing_nonce']) || !wp_verify_nonce($_POST['ttbp_add_pricing_nonce'], 'ttbp_add_pricing')) {
            wp_redirect(add_query_arg('settings-updated', 'false', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_settings')));
            exit;
        }

        // Sanitize and save option
        $custom_weekday = isset($_POST['custom_weekday']) ? sanitize_text_field($_POST['custom_weekday']) : '';
        $custom_booking_amount = isset($_POST['custom_booking_amount']) ? sanitize_text_field($_POST['custom_booking_amount']) : '';
        $custom_window_start = isset($_POST['custom_window_start']) ? sanitize_text_field($_POST['custom_window_start']) : '';
        $custom_window_end = isset($_POST['custom_window_end']) ? sanitize_text_field($_POST['custom_window_end']) : '';
        $custom_total_amount_9 = isset($_POST['custom_total_amount_9']) ? sanitize_text_field($_POST['custom_total_amount_9']) : '';
        $custom_total_amount_18 = isset($_POST['custom_total_amount_18']) ? sanitize_text_field($_POST['custom_total_amount_18']) : '';

        $rules = $this->custom_pricing ? $this->custom_pricing : [];

        $rules[$custom_weekday] = array(
            'weekday' => $custom_weekday,
            'window_start' => $custom_window_start,
            'window_end' => $custom_window_end,
            'booking_amount' => $custom_booking_amount,
            'total_amount_9' => $custom_total_amount_9,
            'total_amount_18' => $custom_total_amount_18,
        );

        update_option('ttbp_custom_pricing', $rules);

        // Redirect back with success message
        wp_redirect(add_query_arg('settings-updated', 'true', admin_url('edit.php?post_type=ttbp_booking&page=ttbp_settings')));
        exit;
    }

    public function ttbp_menu_pages()
    {
        add_submenu_page(
            'edit.php?post_type=ttbp_booking',
            __('Settings', 'teetime-booking-pro'),
            __('Settings', 'teetime-booking-pro'),
            'manage_options',
            'ttbp_settings',
            [$this, 'ttbp_settings_renderer']
        );

        add_submenu_page(
            'edit.php?post_type=ttbp_booking',
            __('Payment Methods', 'teetime-booking-pro'),
            __('Payment Methods', 'teetime-booking-pro'),
            'manage_options',
            'ttbp_payment_methods',
            [$this, 'ttbp_payment_methods_renderer']
        );

        add_submenu_page(
            'edit.php?post_type=ttbp_booking', // Parent slug
            'Booking Email Settings', // Page title
            'Booking Email', // Menu title
            'manage_options', // Capability
            'ttbp-email-settings', // Menu slug
            [$this, 'ttbp_render_email_settings_page'] // Callback
        );
    }

    public function ttbp_render_email_settings_page()
    {
        require_once(TTBP_PATH . 'templates/admin_menu/email_settings_page.php');
    }

    public function ttbp_settings_renderer()
    {
        require_once(TTBP_PATH . 'templates/admin_menu/settings.php');
    }

    public function ttbp_payment_methods_renderer()
    {
        require_once(TTBP_PATH . 'templates/admin_menu/payment_methods.php');
    }

    // Add custom admin columns
    public function custom_columns($columns)
    {
        unset($columns['date']);

        return array_merge($columns, [
            'ttbp_date'   => __('Date', 'teetime-booking-pro'),
            'ttbp_time'   => __('Time', 'teetime-booking-pro'),
            'ttbp_players' => __('Players', 'teetime-booking-pro'),
            'ttbp_name'   => __('Name', 'teetime-booking-pro'),
            'ttbp_email'  => __('Email', 'teetime-booking-pro')
        ]);
    }

    // Populate admin columns
    public function render_columns($column, $post_id)
    {
        $value = get_post_meta($post_id, $column, true);
        echo esc_html($value);
    }

    // Add meta box to booking edit screen
    public function register_meta_box()
    {
        add_meta_box(
            'ttbp_booking_meta',
            __('Booking Details', 'teetime-booking-pro'),
            [$this, 'meta_box_callback'],
            'ttbp_booking',
            'normal',
            'default'
        );
    }

    // Meta box UI
    public function meta_box_callback($post)
    {
        wp_nonce_field('ttbp_booking_meta_nonce', 'ttbp_booking_nonce');

        $customer_fields = [
            'ttbp_name' => __('Name', 'teetime-booking-pro'),
            'ttbp_email' => __('Email', 'teetime-booking-pro'),
            'ttbp_phone' => __('Phone Number', 'teetime-booking-pro'),
            'ttbp_payment_method' => __('Payment Method', 'teetime-booking-pro'),
            'ttbp_marketing' => __('Marketing Opt-in', 'teetime-booking-pro'),
        ];

        $payment_method = get_post_meta($post->ID, 'ttbp_payment_method', true);
        $payment_status = get_post_meta($post->ID, 'ttbp_payment_status', true) ? get_post_meta($post->ID, 'ttbp_payment_status', true) : 'due';
        $total = get_post_meta($post->ID, 'ttbp_total_amount', true) ? get_post_meta($post->ID, 'ttbp_total_amount', true) : 0;
        $deposit = $payment_method == 'offline' ? 0 : get_post_meta($post->ID, 'ttbp_booking_amount', true);
        $due = (float) $total - (float) $deposit;

    ?>
        <table class="ttbp_customer_table" style="max-width: 450px; width: 100%">
            <tbody>
                <tr>
                    <td>
                        <table style="width: 100%">
                            <tbody>
                                <tr>
                                    <td colspan="2">
                                        <h4><?php _e('Customer Information', 'teetime-booking-pro'); ?></h4>
                                    </td>
                                </tr>
                                <?php foreach ($customer_fields as $field_key => $field_value): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $field_value; ?>: </strong>
                                        </td>
                                        <td>
                                            <?php echo get_post_meta($post->ID, $field_key, true); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php
                                $players = get_post_meta($post->ID, 'ttbp_players', true);
                                for ($p = 1; $p <= (int) $players; $p++) {
                                    $player_stat = get_post_meta($post->ID, 'ttbp_payment_status_' . $p, true) ? get_post_meta($post->ID, 'ttbp_payment_status_' . $p, true) : 'due';
                                    $player_name = get_post_meta($post->ID, 'ttbp_player_name_' . $p, true) ? get_post_meta($post->ID, 'ttbp_player_name_' . $p, true) : 'Player - ' . $p;
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo __('Payment (' . $player_name . ')', 'teetime-booking-pro'); ?>: </strong>
                                        </td>
                                        <td>
                                            <span class="payment_status <?php echo $player_stat; ?>"><?php echo ucwords($player_stat); ?></span>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>

                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php
        $payment_data = get_post_meta($post->ID, 'ttbp_payment_data', true);
        if ($payment_data):
        ?>
            <div class="payment-transaction-details">
                <h3 class="hndle">Payment Transaction Details</h3>

                <div class="payment-details-container">
                    <div class="payment-column">
                        <h4>Transaction Information</h4>
                        <table class="payment-info-table">
                            <tr>
                                <th>Transaction ID:</th>
                                <td><?php echo $payment_data['id']; ?></td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td><?php echo TTBP__price((int) $payment_data['amount'] / 100); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><span class="status-succeeded"><?php echo ucwords($payment_data['status']); ?></span></td>
                            </tr>
                            <tr>
                                <th>Ref Number:</th>
                                <td><?php echo $payment_data['ref_num']; ?></td>
                            </tr>
                            <tr>
                                <th>Auth Code:</th>
                                <td><?php echo $payment_data['auth_code']; ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="payment-column">
                        <h4>Payment Method</h4>
                        <div class="card-details">
                            <div class="card-icon visa">
                                <img src="<?php echo TTBP_URL . 'images/card.png' ?>" alt="">
                            </div>
                            <div class="card-info">
                                <div class="card-number"><?php echo $payment_data['source']['brand']; ?> •••• <?php echo $payment_data['source']['last4']; ?></div>
                                <div class="card-expiry">Expires <?php echo $payment_data['source']['exp_month'] . '/' . $payment_data['source']['exp_year']; ?></div>
                            </div>
                        </div>
                        <table class="payment-info-table">
                            <tr>
                                <th>ZIP Check:</th>
                                <td><span class="check-passed"><?php echo strtoupper($payment_data['source']['address_zip_check']); ?></span></td>
                            </tr>
                            <tr>
                                <th>Network Status:</th>
                                <td><?php echo ucwords(str_replace('_', ' ', $payment_data['outcome']['network_status'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <table class="booking_detail">
            <thead>
                <th>Golf Course</th>
                <th>Date</th>
                <th>Slot</th>
                <th>Players</th>
                <th>Holes</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php
                        $term = get_term_by('id', get_post_meta($post->ID, 'ttbp_golf_course', true), 'golf_course');
                        if (!empty($term)) {
                            echo $term->name;
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo date('F j, Y', strtotime(get_post_meta($post->ID, 'ttbp_date', true))); ?>
                    </td>
                    <td>
                        <?php echo date('g:i A', strtotime(get_post_meta($post->ID, 'ttbp_time', true))); ?>
                    </td>
                    <td>
                        <?php echo get_post_meta($post->ID, 'ttbp_players', true); ?>
                    </td>
                    <td>
                        <?php echo get_post_meta($post->ID, 'ttbp_holes', true); ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3"></th>
                    <td style="font-weight: 500; color: #7a7a7a">Total Payment</td>
                    <td style="text-align: right;font-weight: 500; color: #7a7a7a"><?php echo TTBP__price($total); ?></td>
                </tr>
                <tr>
                    <th colspan="3"></th>
                    <td style="font-weight: 500; color: #7a7a7a">Booking Deposit</td>
                    <td style="text-align: right;font-weight: 500; color: #7a7a7a">
                        <?php echo TTBP__price($deposit); ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="3"></th>
                    <td style="border-top: 2px solid #ddd;font-weight: 500; color: #000">Total Due at Course</td>
                    <td style="border-top: 2px solid #ddd; text-align: right;font-weight: 500; color: #000">
                        <?php echo TTBP__price($due); ?>
                    </td>
                </tr>
                <?php if ($payment_status == 'paid'): ?>
                    <tr>
                        <th colspan="3"></th>
                        <td style="font-weight: 500; color: #000">Payment Collected</td>
                        <td style="text-align: right;font-weight: 500; color: #000">
                            <?php echo TTBP__price($due); ?>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="3"></th>
                        <td style="border-top: 2px solid #ddd;font-weight: 500; color: #000">Remaining Payment</td>
                        <td style="border-top: 2px solid #ddd; text-align: right;font-weight: 500; color: #000">
                            <?php echo TTBP__price(0); ?>
                        </td>
                    </tr>
                <?php endif ?>
            </tfoot>
        </table>
<?php
    }

    // Save meta when post is saved
    public function save_booking_meta($post_id)
    {
        if (!isset($_POST['ttbp_booking_nonce']) || !wp_verify_nonce($_POST['ttbp_booking_nonce'], 'ttbp_booking_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (get_post_type($post_id) !== 'ttbp_booking') return;

        $fields = [
            'ttbp_date',
            'ttbp_time',
            'ttbp_players',
            'ttbp_holes',
            'ttbp_name',
            'ttbp_email',
            'ttbp_phone',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}
