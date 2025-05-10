<?php
if (!defined('ABSPATH')) return;

class TTBP_WooCommerce
{
    public function __construct()
    {
        //add_action('init', [$this, 'create_booking_product']);
        //add_filter('woocommerce_cart_item_name', [$this, 'populate_cart_item_data'], 10, 3);
        //add_action('woocommerce_checkout_create_order_line_item', [$this, 'set_order_data'], 10, 4);
        //add_filter('woocommerce_order_item_name', [$this, 'ttbp_custom_order_item_name'], 10, 2);
        //add_filter('woocommerce_before_calculate_totals', [$this, 'ttbp_dynamic_cart_price'], 20, 1);
        //add_filter('woocommerce_cart_item_quantity', [$this, 'cart_quantity_validation'], 10, 3);

        add_action('init', [$this, 'register_my_bookings_endpoint']);
        add_filter('query_vars', [$this, 'add_my_bookings_query_var']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_my_account_menu_item']);
        //add_action('woocommerce_account_my-bookings_endpoint', [$this, 'my_bookings_endpoint_content']);
        add_action('woocommerce_account_staff-dashboard_endpoint', [$this, 'staff_dashboard_endpoint_content']);

        add_filter('woocommerce_account_menu_items', [$this, 'reorder_my_account_menu_items'], 100);
    }

    public function reorder_my_account_menu_items($items)
    {
        // Save logout item and remove temporarily
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);

        // Rebuild items in desired order
        $new_items = [];

        // Default items (you can reorder or selectively include them)
        if (isset($items['dashboard'])) $new_items['dashboard'] = $items['dashboard'];
        if (isset($items['orders'])) $new_items['orders'] = $items['orders'];
        if (isset($items['downloads'])) $new_items['downloads'] = $items['downloads'];
        if (isset($items['edit-address'])) $new_items['edit-address'] = $items['edit-address'];
        if (isset($items['edit-account'])) $new_items['edit-account'] = $items['edit-account'];

        // Custom endpoints
        //if (isset($items['my-bookings'])) $new_items['my-bookings'] = $items['my-bookings'];
        $is_staff = get_user_meta(get_current_user_id(), 'ttbp_is_staff', true);
        if (isset($items['staff-dashboard']) && ($is_staff || current_user_can('manage_options'))) {
            $new_items['staff-dashboard'] = $items['staff-dashboard'];
        }

        // Add logout at the end
        $new_items['customer-logout'] = $logout;

        return $new_items;
    }

    // Register custom endpoint
    public function register_my_bookings_endpoint()
    {
        //add_rewrite_endpoint('my-bookings', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('staff-dashboard', EP_ROOT | EP_PAGES);
        flush_rewrite_rules(true);
    }

    // Add query var so WordPress recognizes the endpoint
    public function add_my_bookings_query_var($vars)
    {
        //$vars[] = 'my-bookings';
        $vars[] = 'staff-dashboard';
        return $vars;
    }

    // Add custom menu item to WooCommerce My Account menu
    public function add_my_account_menu_item($items)
    {
        //$items['my-bookings'] = __('My Bookings', 'ttbp');
        $is_staff = get_user_meta(get_current_user_id(), 'ttbp_is_staff', true);
        if ($is_staff || current_user_can('manage_options')) {
            $items['staff-dashboard'] = __('Staff Dashboard', 'teetime-booking-pro');
        }
        return $items;
    }

    public function staff_dashboard_endpoint_content()
    {
        $is_staff = get_user_meta(get_current_user_id(), 'ttbp_is_staff', true);
        if (!$is_staff && !current_user_can('manage_options')) {
            echo '<p>' . __('You are not allowed to access this page.', 'te') . '</p>';
            return;
        }
        echo do_shortcode('[tee_time_calendar]');
    }

    // Render the endpoint content using a shortcode
    public function my_bookings_endpoint_content()
    {
        echo do_shortcode('[ttbp_my_bookings]');
    }

    function cart_quantity_validation($quantity_html, $cart_item_key, $cart_item)
    {
        // Target specific product ID if needed
        if ($cart_item['product_id'] == get_option('ttbp_booking_product_id')) {
            return '1'; // display as plain text
        }
        return $quantity_html;
    }


