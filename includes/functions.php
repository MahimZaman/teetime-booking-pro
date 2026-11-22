<?php

if (!defined('ABSPATH')) return;

$ttbp_currencies = array(
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'AUD' => 'A$',
    'CAD' => 'C$',
    'JPY' => '¥',
    'CNY' => '¥',
    'INR' => '₹',
    'RUB' => '₽',
    'BRL' => 'R$',
    'ZAR' => 'R',
    'NZD' => 'NZ$',
    'CHF' => 'CHF',
    'SEK' => 'kr',
    'NOK' => 'kr',
    'DKK' => 'kr',
    'HKD' => 'HK$',
    'SGD' => 'S$',
    'MXN' => 'MX$',
    'PLN' => 'zł',
    'TRY' => '₺',
    'THB' => '฿',
    'MYR' => 'RM',
    'IDR' => 'Rp',
    'PHP' => '₱',
    'VND' => '₫',
    'KRW' => '₩',
    'ILS' => '₪',
    'SAR' => '﷼',
    'AED' => 'د.إ',
    'EGP' => 'E£',
);

function render_slots($start_hour, $end_hour, $interval_minutes)
{
    $current = strtotime($start_hour);
    $end = strtotime($end_hour);
    $output = [];

    if ($current <= $end) {
        while ($current <= $end) {
            $output[] = $current;
            $current = strtotime("+{$interval_minutes} minutes", $current);
        }
    } else {
        $new_end = '23:59';

        while ($current <= strtotime($new_end)) {
            $output[] = $current;
            $current = strtotime("+{$interval_minutes} minutes", $current);
        }

        $new_current = strtotime(date('H:i', $current));

        while ($new_current <= strtotime($end_hour)) {
            $output[] = $new_current;
            $new_current = strtotime("+{$interval_minutes} minutes", $new_current);
        }
    }

    return $output;
}


function TTBP__modal($id = '', $body = '', $header = 'Modal', $footer = '')
{
    ob_start();
?>
    <div class="ttbp_fe_modal" id="<?php echo $id ?>">
        <div class="ttbp_modal_inner">
            <div class="ttbp_modal_header">
                <?php echo $header; ?>
                <span class="ttbp_close_modal">
                    <i class="fa-solid fa-times"></i>
                </span>
            </div>
            <div class="ttbp_modal_body">
                <?php echo $body; ?>
            </div>
            <?php if (!empty($footer)): ?>
                <div class="ttbp_modal_footer">
                    <?php echo $footer; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

function TTBP__send_email($to_email, $data = [], $type = '')
{
    $from = get_option('ttbp_email_from', get_bloginfo('admin_email'));
    $subject = get_option('ttbp_email_subject', 'Your Booking Confirmation');
    $template = get_option('ttbp_email_template', '');

    if ($type == 'update') {
        $template = get_option('ttbp_update_email_template', '');
        $subject = get_option('ttbp_update_email_subject', 'Your Booking Details Changed');
    } elseif ($type == 'canceled') {
        $template = get_option('ttbp_canceled_email_template', '');
        $subject = get_option('ttbp_canceled_email_subject', 'Your Booking Canceled');
    }

    // Replace placeholders
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', esc_html($value), $template);
    }

    $url = site_url(); // e.g. https://www.sitetitle.com
    $host = parse_url($url, PHP_URL_HOST); // Gets 'www.sitetitle.com'

    // Remove 'www.' if present
    $domain = preg_replace('/^www\./', '', $host);
    $site_name = get_bloginfo('name');
    $from_name = $site_name . '<noreply@' . $domain . '>';
    if (empty($site_name)) {
        $from_name = 'Tee Time Booking <noreply@' . $domain . '>';
    }

    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: $from_name";

    return wp_mail($to_email, $subject, $template, $headers);
}

