<?php
if (!defined('ABSPATH')) return;
?>

<div class="ttbp_modal" id="ttbp_add_rule">
    <div class="ttbp_modal_inner">
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="ttbp_add_rule">
            <span class="ttbp_close_modal ttbp_close_icon">
                <i class="fa-solid fa-times"></i>
            </span>
            <div class="ttbp_form_group">
                <label for="custom_date" class="ttbp_label"><?php _e('Date', 'teetime-booking-pro'); ?></label>
                <input type="date" name="custom_date" id="custom_date" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_start_morning" class="ttbp_label"><?php _e('Start Time (Morning)', 'teetime-booking-pro'); ?></label>
                <input type="time" name="custom_start_morning" id="custom_start_morning" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_start_midday" class="ttbp_label"><?php _e('Start Time (Midday)', 'teetime-booking-pro'); ?></label>
                <input type="time" name="custom_start_midday" id="custom_start_midday" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_start_evening" class="ttbp_label"><?php _e('Start Time (Afternoon)', 'teetime-booking-pro'); ?></label>
                <input type="time" name="custom_start_evening" id="custom_start_evening" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_end" class="ttbp_label"><?php _e('End Time', 'teetime-booking-pro'); ?></label>
                <input type="time" name="custom_end" id="custom_end" class="ttbp_input" required />
            </div>
            <div class="ttbp_form_group">
                <label for="custom_duration" class="ttbp_label"><?php _e('Duration (mins)', 'teetime-booking-pro'); ?></label>
                <input type="number" name="custom_duration" id="custom_duration" class="ttbp_input" required />
            </div>
            <?php wp_nonce_field('ttbp_add_rule', 'ttbp_add_rule_nonce'); ?>
            <input type="hidden" name="action" value="ttbp_add_rule" />
            <button class="ttbp_button" id="ttbp_submit_rule"><?php _e('Submit Rule', 'teetime-booking-pro'); ?></button>
        </form>
    </div>
</div>