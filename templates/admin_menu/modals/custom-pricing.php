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
            <button type="button" class="ttbp_button" id="add_weekday_rule"><span><?php _e('Add Rule ', 'teetime-booking-pro'); ?></span><i class="fa-solid fa-plus"></i></button>
            <div class="ttbp_filters">
                <div class="ttbp_filter">
                    <select id="ttbp_bulk_clone" name="ttbp_clone_day">
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
                <button type="button" class="button" id="ttbp_apply_filters"><?php echo __('Clone Rules','teetime-booking-pro');?></button>
            </div>
            <table class="ttbp_weekday_rules">
                <thead>
                    <tr>
                        <th><input type="checkbox" name="ttbp_select_all" class="ttbp_select_all" data-table="ttbp_weekday_rules"></th>
                        <th><?php _e('Deposit', 'teetime-booking-pro'); ?></th>
                        <th><?php _e('Time Start', 'teetime-booking-pro'); ?></th>
                        <th><?php _e('Time End', 'teetime-booking-pro'); ?></th>
                        <th><?php _e('Total Amount Walking (9 Holes)', 'teetime-booking-pro'); ?></th>
                        <th><?php _e('Total Amount Cart (9 Holes)', 'teetime-booking-pro'); ?></th>
                        <th><?php _e('Total Amount Walking (18 Holes)', 'teetime-booking-pro'); ?></th>
                        <th><?php _e('Total Amount Cart (18 Holes)', 'teetime-booking-pro'); ?></th>
                        <th><?php _e('Action', 'teetime-booking-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
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
                </tbody>
            </table>
            <?php wp_nonce_field('ttbp_add_pricing', 'ttbp_add_pricing_nonce'); ?>
            <input type="hidden" name="action" value="ttbp_add_pricing" />
            <button class="ttbp_button" id="ttbp_submit_pricing"><?php _e('Save Rules', 'teetime-booking-pro'); ?></button>
        </form>
    </div>
</div>