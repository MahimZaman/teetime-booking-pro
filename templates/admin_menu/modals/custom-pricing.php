<?php
if (!defined('ABSPATH')) return;
?>

<div class="ttbp_modal" id="ttbp_add_pricing">
    <div class="ttbp_modal_inner">
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="ttbp_add_rule">
            <span class="ttbp_close_modal ttbp_close_icon">
                <i class="fa-solid fa-times"></i>
            </span>
            <div class="ttbp_form_group">
                <label for="custom_weekday" class="ttbp_label"><?php _e('Weekday', 'teetime-booking-pro'); ?></label>
                <select type="date" name="custom_weekday" id="custom_weekday" class="ttbp_input" required>
                    <option value="">Select a day</option>
                    <option value="1"><?php echo __('Monday', 'teetime-booking-pro') ?></option>
                    <option value="2"><?php echo __('Tuesday', 'teetime-booking-pro') ?></option>
                    <option value="3"><?php echo __('Wednesday', 'teetime-booking-pro') ?></option>
                    <option value="4"><?php echo __('Thursday', 'teetime-booking-pro') ?></option>
                    <option value="5"><?php echo __('Friday', 'teetime-booking-pro') ?></option>
                    <option value="6"><?php echo __('Saturday', 'teetime-booking-pro') ?></option>
                    <option value="7"><?php echo __('Sunday', 'teetime-booking-pro') ?></option>
                </select>
            </div>
            <div class="ttbp_form_group">
                <label for="custom_booking_amount" class="ttbp_label"><?php _e('Booking Deposit', 'teetime-booking-pro'); ?></label>
                <input type="number" step="0.01" min="1" name="custom_booking_amount" id="custom_booking_amount" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_window_start" class="ttbp_label"><?php _e('Time Start', 'teetime-booking-pro'); ?></label>
                <input type="time" name="custom_window_start" id="custom_window_start" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_window_end" class="ttbp_label"><?php _e('Time End', 'teetime-booking-pro'); ?></label>
                <input type="time" name="custom_window_end" id="custom_window_end" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_total_amount_9" class="ttbp_label"><?php _e('Total Amount (9 Holes)', 'teetime-booking-pro'); ?></label>
                <input type="number" step="0.01" min="1" name="custom_total_amount_9" id="custom_total_amount_9" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_total_amount_18" class="ttbp_label"><?php _e('Total Amount (18 Holes)', 'teetime-booking-pro'); ?></label>
                <input type="number" step="0.01" min="1" name="custom_total_amount_18" id="custom_total_amount_18" class="ttbp_input" required />
            </div>
            <?php wp_nonce_field('ttbp_add_pricing', 'ttbp_add_pricing_nonce'); ?>
            <input type="hidden" name="action" value="ttbp_add_pricing" />
            <button class="ttbp_button" id="ttbp_submit_pricing"><?php _e('Submit Pricing', 'teetime-booking-pro'); ?></button>
        </form>
    </div>
</div>