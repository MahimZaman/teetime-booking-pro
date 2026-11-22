<?php
if (!defined('ABSPATH')) return;

global $wpdb;
$date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');

$query = "
    SELECT pm_time.meta_value AS tee_time, GROUP_CONCAT(pm_date.post_id) AS post_ids
    FROM {$wpdb->postmeta} pm_date
    INNER JOIN {$wpdb->postmeta} pm_time ON pm_date.post_id = pm_time.post_id
    WHERE pm_date.meta_key = %s
      AND pm_date.meta_value = %s
      AND pm_time.meta_key = %s
    GROUP BY pm_time.meta_value
    ORDER BY STR_TO_DATE(pm_time.meta_value, '%%h:%%i%%p') ASC
";

$results = $wpdb->get_results(
    $wpdb->prepare($query, 'ttbp_date', $date, 'ttbp_time'),
    ARRAY_A
);

if (!empty($results)) {
?>
    <div class="ttbp_starter_container">
        <?php
        foreach ($results as $result) {
            $time = $result['tee_time'];
            $bookings = explode(',', $result['post_ids']);
        ?>
            <div class="ttbp_starter_group">
                <span class="ttbp_teetime"><?php echo $time; ?></span>
                <?php
                foreach ($bookings as $booking_id) {
                    $player_1 = get_post_meta($booking_id, 'ttbp_player_name_1', true);
                    $player_2 = get_post_meta($booking_id, 'ttbp_player_name_2', true);
                    $player_3 = get_post_meta($booking_id, 'ttbp_player_name_3', true);
                    $player_4 = get_post_meta($booking_id, 'ttbp_player_name_4', true);
                    $paid_1 = get_post_meta($booking_id, 'ttbp_payment_status_1', true);
                    $paid_2 = get_post_meta($booking_id, 'ttbp_payment_status_2', true);
                    $paid_3 = get_post_meta($booking_id, 'ttbp_payment_status_3', true);
                    $paid_4 = get_post_meta($booking_id, 'ttbp_payment_status_4', true);
                    $teed_off = get_post_meta($booking_id, 'ttbp_teed_off', true);
                    $turn_time = get_post_meta($booking_id, 'ttbp_turn_time', true);
                    $end_time = get_post_meta($booking_id, 'ttbp_end_time', true);
                    $front_nine = get_post_meta($booking_id, 'ttbp_front_nine', true);
                    $back_nine = get_post_meta($booking_id, 'ttbp_back_nine', true);
                    $time_total = get_post_meta($booking_id, 'ttbp_time_total', true);
                    $cart_code_1 = get_post_meta($booking_id, 'ttbp_cart_code_1', true);
                    $cart_code_2 = get_post_meta($booking_id, 'ttbp_cart_code_2', true);
                ?>
                    <div class="ttbp_single_booking">
                        <ul class="ttbp_customers">
                            <li>
                                <i class="fa-solid fa-circle" style="color: <?php echo $paid_1 == 'paid' ? 'var(--success)' : '#FFA71A' ?>"></i> <?php echo $player_1; ?>
                            </li>
                            <li>
                                <i class="fa-solid fa-circle" style="color: <?php echo $paid_2 == 'paid' ? 'var(--success)' : '#FFA71A' ?>"></i> <?php echo $player_2; ?>
                            </li>
                            <li>
                                <i class="fa-solid fa-circle" style="color: <?php echo $paid_3 == 'paid' ? 'var(--success)' : '#FFA71A' ?>"></i> <?php echo $player_3; ?>
                            </li>
                            <li>
                                <i class="fa-solid fa-circle" style="color: <?php echo $paid_4 == 'paid' ? 'var(--success)' : '#FFA71A' ?>"></i> <?php echo $player_4; ?>
                            </li>
                        </ul>
                        <div class="ttbp_timeboard">
                            <div class="ttbp_cart_count">
                                <div class="ttbp_cc_group">
                                    <span class="ttbp_cc_img">
                                        <?php TTBP_get_cart_icon() ?>
                                    </span>
                                    <input type="text" id="ttbp_cart_code_1" value="<?php echo $cart_code_1 ?>" data-booking_id="<?php echo $booking_id; ?>" />
                                </div>
                                <div class="ttbp_cc_group">
                                    <span class="ttbp_cc_img">
                                        <?php TTBP_get_cart_icon() ?>
                                    </span>
                                    <input type="text" id="ttbp_cart_code_2" value="<?php echo $cart_code_2 ?>" data-booking_id="<?php echo $booking_id; ?>" />
                                </div>
                            </div>
                            <div class="ttbp_turn_times">
                                <button type="button" class="turn_btn ttbp_button_has_loader <?php echo $teed_off ? 'ttbp_disabled' : '' ?>" data-meta_key="teed_off" id="teed_off_<?php echo $booking_id ?>" value="<?php echo $booking_id; ?>">
                                    <span class="ttbp_clicked_time"><?php echo $teed_off ? date('g:ia', $teed_off) : __('Tee\'d Off', 'teetime-booking-pro'); ?></span>
                                    <span class="ttbp_button_loader"></span>
                                </button>
                                <button type="button" class="turn_btn ttbp_button_has_loader <?php echo $turn_time ? 'ttbp_disabled' : '' ?>" data-meta_key="turn_time" id="turn_time_<?php echo $booking_id ?>" value="<?php echo $booking_id; ?>">
                                    <span class="ttbp_clicked_time"><?php echo $turn_time ? date('g:ia', $turn_time) : __('Turn Time', 'teetime-booking-pro'); ?></span>
                                    <span class="ttbp_button_loader"></span>
                                </button>
                                <button type="button" class="turn_btn ttbp_button_has_loader <?php echo $end_time ? 'ttbp_disabled' : '' ?>" data-meta_key="end_time" id="end_time_<?php echo $booking_id ?>" value="<?php echo $booking_id; ?>">
                                    <span class="ttbp_clicked_time"><?php echo $end_time ? date('g:ia', $end_time) : __('End Time', 'teetime-booking-pro'); ?></span>
                                    <span class="ttbp_button_loader"></span>
                                </button>
                            </div>
                        </div>
                        <div class="ttbp_paceofplay">
                            <h2><?php _e('Pace of play', 'teetime-booking-pro'); ?></h2>
                            <div class="ttbp_cal_paceofplay">
                                <span class="pace_btn front_nine" id="front_nine_<?php echo $booking_id; ?>">
                                    <span><?php _e('Front Nine', 'teetime-booking-pro'); ?></span>
                                    <strong><?php echo $front_nine; ?></strong>
                                </span>
                                <span class="pace_btn back_nine" id="back_nine_<?php echo $booking_id; ?>">
                                    <span><?php _e('Back Nine', 'teetime-booking-pro'); ?></span>
                                    <strong><?php echo $back_nine; ?></strong>
                                </span>
                                <span class="pace_btn time_total" id="time_total_<?php echo $booking_id; ?>">
                                    <span><?php _e('Total', 'teetime-booking-pro'); ?></span>
                                    <strong><?php echo $time_total; ?></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        <?php
        }
        ?>
    </div>
<?php
} else {
    echo '<p class="ttbp-not-found">' . __('No bookings available', 'teetime-booking-pro') . '</p>';
}