function TTBP__get_default_email_template()
{
    return '
    <h2>Booking Confirmed 🎉</h2>
    <p>Hi {{name}},</p>
    <p>Thank you for your booking! Here are your reservation details:</p>
    <table>
        <tbody>
            <tr><th align="left">Name:</th><td>{{name}}</td></tr>
            <tr><th align="left">Email:</th><td>{{email}}</td></tr>
            <tr><th align="left">Phone:</th><td>{{phone}}</td></tr>
            <tr><th align="left">Date:</th><td>{{date}}</td></tr>
            <tr><th align="left">Time:</th><td>{{time}}</td></tr>
            <tr><th align="left">No. of Players:</th><td>{{players}}</td></tr>
            <tr><th align="left">Holes:</th><td>{{holes}}</td></tr>
            <tr><th align="left">Ride:</th><td>{{ride}}</td></tr>
            <tr><th align="left">Golf Course:</th><td>{{golf_course}}</td></tr>
        </tbody>
        <tfoot>
            <tr><th style="text-align: left;">Total Amount</th><td>{{total_amount}}</td></tr>
            <tr><th style="text-align: left;">Booking Amount</th><td>{{booking_amount}}</td></tr>
            <tr><th style="text-align: left;">Total Due at Course</th><td>{{due_amount}}</td></tr>
        </tfoot>
    </table>
    <p>If you have any questions, feel free to reach out to us at {{support_email}}.</p>
    ';
}

function TTBP__get_update_email_template()
{
    return '
    <h2>Booking Details Changed</h2>
    <p>Hi {{name}},</p>
    <p>Your booking details have been updated:</p>
    <table>
        <tbody>
            <tr><th align="left">Name:</th><td>{{name}}</td></tr>
            <tr><th align="left">Email:</th><td>{{email}}</td></tr>
            <tr><th align="left">Phone:</th><td>{{phone}}</td></tr>
            <tr><th align="left">Date:</th><td>{{date}}</td></tr>
            <tr><th align="left">Time:</th><td>{{time}}</td></tr>
            <tr><th align="left">No. of Players:</th><td>{{players}}</td></tr>
            <tr><th align="left">Holes:</th><td>{{holes}}</td></tr>
            <tr><th align="left">Ride:</th><td>{{ride}}</td></tr>
            <tr><th align="left">Golf Course:</th><td>{{golf_course}}</td></tr>
        </tbody>
        <tfoot>
            <tr><th style="text-align: left;">Total Amount</th><td>{{total_amount}}</td></tr>
            <tr><th style="text-align: left;">Booking Amount</th><td>{{booking_amount}}</td></tr>
            <tr><th style="text-align: left;">Total Due at Course</th><td>{{due_amount}}</td></tr>
        </tfoot>
    </table>
    <p>If you have any questions, feel free to reach out to us at {{support_email}}.</p>
    ';
}

function TTBP__get_canceled_email_template()
{
    return '
    <h2>Booking Canceled</h2>
    <p>Hi {{name}},</p>
    <p>Your booking has been canceled. Contact Support for details {{support_email}}</p>
    <table>
        <tbody>
            <tr><th align="left">Name:</th><td>{{name}}</td></tr>
            <tr><th align="left">Email:</th><td>{{email}}</td></tr>
            <tr><th align="left">Phone:</th><td>{{phone}}</td></tr>
            <tr><th align="left">Date:</th><td>{{date}}</td></tr>
            <tr><th align="left">Time:</th><td>{{time}}</td></tr>
            <tr><th align="left">No. of Players:</th><td>{{players}}</td></tr>
            <tr><th align="left">Holes:</th><td>{{holes}}</td></tr>
            <tr><th align="left">Ride:</th><td>{{ride}}</td></tr>
            <tr><th align="left">Golf Course:</th><td>{{golf_course}}</td></tr>
        </tbody>
        <tfoot>
            <tr><th style="text-align: left;">Total Amount</th><td>{{total_amount}}</td></tr>
            <tr><th style="text-align: left;">Booking Amount</th><td>{{booking_amount}}</td></tr>
            <tr><th style="text-align: left;">Total Due at Course</th><td>{{due_amount}}</td></tr>
        </tfoot>
    </table>
    <p>If you have any questions, feel free to reach out to us at {{support_email}}.</p>
    ';
}

