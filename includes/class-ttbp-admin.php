<?php

if (!defined('ABSPATH')) exit;

class TTBP_Admin
{
    private $default_duration;
    private $default_start_morning;
    private $default_start_midday;
    private $default_start_evening;
    private $booking_amount;
    private $total_amount_9_walking;
    private $total_amount_9_cart;
    private $total_amount_18_walking;
    private $total_amount_18_cart;
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
    private $adv_payment_discount;
    private $pia_label;
    private $cart_tax_rate;
    private $taxable_amount;

    public function __construct()
    {
        $this->default_duration = get_option('ttbp_default_duration');
        $this->default_start_morning = get_option('ttbp_default_start_morning');
        $this->default_start_midday = get_option('ttbp_default_start_midday');
        $this->default_start_evening = get_option('ttbp_default_start_evening');
        $this->default_end = get_option('ttbp_default_end');
        $this->booking_amount = get_option('ttbp_booking_amount');
        $this->total_amount_9_walking = get_option('ttbp_total_amount_9_walking');
        $this->total_amount_9_cart = get_option('ttbp_total_amount_9_cart');
        $this->total_amount_18_walking = get_option('ttbp_total_amount_18_walking');
        $this->total_amount_18_cart = get_option('ttbp_total_amount_18_cart');
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
        $this->adv_payment_discount = get_option('ttbp_adv_payment_discount', 0);
        $this->pia_label = get_option('ttbp_pia_label');
        $this->cart_tax_rate = get_option('ttbp_cart_tax_rate');
        $this->taxable_amount = get_option('ttbp_taxable_amount');

        add_filter('manage_ttbp_booking_posts_columns', [$this, 'custom_columns']);
        add_action('manage_ttbp_booking_posts_custom_column', [$this, 'render_columns'], 10, 2);
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post_ttbp_booking', [$this, 'save_booking_meta']);
        add_action('save_post_ttbp_coupon', [$this, 'save_post_coupon']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        // Adding settings page
        add_action('admin_menu', [$this, 'ttbp_menu_pages']);

        //Save settings 
        add_action('admin_post_ttbp_add_rule', [$this, 'handle_add_rule']);
        add_action('admin_post_ttbp_add_pricing', [$this, 'handle_add_pricing']);
        add_action('admin_post_ttbp_save_settings', [$this, 'ttbp_save_settings']);
        add_action('admin_post_ttbp_payment_methods', [$this, 'ttbp_payment_methods']);
        add_action('wp_ajax_ttbp_delete_rule', [$this, 'ttbp_delete_rule']);
        add_action('wp_ajax_ttbp_delete_pricing', [$this, 'ttbp_delete_pricing']);
        add_action('wp_ajax_ttbp_edit_pricing', [$this, 'ttbp_edit_pricing']);
        add_action('wp_ajax_ttbp_clone_rules', [$this, 'ttbp_clone_rules']);
        add_action('wp_ajax_ttbp_reset_settings', [$this, 'ttbp_reset_settings']);

        // Add user field in admin
        add_action('show_user_profile', [$this, 'add_staff_role_checkbox']);
        add_action('edit_user_profile', [$this, 'add_staff_role_checkbox']);
        add_action('edit_user_profile_update', [$this, 'save_staff_role_checkbox']);
        add_action('personal_options_update', [$this, 'save_staff_role_checkbox']);
        add_action('wp_trash_post', [$this, 'ttbp_on_post_trashed'], 10, 1);
    }

    public function ttbp_reset_settings()
    {
        update_option('ttbp_default_duration', 10);
        update_option('ttbp_default_start_morning', '05:00');
        update_option('ttbp_default_start_midday', '12:00');
        update_option('ttbp_default_start_evening', '17:00');
        update_option('ttbp_default_end', '23:59');
        update_option('ttbp_booking_amount', 0);
        update_option('ttbp_total_amount_9_walking', 0);
        update_option('ttbp_total_amount_9_cart', 0);
        update_option('ttbp_total_amount_18_walking', 0);
        update_option('ttbp_total_amount_18_cart', 0);
        update_option('ttbp_custom_slot_rules', []);
        update_option('ttbp_custom_pricing', []);
        update_option('ttbp_payment_notice', '');
        update_option('ttbp_adv_payment_discount', 0);
        update_option('ttbp_opt_in_label', 'Opt-in for promotions and latest news via email and text.');
        update_option('ttbp_payment_notice', 'You have paid {booking_amount} but the total amount is {total_amount}. Pay the {due_amount} on the location.');
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($weekdays as $weekday) {
            update_option('ttbp_restrict_start_' . $weekday, '');
            update_option('ttbp_restrict_end_' . $weekday, '');
            update_option('ttbp_restrict_holes_' . $weekday, 9);
            update_option('ttbp_restrict_ride_' . $weekday, 'walking');
            update_option('ttbp_advPay_restrict_start_' . $weekday, '');
            update_option('ttbp_advPay_restrict_end_' . $weekday, '');
            update_option('ttbp_advPay_rule_' . $weekday, '');
        }
        wp_die();
    }

    public function ttbp_edit_pricing()
    {
        $weekday = sanitize_text_field($_POST['weekday']);
        $rules = $this->custom_pricing;
        $weekdayRule = isset($rules[$weekday]) ? $rules[$weekday] : [];

        $booking_amount = $weekdayRule['booking_amount'];
        $window_start = $weekdayRule['window_start'];
        $window_end = $weekdayRule['window_end'];
        $total_amount_9_walking = $weekdayRule['total_amount_9_walking'];
        $total_amount_9_cart = $weekdayRule['total_amount_9_cart'];
        $total_amount_18_walking = $weekdayRule['total_amount_18_walking'];
        $total_amount_18_cart = $weekdayRule['total_amount_18_cart'];

        if (!empty($booking_amount)) {
            foreach ($booking_amount as $key => $value) {
?>
                <tr>
                    <td>
                        <input type="checkbox" name="ttbp_select_row[]" value="<?php echo $key; ?>" />
                    </td>
                    <td>
                        <div class="ttbp_form_group">
                            <input type="number" step="0.01" min="1" name="custom_booking_amount[]" value="<?php echo $booking_amount[$key]; ?>" class="ttbp_input" required />
                        </div>
                    </td>
                    <td>
                        <div class="ttbp_form_group">
                            <input type="time" name="custom_window_start[]" value="<?php echo $window_start[$key]; ?>" class="ttbp_input" required />
                        </div>
                    </td>
                    <td>
                        <div class="ttbp_form_group">
                            <input type="time" name="custom_window_end[]" value="<?php echo $window_end[$key]; ?>" class="ttbp_input" required />
                        </div>
                    </td>
                    <td>
                        <div class="ttbp_form_group">
                            <input type="number" step="0.01" min="1" name="custom_total_amount_9_walking[]" value="<?php echo $total_amount_9_walking[$key]; ?>" class="ttbp_input" required />
                        </div>
                    </td>
                    <td>
                        <div class="ttbp_form_group">
                            <input type="number" step="0.01" min="1" name="custom_total_amount_9_cart[]" value="<?php echo $total_amount_9_cart[$key]; ?>" class="ttbp_input" required />
                        </div>
                    </td>
                    <td>
                        <div class="ttbp_form_group">
                            <input type="number" step="0.01" min="1" name="custom_total_amount_18_walking[]" value="<?php echo $total_amount_18_walking[$key]; ?>" class="ttbp_input" required />
                        </div>
                    </td>
                    <td>
                        <div class="ttbp_form_group">
                            <input type="number" step="0.01" min="1" name="custom_total_amount_18_cart[]" value="<?php echo $total_amount_18_cart[$key]; ?>" class="ttbp_input" required />
                        </div>
                    </td>
                    <td>
                        <button type="button" class="delete-row ttbp_danger ttbp_button">Delete</button>
                    </td>
                </tr>
            <?php
            }
        } else {
            ?>
            <tr>
                <td>
                    <input type="checkbox" name="ttbp_select_row[]" />
                </td>
                <td>
                    <div class="ttbp_form_group">
                        <input type="number" step="0.01" min="1" name="custom_booking_amount[]" id="custom_booking_amount" class="ttbp_input" required />
                    </div>
                </td>
                <td>
                    <div class="ttbp_form_group">
                        <input type="time" name="custom_window_start[]" id="custom_window_start" class="ttbp_input" required />
                    </div>
                </td>
                <td>
                    <div class="ttbp_form_group">
                        <input type="time" name="custom_window_end[]" id="custom_window_end" class="ttbp_input" required />
                    </div>
                </td>
                <td>
                    <div class="ttbp_form_group">
                        <input type="number" step="0.01" min="1" name="custom_total_amount_9_walking[]" id="custom_total_amount_9_walking" class="ttbp_input" required />
                    </div>
                </td>
                <td>
                    <div class="ttbp_form_group">
                        <input type="number" step="0.01" min="1" name="custom_total_amount_9_cart[]" id="custom_total_amount_9_cart" class="ttbp_input" required />
                    </div>
                </td>
                <td>
                    <div class="ttbp_form_group">
                        <input type="number" step="0.01" min="1" name="custom_total_amount_18_walking[]" id="custom_total_amount_18_walking" class="ttbp_input" required />
                    </div>
                </td>
                <td>
                    <div class="ttbp_form_group">
                        <input type="number" step="0.01" min="1" name="custom_total_amount_18_cart[]" id="custom_total_amount_18_cart" class="ttbp_input" required />
                    </div>
                </td>
                <td>
                    <button type="button" class="delete-row ttbp_danger ttbp_button">Delete</button>
                </td>
            </tr>
        <?php
        }

        wp_die();
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

    public function ttbp_delete_pricing()
    {
        $key = sanitize_text_field($_POST['key']);
        $rules = $this->custom_pricing;

        if (isset($rules[$key])) {
            unset($rules[$key]);
            update_option('ttbp_custom_pricing', $rules);
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
        $total_amount_9_walking = isset($_POST['ttbp_total_amount_9_walking']) ? sanitize_text_field($_POST['ttbp_total_amount_9_walking']) : '';
        $total_amount_9_cart = isset($_POST['ttbp_total_amount_9_cart']) ? sanitize_text_field($_POST['ttbp_total_amount_9_cart']) : '';
        $total_amount_18_walking = isset($_POST['ttbp_total_amount_18_walking']) ? sanitize_text_field($_POST['ttbp_total_amount_18_walking']) : '';
        $total_amount_18_cart = isset($_POST['ttbp_total_amount_18_cart']) ? sanitize_text_field($_POST['ttbp_total_amount_18_cart']) : '';
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
        $adv_payment_discount = isset($_POST['ttbp_adv_payment_discount']) ? wp_kses_post($_POST['ttbp_adv_payment_discount']) : '';
        $pia_label = isset($_POST['ttbp_pia_label']) ? wp_kses_post($_POST['ttbp_pia_label']) : '';
        $cart_tax_rate = isset($_POST['ttbp_cart_tax_rate']) ? sanitize_text_field($_POST['ttbp_cart_tax_rate']) : '';
        $taxable_amount = isset($_POST['ttbp_taxable_amount']) ? sanitize_text_field($_POST['ttbp_taxable_amount']) : '';

        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($weekdays as $weekday) {
            if (isset($_POST['ttbp_restrict_start_' . $weekday])) {
                update_option('ttbp_restrict_start_' . $weekday, sanitize_text_field($_POST['ttbp_restrict_start_' . $weekday]));
            }
            if (isset($_POST['ttbp_restrict_end_' . $weekday])) {
                update_option('ttbp_restrict_end_' . $weekday, sanitize_text_field($_POST['ttbp_restrict_end_' . $weekday]));
            }
            if (isset($_POST['ttbp_restrict_holes_' . $weekday])) {
                update_option('ttbp_restrict_holes_' . $weekday, sanitize_text_field($_POST['ttbp_restrict_holes_' . $weekday]));
            }
            if (isset($_POST['ttbp_restrict_ride_' . $weekday])) {
                update_option('ttbp_restrict_ride_' . $weekday, sanitize_text_field($_POST['ttbp_restrict_ride_' . $weekday]));
            }
            if (isset($_POST['ttbp_advPay_restrict_start_' . $weekday])) {
                update_option('ttbp_advPay_restrict_start_' . $weekday, sanitize_text_field($_POST['ttbp_advPay_restrict_start_' . $weekday]));
            }
            if (isset($_POST['ttbp_advPay_restrict_end_' . $weekday])) {
                update_option('ttbp_advPay_restrict_end_' . $weekday, sanitize_text_field($_POST['ttbp_advPay_restrict_end_' . $weekday]));
            }
            if (isset($_POST['ttbp_advPay_rule_' . $weekday])) {
                update_option('ttbp_advPay_rule_' . $weekday, sanitize_text_field($_POST['ttbp_advPay_rule_' . $weekday]));
            }
        }

        update_option('ttbp_default_start_morning', $start_morning);
        update_option('ttbp_default_start_midday', $start_midday);
        update_option('ttbp_default_start_evening', $start_evening);
        update_option('ttbp_default_end', $end);
        update_option('ttbp_default_duration', $duration);
        update_option('ttbp_booking_amount', $booking_amount);
        update_option('ttbp_total_amount_9_walking', $total_amount_9_walking);
        update_option('ttbp_total_amount_9_cart', $total_amount_9_cart);
        update_option('ttbp_total_amount_18_walking', $total_amount_18_walking);
        update_option('ttbp_total_amount_18_cart', $total_amount_18_cart);
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
        update_option('ttbp_adv_payment_discount', $adv_payment_discount);
        update_option('ttbp_pia_label', $pia_label);
        update_option('ttbp_cart_tax_rate', $cart_tax_rate);
        update_option('ttbp_taxable_amount', $taxable_amount);

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

    public function ttbp_clone_rules()
    {
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'teetime-booking-pro'));
        }
        parse_str($_POST['formData'], $form);

        $selected_row = $form['ttbp_select_row'];
        $custom_booking_amount = $form['custom_booking_amount'];
        $custom_window_start = $form['custom_window_start'];
        $custom_window_end = $form['custom_window_end'];
        $custom_total_amount_9_walking = $form['custom_total_amount_9_walking'];
        $custom_total_amount_9_cart = $form['custom_total_amount_9_cart'];
        $custom_total_amount_18_walking = $form['custom_total_amount_18_walking'];
        $custom_total_amount_18_cart = $form['custom_total_amount_18_cart'];

        $weekday = sanitize_text_field($form['ttbp_clone_day']);

        if (empty($selected_row)) wp_die();

        $rules = $this->custom_pricing ? $this->custom_pricing : [];

        foreach ($selected_row as $key => $value) {
            $rules[$weekday]['weekday'] = $weekday;
            $rules[$weekday]['window_start'][] = $custom_window_start[$value];
            $rules[$weekday]['window_end'][] = $custom_window_end[$value];
            $rules[$weekday]['booking_amount'][] = $custom_booking_amount[$value];
            $rules[$weekday]['total_amount_9_walking'][] = $custom_total_amount_9_walking[$value];
            $rules[$weekday]['total_amount_9_cart'][] = $custom_total_amount_9_cart[$value];
            $rules[$weekday]['total_amount_18_walking'][] = $custom_total_amount_18_walking[$value];
            $rules[$weekday]['total_amount_18_cart'][] = $custom_total_amount_18_cart[$value];
        }

        update_option('ttbp_custom_pricing', $rules);
        wp_send_json_success(array(
            'day' => $weekday
        ));
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
        $custom_booking_amount = isset($_POST['custom_booking_amount']) ? $_POST['custom_booking_amount'] : [];
        $custom_window_start = isset($_POST['custom_window_start']) ? $_POST['custom_window_start'] : [];
        $custom_window_end = isset($_POST['custom_window_end']) ? $_POST['custom_window_end'] : [];
        $custom_total_amount_9_walking = isset($_POST['custom_total_amount_9_walking']) ? $_POST['custom_total_amount_9_walking'] : [];
        $custom_total_amount_9_cart = isset($_POST['custom_total_amount_9_cart']) ? $_POST['custom_total_amount_9_cart'] : [];
        $custom_total_amount_18_walking = isset($_POST['custom_total_amount_18_walking']) ? $_POST['custom_total_amount_18_walking'] : [];
        $custom_total_amount_18_cart = isset($_POST['custom_total_amount_18_cart']) ? $_POST['custom_total_amount_18_cart'] : [];

        $rules = $this->custom_pricing ? $this->custom_pricing : [];

        $rules[$custom_weekday] = array(
            'weekday' => $custom_weekday,
            'window_start' => $custom_window_start,
            'window_end' => $custom_window_end,
            'booking_amount' => $custom_booking_amount,
            'total_amount_9_walking' => $custom_total_amount_9_walking,
            'total_amount_9_cart' => $custom_total_amount_9_cart,
            'total_amount_18_walking' => $custom_total_amount_18_walking,
            'total_amount_18_cart' => $custom_total_amount_18_cart,
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
            __('Coupon', 'teetime-booking-pro'),
            __('Coupon', 'teetime-booking-pro'),
            'manage_options',
            'edit.php?post_type=ttbp_coupon',
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

        add_meta_box(
            'ttbp_coupon_meta',
            __('Settings', 'teetime-booking-pro'),
            [$this, 'coupon_fields'],
            'ttbp_coupon',
            'normal',
            'default'
        );
    }

    public function coupon_fields($post)
    {
        $coupon_code     = get_post_meta($post->ID, '_ttbp_coupon_code', true);
        $use_limit       = get_post_meta($post->ID, '_ttbp_coupon_use_limit', true);
        $discount_type   = get_post_meta($post->ID, '_ttbp_coupon_discount_type', true);
        $discount_amount = get_post_meta($post->ID, '_ttbp_coupon_discount_amount', true);

        wp_nonce_field('save_coupon_meta_box', 'coupon_meta_nonce');
    ?>
        <style>
            .generate-code-wrap {
                display: flex;
                gap: 10px;
                align-items: center;
            }

            .generate-code-wrap input {
                flex: 1;
            }
        </style>

        <p>
            <label for="coupon_code"><strong><?php _e('Coupon Code:', 'teetime-booking-pro'); ?></strong></label><br />
        <div class="generate-code-wrap">
            <input type="text" name="coupon_code" id="coupon_code" value="<?php echo esc_attr($coupon_code); ?>" />
            <button type="button" class="button" id="generate_coupon_code"><?php _e('Generate Code', 'teetime-booking-pro'); ?></button>
        </div>
        </p>

        <p>
            <label for="coupon_use_limit"><strong><?php _e('Coupon Use Limit:', 'teetime-booking-pro'); ?></strong></label><br />
            <input type="number" name="coupon_use_limit" id="coupon_use_limit" value="<?php echo esc_attr($use_limit); ?>" min="0" />
        </p>

        <p>
            <label for="coupon_discount_type"><strong><?php _e('Discount Type:', 'teetime-booking-pro'); ?></strong></label><br />
            <select name="coupon_discount_type" id="coupon_discount_type">
                <option value="fixed" <?php selected($discount_type, 'fixed'); ?>><?php _e('Fixed Amount', 'teetime-booking-pro'); ?></option>
                <option value="percentage" <?php selected($discount_type, 'percentage'); ?>><?php _e('Percentage', 'teetime-booking-pro'); ?></option>
            </select>
        </p>

        <p>
            <label for="coupon_discount_amount"><strong><?php _e('Discount Amount:', 'teetime-booking-pro'); ?></strong></label><br />
            <input type="number" step="0.01" name="coupon_discount_amount" id="coupon_discount_amount" value="<?php echo esc_attr($discount_amount); ?>" />
        </p>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const generateBtn = document.getElementById('generate_coupon_code');
                const inputField = document.getElementById('coupon_code');

                generateBtn?.addEventListener('click', function() {
                    const code = Math.random().toString(36).substring(2, 10).toUpperCase();
                    inputField.value = code;
                });
            });
        </script>
    <?php
    }