    function ttbp_dynamic_cart_price($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) return;

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['price'])) {
                $cart_item['data']->set_price(floatval($cart_item['price']));
            }
        }
    }

    function ttbp_custom_order_item_name($name, $item)
    {
        if ($custom = $item->get_meta('Golf Course')) {
            return esc_html($custom);
        }
        return $name;
    }

    public function set_order_data($item, $cart_item_key, $values, $order)
    {
        if (!empty($values['custom_booking'])) {
            $data = $values['booking_data'];
            $date = date('F j, Y', strtotime($data['date']));
            $courseName = 'Booking Item';
            $courseDesc = '';
            $term = get_term_by('id', $data['course'], 'golf_course');
            if ($term) {
                $courseName = $term->name;
                $courseDesc = trim($term->description);
            }
            $item->add_meta_data('Golf Course', $courseName . ' (' . $courseDesc . ')');
            $item->add_meta_data('Booking Date', $date);
            $item->add_meta_data('Time', $data['time']);
            $item->add_meta_data('Players', $data['players']);
            $item->add_meta_data('Holes', $data['holes']);

            $first_name = $order->get_billing_first_name();
            $last_name = $order->get_billing_last_name();
            $full_name = trim("$first_name $last_name");

            $title = $full_name . ' - ' . $date . ' @ ' . $data['time'];

            $post_id = wp_insert_post([
                'post_type'   => 'ttbp_booking',
                'post_title'  => sanitize_text_field($title),
                'post_status' => 'publish',
            ]);

            if (is_wp_error($post_id)) {
                $item->add_meta_data('Booking Status', 'Something went wrong!');
            }

            $item->add_meta_data('Booking ID', $post_id);

            $meta_fields = [
                'ttbp_date' => $data['date'],
                'ttbp_time' => $data['time'],
                'ttbp_players' => $data['players'],
                'ttbp_holes' => $data['holes'],
                'ttbp_golf_course' => $data['course'],
                'ttbp_name' => $full_name,
                'ttbp_email' => $order->get_billing_email(),
                'ttbp_phone' => $order->get_billing_phone(),
            ];

            wp_set_post_terms($post_id, [$data['course']], 'golf_course');

            TTBP_Database::insert_slot($data['date'], strtotime($data['time']), $post_id, $data['players'], 4);

            foreach ($meta_fields as $key => $field) {
                $value = $field ? sanitize_text_field($field) : '';
                update_post_meta($post_id, $key, $value);
            }

            $email_data = [
                'name' => $full_name,
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'date' => $data['date'],
                'time' => $data['time'],
                'players' => $data['players'],
                'holes' => $data['holes'],
                'golf_course' => $courseName,
            ];

            $email_sent = TTBP__send_email($order->get_billing_email(), $email_data);
            update_post_meta($post_id, 'ttbp_email_sent', $email_sent);
        }
    }

    public function populate_cart_item_data($name, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['custom_booking'])) {
            $data = $cart_item['booking_data'];
            $date = date('F j, Y', strtotime($data['date']));
            $term = get_term_by('id', $data['course'], 'golf_course');
            $courseName = 'Booking Item';
            $courseDesc = '';
            if ($term) {
                $courseName = $term->name;
                $courseDesc = trim($term->description);
            }
            return "<strong>{$courseName}</strong> ({$courseDesc})<br>
                    <div><i class='fa-regular fa-calendar'></i> Date: {$date}</div>
                    <div><i class='fa-regular fa-clock'></i> Time: {$data['time']}</div>
                    <div><i class='fa-regular fa-user'></i> {$data['players']} Player(s)</div>
                    <div><i class='fa-regular fa-circle'></i> {$data['holes']} Hole(s)</div>";
        }
    }

    public function create_booking_product()
    {
        $existing = get_page_by_path('ttbp_booking_product', OBJECT, 'product');
        if ($existing) return; // Avoid duplicates

        $product = new WC_Product_Simple();
        $product->set_name('Tee Time Booking');
        $product->set_slug('ttbp_booking_product');
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_regular_price(1);
        $product->set_virtual(true);
        $product->set_manage_stock(false);
        $product->save();


        // Store the ID in an option to reference later
        update_option('ttbp_booking_product_id', $product->get_id());
    }
}