function TTBP__declare_defaults()
{
    if (!get_option('ttbp_email_template')) {
        update_option('ttbp_email_template', TTBP__get_default_email_template());
    }
    if (!get_option('ttbp_default_duration')) {
        update_option('ttbp_default_duration', 9);
    }
    if (!get_option('ttbp_default_start_morning')) {
        update_option('ttbp_default_start_morning', '05:00');
    }
    if (!get_option('ttbp_default_start_midday')) {
        update_option('ttbp_default_start_midday', '12:00');
    }
    if (!get_option('ttbp_default_start_evening')) {
        update_option('ttbp_default_start_evening', '17:00');
    }
    if (!get_option('ttbp_default_end')) {
        update_option('ttbp_default_end', '00:00');
    }
    if (!get_option('ttbp_booking_amount')) {
        update_option('ttbp_booking_amount', '0');
    }
    if (!get_option('ttbp_total_amount_9_walking')) {
        update_option('ttbp_total_amount_9_walking', '0');
    }
    if (!get_option('ttbp_total_amount_18_walking')) {
        update_option('ttbp_total_amount_18_walking', '0');
    }
    if (!get_option('ttbp_total_amount_9_cart')) {
        update_option('ttbp_total_amount_9_cart', '0');
    }
    if (!get_option('ttbp_total_amount_18_cart')) {
        update_option('ttbp_total_amount_18_cart', '0');
    }

    if (!get_option('ttbp_currency')) {
        update_option('ttbp_currency', 'USD');
    }
    if (!get_option('ttbp_currency_position')) {
        update_option('ttbp_currency_position', 'left');
    }
    if (!get_option('ttbp_decimal_separator')) {
        update_option('ttbp_decimal_separator', '.');
    }
    if (!get_option('ttbp_thousand_separator')) {
        update_option('ttbp_thousand_separator', ',');
    }
    if (!get_option('ttbp_decimals')) {
        update_option('ttbp_decimals', '2');
    }
    if (!get_option('ttbp_max_booking_window')) {
        update_option('ttbp_max_booking_window', '7');
    }
    if (!get_option('ttbp_lead_time')) {
        update_option('ttbp_lead_time', '1');
    }
    if (!get_option('ttbp_default_timezone')) {
        update_option('ttbp_default_timezone', 'UTC');
    }
    if (!get_option('ttbp_opt_in_label')) {
        update_option('ttbp_opt_in_label', 'Opt-in for promotions and latest news via email and text.');
    }
    if (!get_option('ttbp_pia_label')) {
        update_option('ttbp_pia_label', 'Pay in advanced to get a {discount} discount');
    }
    if (!get_option('ttbp_clover_stat')) {
        update_option('ttbp_clover_stat', 'disable');
    }
    if (!get_option('ttbp_offline_stat')) {
        update_option('ttbp_offline_stat', 'enable');
    }
    if (!get_option('ttbp_adv_payment_discount')) {
        update_option('ttbp_adv_payment_discount', 0);
    }
}

function TTBP__add_notice($message, $type)
{
    add_action('admin_notices', function () use ($message, $type) {
        $class = 'notice notice-' . $type;
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), __(esc_html($message), 'teetime-booking-pro'));
    });
}

function TTBP__get_template($path)
{
    require_once(TTBP_PATH . 'templates/' . $path);
}

function TTBP_get_currency_symbol($currency)
{
    global $ttbp_currencies;
    $currencies = $ttbp_currencies;

    return $currencies[$currency];
}


function TTBP__price($price, $args = array())
{
    $defaults = array(
        'currency'           => get_option('ttbp_currency'),
        'currency_position'  => get_option('ttbp_currency_position'),
        'decimal_separator'  => get_option('ttbp_decimal_separator'),
        'thousand_separator' => get_option('ttbp_thousand_separator'),
        'decimals'           => get_option('ttbp_decimals'),
    );

    $args = wp_parse_args($args, $defaults);

    // Format the number first
    $formatted_price = number_format(floatval($price), $args['decimals'], $args['decimal_separator'], $args['thousand_separator']);

    // Get currency symbol
    $currency_symbol = TTBP_get_currency_symbol($args['currency']);

    // Build based on position
    switch ($args['currency_position']) {
        case 'left':
            $formatted = $currency_symbol . $formatted_price;
            break;
        case 'right':
            $formatted = $formatted_price . $currency_symbol;
            break;
        case 'left_space':
            $formatted = $currency_symbol . ' ' . $formatted_price;
            break;
        case 'right_space':
            $formatted = $formatted_price . ' ' . $currency_symbol;
            break;
        default:
            $formatted = $currency_symbol . $formatted_price;
            break;
    }

    return apply_filters('ttbp_format_price', $formatted, $price, $args);
}

function TTBP_get_courses()
{
    $golf_courses = get_terms('golf_course', array(
        'hide_empty' => 0,
    ));
    $courses = [];
    if (!empty($golf_courses)) {
        foreach ($golf_courses as $golf_course) {
            $courses[$golf_course->term_id] = __($golf_course->name, 'teetime-booking-pro');
        }
    }

    return $courses;
}