    public function save_post_coupon($post_id)
    {
        if (
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
            !isset($_POST['coupon_meta_nonce']) ||
            !wp_verify_nonce($_POST['coupon_meta_nonce'], 'save_coupon_meta_box')
        ) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) return;

        update_post_meta($post_id, '_ttbp_coupon_code', sanitize_text_field($_POST['coupon_code'] ?? ''));
        update_post_meta($post_id, '_ttbp_coupon_use_limit', intval($_POST['coupon_use_limit'] ?? 0));
        update_post_meta($post_id, '_ttbp_coupon_discount_type', sanitize_text_field($_POST['coupon_discount_type'] ?? 'fixed'));
        update_post_meta($post_id, '_ttbp_coupon_discount_amount', floatval($_POST['coupon_discount_amount'] ?? 0));
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
        $tax = (float) get_post_meta($post->ID, 'ttbp_tax_amount', true);
        $due = (float) $total + $tax - (float) $deposit;
        $is_adv = get_post_meta($post->ID, 'ttbp_adv_pay', true) ? get_post_meta($post->ID, 'ttbp_adv_pay', true) : false;

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
                                    $player_email = get_post_meta($post->ID, 'ttbp_player_email_' . $p, true) ? get_post_meta($post->ID, 'ttbp_player_email_' . $p, true) : 'Player - ' . $p;
                                    $player_phone = get_post_meta($post->ID, 'ttbp_player_phone_' . $p, true) ? get_post_meta($post->ID, 'ttbp_player_phone_' . $p, true) : 'Player - ' . $p;
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
                    <td style="font-weight: 500; color: #7a7a7a">Subtotal</td>
                    <td style="text-align: right;font-weight: 500; color: #7a7a7a"><?php echo TTBP__price($total); ?></td>
                </tr>
                <tr>
                    <th colspan="3"></th>
                    <td style="font-weight: 500; color: #7a7a7a">Tax</td>
                    <td style="text-align: right;font-weight: 500; color: #7a7a7a">
                        <?php echo TTBP__price($tax); ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="3"></th>
                    <td style="font-weight: 500; color: #7a7a7a">Booking Deposit</td>
                    <td style="text-align: right;font-weight: 500; color: #7a7a7a">
                        <?php echo '-' . TTBP__price($deposit); ?>
                    </td>
                </tr>
                <?php if ($is_adv): ?>
                    <tr>
                        <th colspan="3"></th>
                        <td style="font-weight: 500; color: #7a7a7a">Adv. Pay Discount</td>
                        <td style="text-align: right;font-weight: 500; color: #7a7a7a">
                            <?php echo TTBP__price($total - $deposit); ?>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="3"></th>
                        <td style="border-top: 2px solid #ddd;font-weight: 500; color: #000">Total Due at Course</td>
                        <td style="border-top: 2px solid #ddd; text-align: right;font-weight: 500; color: #000">
                            <?php echo TTBP__price(0); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th colspan="3"></th>
                        <td style="border-top: 2px solid #ddd;font-weight: 500; color: #000">Total Due at Course</td>
                        <td style="border-top: 2px solid #ddd; text-align: right;font-weight: 500; color: #000">
                            <?php echo TTBP__price($due); ?>
                        </td>
                    </tr>
                <?php endif; ?>
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
