<?php

if (!defined('ABSPATH')) return;

$booking_id = sanitize_text_field($_GET['booking_id']);
$date = get_post_meta($booking_id, 'ttbp_date', true);
$time = get_post_meta($booking_id, 'ttbp_time', true);
$name = get_post_meta($booking_id, 'ttbp_name', true);
$email = get_post_meta($booking_id, 'ttbp_email', true);
$phone = get_post_meta($booking_id, 'ttbp_phone', true);
$players = get_post_meta($booking_id, 'ttbp_players', true);
$p_method = get_post_meta($booking_id, 'ttbp_payment_method', true);
$holes = get_post_meta($booking_id, 'ttbp_holes', true);
$term_id = get_post_meta($booking_id, 'ttbp_golf_course', true);
$term = get_term_by('term_id', $term_id, 'golf_course');
$course = isset($term->name) ? $term->name : 'Not Mentioned';

?>
<div class="ttbp_confirmation_container">
    <div class="ttbp_confirmation_header">
        <h1>Booking Confirmation</h1>
    </div>

    <div class="ttbp_confirmation_booking-details">
        <div class="ttbp_confirmation_confirmation-id">
            <div class="ttbp_confirmation_detail-label">Booking ID</div>
            <div class="ttbp_confirmation_detail-value"><strong>#<?php echo $booking_id; ?></strong></div>
        </div>

        <div class="ttbp_confirmation_detail-row">
            <div class="ttbp_confirmation_detail-label">Date</div>
            <div class="ttbp_confirmation_detail-value"><?php echo date('l, F d, Y', strtotime($date)) ?></div>
        </div>

        <div class="ttbp_confirmation_detail-row">
            <div class="ttbp_confirmation_detail-label">Slot</div>
            <div class="ttbp_confirmation_detail-value"><?php echo date('g:i A', strtotime($time)) ?></div>
        </div>

        <div class="ttbp_confirmation_detail-row">
            <div class="ttbp_confirmation_detail-label">Golf Course</div>
            <div class="ttbp_confirmation_detail-value"><?php echo $course; ?></div>
        </div>

        <div class="ttbp_confirmation_detail-row">
            <div class="ttbp_confirmation_detail-label">Players</div>
            <div class="ttbp_confirmation_detail-value">
                <?php echo $players; ?>
            </div>
        </div>

        <div class="ttbp_confirmation_detail-row">
            <div class="ttbp_confirmation_detail-label">Holes</div>
            <div class="ttbp_confirmation_detail-value"><?php echo $holes; ?></div>
        </div>

        <div class="ttbp_confirmation_detail-row">
            <div class="ttbp_confirmation_detail-label">Payment Method</div>
            <div class="ttbp_confirmation_detail-value"><?php echo $p_method; ?></div>
        </div>

        <?php for ($i = 1; $i <= (int) $players; $i++):
        ?>
            <div class="ttbp_confirmation_detail-row">
                <div class="ttbp_confirmation_detail-label">Payment (Player - <?php echo $i; ?>)</div>
                <?php if (get_post_meta($booking_id, 'ttbp_payment_status_' . $i, true) == 'paid'): ?>
                    <div class="ttbp_confirmation_detail-value ttbp_confirmation_payment-completed">Paid</div>
                <?php else: ?>
                    <div class="ttbp_confirmation_detail-value ttbp_confirmation_payment-pending">Due</div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>

        <div class="ttbp_confirmation_contact-info">
            If you need to modify or cancel your booking, please contact us at:<br>
            📞 <?php echo get_option('ttbp_support_phone'); ?><br>
            📧 <?php echo get_option('ttbp_email_from'); ?>
        </div>
    </div>

    <div class="ttbp_confirmation_footer">
        Thank you for choosing <?php echo $course; ?>.<br>
        We look forward to seeing you on the course!
    </div>
</div>