function TTBP_get_cart_icon()
{
    ob_start();
?>
    <svg fill="#000000" width="16px" height="20px" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <path d="M23 7C14.523438 7 8.8125 8 8.8125 8C8.261719 8.101563 7.894531 8.636719 8 9.1875C8.101563 9.738281 8.636719 10.105469 9.1875 10C9.1875 10 9.945313 9.890625 11 9.75L11 18L6 18L6 20L11 20L11 24.03125C13.097656 24.128906 15.246094 24.363281 17.34375 24.75C16.949219 23.519531 16.421875 21.949219 15.9375 20.65625C15.296875 18.960938 13.949219 18.34375 13 18.125L13 9.5C15.410156 9.25 18.855469 9 23 9C26.136719 9 28.871094 9.136719 31.0625 9.3125C31.070313 9.34375 31.082031 9.375 31.09375 9.40625L37.90625 25L34.34375 33L26 33L26 31.28125C26 30.152344 25.277344 29.167969 24.125 28.71875C19.441406 26.882813 13.253906 25.835938 7.96875 26C6.21875 26.054688 5.015625 26.449219 4.28125 27.21875C2.273438 29.320313 2 32.839844 2 36C2 36.550781 2.449219 37 3 37L4.09375 37C4.027344 37.324219 4 37.65625 4 38C4 40.757813 6.242188 43 9 43C11.414063 43 13.441406 41.277344 13.90625 39L38.09375 39C38.558594 41.277344 40.585938 43 43 43C45.757813 43 48 40.757813 48 38C48 37.773438 47.964844 37.5625 47.9375 37.34375C47.972656 37.238281 48 37.117188 48 37L48 31C48 30.917969 47.988281 30.828125 47.96875 30.75C47.105469 27.292969 41.171875 24.78125 39.75 24.21875L33.3125 9.53125C35.503906 9.769531 36.8125 10 36.8125 10C37.363281 10.105469 37.898438 9.738281 38 9.1875C38.105469 8.636719 37.738281 8.101563 37.1875 8C37.1875 8 31.476563 7 23 7 Z M 30.875 16C30.652344 16.023438 30.441406 16.125 30.28125 16.28125L26.28125 20.28125C25.882813 20.679688 25.882813 21.320313 26.28125 21.71875C26.679688 22.117188 27.320313 22.117188 27.71875 21.71875L29 20.4375L35.0625 26.46875L35.9375 24.5L30.4375 19L31.71875 17.71875C32.042969 17.417969 32.128906 16.941406 31.933594 16.546875C31.742188 16.148438 31.308594 15.929688 30.875 16 Z M 9 35C10.652344 35 12 36.347656 12 38C12 39.652344 10.652344 41 9 41C7.347656 41 6 39.652344 6 38C6 36.347656 7.347656 35 9 35 Z M 43 35C44.652344 35 46 36.347656 46 38C46 39.652344 44.652344 41 43 41C41.347656 41 40 39.652344 40 38C40 36.347656 41.347656 35 43 35Z" />
    </svg>
<?php
    return ob_get_contents();
}

function TTBP_timediff($start_timestamp, $end_timestamp)
{
    $diff = abs($end_timestamp - $start_timestamp); // difference in seconds

    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);

    return "{$hours} hr" . ($hours !== 1 ? "s" : "") . " {$minutes} min" . ($minutes !== 1 ? "s" : "");
}

function TTBP_timesum($time1, $time2)
{
    // Extract hours and minutes using regex
    preg_match('/(\d+)\s+hour.*?(\d+)\s+minute/', $time1, $matches1);
    preg_match('/(\d+)\s+hour.*?(\d+)\s+minute/', $time2, $matches2);

    // Convert to integers
    $hours   = (int)$matches1[1] + (int)$matches2[1];
    $minutes = (int)$matches1[2] + (int)$matches2[2];

    // Normalize minutes to hours
    $extra_hours = floor($minutes / 60);
    $minutes = $minutes % 60;
    $hours += $extra_hours;

    return "{$hours} hr" . ($hours !== 1 ? "s" : "") . " {$minutes} min" . ($minutes !== 1 ? "s" : "");
}

function TTBP_normalize_time_string($str)
{
    return str_replace(['hrs', 'hr', 'mins', 'min'], ['hours', 'hour', 'minutes', 'minute'], strtolower($str));
}
