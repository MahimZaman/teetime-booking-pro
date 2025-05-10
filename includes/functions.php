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

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
    ];

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
    if (!get_option('ttbp_total_amount_9')) {
        update_option('ttbp_total_amount_9', '0');
    }
    if (!get_option('ttbp_total_amount_18')) {
        update_option('ttbp_total_amount_18', '0');
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
    if (!get_option('ttbp_clover_stat')) {
        update_option('ttbp_clover_stat', 'disable');
    }
    if (!get_option('ttbp_offline_stat')) {
        update_option('ttbp_offline_stat', 'enable');
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
