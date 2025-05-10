<?php

if (!defined('ABSPATH')) return;

?>

<div class="wrap ttbp-wrap">
    <h2><?php _e('Teetime Booking Pro - Settings', 'teetime-booking-pro'); ?></h2>
    <hr>
    <?php
    echo '<p>Use <code>[tee_time_calendar]</code> to display the booking calendar.</p>';
    echo '<p>Use <code>[ttbp_booking_form]</code> to show the user\'s booking list.</p>';
    ?>
    <hr>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="ttbp-settings-form">
        <?php wp_nonce_field('ttbp_save_settings', 'ttbp_settings_nonce'); ?>
        <input type="hidden" name="action" value="ttbp_save_settings" />
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="default_timezone" class="ttbp-label"><?php _e('Default Timezone', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <select name="ttbp_default_timezone" id="default_timezone" value="<?php echo $this->default_timezone; ?>">
                            <?php
                            if (is_array(timezone_identifiers_list()) && !empty(timezone_identifiers_list())) {
                                foreach (timezone_identifiers_list() as $time_key => $time_zone) {
                                    echo '<option value="' . esc_attr($time_zone) . '">' . esc_html__($time_zone) . '</option>';
                                }
                            ?>
                                <script>
                                    jQuery('#default_timezone').val('<?php echo $this->default_timezone; ?>').change();
                                </script>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="default-duration" class="ttbp-label"><?php _e('Default Duration (In mins)', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="ttbp_default_duration" min="1" id="default-duration" value="<?php echo $this->default_duration; ?>">
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="default-start-morning" class="ttbp-label"><?php _e('Default Start Time (Morning)', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="ttbp_default_start_morning" id="default-start-morning" value="<?php echo $this->default_start_morning; ?>">
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="default-start-midday" class="ttbp-label"><?php _e('Default Start Time (Midday)', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="ttbp_default_start_midday" id="default-start-midday" value="<?php echo $this->default_start_midday; ?>">
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="default-start-evening" class="ttbp-label"><?php _e('Default Start Time (Afternoon)', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="ttbp_default_start_evening" id="default-start-evening" value="<?php echo $this->default_start_evening; ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="default-end" class="ttbp-label"><?php _e('End Time', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="ttbp_default_end" id="default-end" value="<?php echo $this->default_end; ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="max_booking_window" class="ttbp-label"><?php _e('Max Booking Window (In days)', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" class="ttbp_small_input" min="1" name="ttbp_max_booking_window" id="max_booking_window" value="<?php echo $this->max_booking_window; ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="lead_time" class="ttbp-label"><?php _e('Lead Time (In mins)', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" class="ttbp_small_input" min="1" name="ttbp_lead_time" id="lead_time" value="<?php echo $this->leadtime; ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="payment_notice" class="ttbp-label"><?php _e('Payment Notice', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <div class="ttbp_inline_input">
                            <textarea name="ttbp_payment_notice" style="min-width: 300px" id="payment_notice"><?php echo $this->payment_notice; ?></textarea>
                            <span class="ttbp-instruction">Enter the payment notice. User <code>{booking_amount}</code>, <code>{total_amount}</code>, <code>{due_amount}</code> for dynamic value</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="currency" class="ttbp-label"><?php _e('Currency Settings', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <div class="ttbp_inline_input">
                            <select name="ttbp_currency" id="currency" value="<?php echo $this->currency; ?>" required>
                                <?php
                                global $ttbp_currencies;
                                if (is_array($ttbp_currencies) && !empty($ttbp_currencies)):
                                    foreach ($ttbp_currencies as $currency_key => $currency_value) {
                                        echo '<option value="' . $currency_key . '">' . $currency_key . ' (' . $currency_value . ')</option>';
                                    }
                                ?>
                                    <script>
                                        jQuery('#currency').val('<?php echo $this->currency; ?>').change();
                                    </script>
                                <?php endif; ?>
                            </select>
                            <span class="ttbp-instruction"><?php _e('Currency', 'teetime-booking-pro'); ?></span>
                        </div>
                        <div class="ttbp_inline_input">
                            <select name="ttbp_currency_position" id="currency_position" value="<?php echo $this->currency_position; ?>" required>
                                <option value="left"><?php _e('Left', 'teetime-booking-pro'); ?></option>
                                <option value="right"><?php _e('Right', 'teetime-booking-pro'); ?></option>
                                <option value="left_space"><?php _e('Left Space', 'teetime-booking-pro'); ?></option>
                                <option value="right_space"><?php _e('Right Space', 'teetime-booking-pro'); ?></option>
                            </select>
                            <script>
                                jQuery('#currency_position').val('<?php echo $this->currency_position; ?>').change();
                            </script>
                            <span class="ttbp-instruction"><?php _e('Currency Position', 'teetime-booking-pro'); ?></span>
                        </div>
                        <div class="ttbp_inline_input">
                            <input type="text" class="ttbp_small_input" name="ttbp_decimal_separator" id="decimal_separator" value="<?php echo $this->decimal_separator; ?>" required>
                            <span class="ttbp-instruction"><?php _e('Decimal Separator', 'teetime-booking-pro'); ?></span>
                        </div>
                        <div class="ttbp_inline_input">
                            <input type="text" class="ttbp_small_input" name="ttbp_thousand_separator" id="thousand_separator" value="<?php echo $this->thousand_separator; ?>" required>
                            <span class="ttbp-instruction"><?php _e('Thousand Separator', 'teetime-booking-pro'); ?></span>
                        </div>
                        <div class="ttbp_inline_input">
                            <input type="number" min="0" class="ttbp_small_input" name="ttbp_decimals" id="decimal_points" value="<?php echo $this->decimal_points; ?>" required>
                            <span class="ttbp-instruction"><?php _e('Decimal Points', 'teetime-booking-pro'); ?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3 class="ttbp_section_heading"><?php _e('Payment Settings', 'teetime-booking-pro'); ?></h3>
                        <hr>
                    </th>
                </tr>
                <tr>
                    <th>
                        <label for="booking-amount" class="ttbp-label"><?php _e('Pricing (' . TTBP_get_currency_symbol($this->currency) . ')', 'teetime-booking-pro'); ?> </label>

                    </th>
                    <td>
                        <label for="booking-amount" class="ttbp_inline_input">
                            <input type="number" name="ttbp_booking_amount" step="0.01" min="1.00" id="booking-amount" placeholder="Enter amount in <?php echo TTBP_get_currency_symbol($this->currency); ?>" value="<?php echo $this->booking_amount; ?>" required>
                            <span class="ttbp-instruction"><?php _e('Booking Deposit', 'teetime-booking-pro'); ?></span>
                        </label>
                        <label for="total-amount-9" class="ttbp_inline_input">
                            <input type="number" name="ttbp_total_amount_9" step="0.01" min="1.00" id="total-amount-9" placeholder="Enter amount in <?php echo TTBP_get_currency_symbol($this->currency); ?>" value="<?php echo $this->total_amount_9; ?>" required>
                            <span class="ttbp-instruction"><?php _e('Total Amount (9 holes)', 'teetime-booking-pro'); ?></span>
                        </label>
                        <label for="total-amount-18" class="ttbp_inline_input">
                            <input type="number" name="ttbp_total_amount_18" step="0.01" min="1.00" id="total-amount-18" placeholder="Enter amount in <?php echo TTBP_get_currency_symbol($this->currency); ?>" value="<?php echo $this->total_amount_18; ?>" required>
                            <span class="ttbp-instruction"><?php _e('Total Amount (18 holes)', 'teetime-booking-pro'); ?></span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="payment-method" class="ttbp-label"><?php _e('Payment Method', 'teetime-booking-pro'); ?> </label>

                    </th>
                    <td>
                        <i><a class="ttbp_url" href="<?php echo admin_url('/edit.php?post_type=ttbp_booking&page=ttbp_payment_methods'); ?>" target="_blank"><?php _e('Choose payment method', 'teetime-booking-pro'); ?></a></i>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_opt_in_label" class="ttbp-label"><?php _e('Opt-in Text', 'teetime-booking-pro'); ?> </label>

                    </th>
                    <td>
                        <textarea type="text" name="ttbp_opt_in_label" id="ttbp_opt_in_label"><?php echo $this->opt_in_label; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3 class="ttbp_section_heading"><?php _e('Custom Pricing', 'teetime-booking-pro'); ?></h3>
                        <hr>
                        <br>
                        <button type="button" class="ttbp_button ttbp_open_modal" data-modal="ttbp_add_pricing"><?php _e('Add Pricing <i class="fa-solid fa-plus" style="font-size: 12px"></i>', 'teetime-booking-pro'); ?></button>
                        <?php if (!empty($this->custom_pricing)): ?>
                            <table class="ttbp_table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Weekday', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Booking Deposit', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Time Window', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Total Amount (9 Holes)', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Total Amount (18 Holes)', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Action', 'teetime-booking-pro'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->custom_pricing as $pricing_key => $pricing_value):
                                        $p_weekday = isset($pricing_value['weekday']) ? $pricing_value['weekday'] : '--';
                                        $p_booking_amount = isset($pricing_value['booking_amount']) ? $pricing_value['booking_amount'] : '--';
                                        $p_total_amount_9 = isset($pricing_value['total_amount_9']) ? $pricing_value['total_amount_9'] : '--';
                                        $p_total_amount_18 = isset($pricing_value['total_amount_18']) ? $pricing_value['total_amount_18'] : '--';
                                        $p_window_start = isset($pricing_value['window_start']) ? $pricing_value['window_start'] : '--';
                                        $p_window_end = isset($pricing_value['window_end']) ? $pricing_value['window_end'] : '--';
                                    ?>
                                        <tr>
                                            <td><?php echo date('l', strtotime("Sunday +{$p_weekday} days")); ?></td>
                                            <td><?php echo TTBP__price($p_booking_amount); ?></td>
                                            <td><?php echo date('g:i A', strtotime($p_window_start)) . ' - ' . date('g:i A', strtotime($p_window_end)) ?></td>
                                            <td><?php echo TTBP__price($p_total_amount_9); ?></td>
                                            <td><?php echo TTBP__price($p_total_amount_18); ?></td>
                                            <td>
                                                <button type="button" class="button button-primary ttbp_edit_pricing ttbp_open_modal" data-modal="ttbp_add_pricing" data-weekday="<?php echo $p_weekday; ?>" data-booking_amount="<?php echo $p_booking_amount; ?>" data-total_amount_9="<?php echo $p_total_amount_9; ?>" data-total_amount_18="<?php echo $p_total_amount_18; ?>" data-window_start="<?php echo $p_window_start ?>" data-window_end="<?php echo $p_window_end; ?>"><?php _e('Edit', 'teetime-booking-bro'); ?></button>
                                                <button type="button" class="button ttbp_delete_pricing" data-weekday="<?php echo $p_weekday; ?>"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </th>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3 class="ttbp_section_heading"><?php _e('Custom Timings', 'teetime-booking-pro'); ?></h3>
                        <hr>
                        <br>
                        <button type="button" class="ttbp_button ttbp_open_modal" data-modal="ttbp_add_rule"><?php _e('Add Rule <i class="fa-solid fa-plus" style="font-size: 12px"></i>', 'teetime-booking-pro'); ?></button>
                        <?php if (!empty($this->custom_slot_rules)): ?>
                            <table class="ttbp_table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Date', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Start (Morning)', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Start (Midday)', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Start (Afteroon)', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('End', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Duration', 'teetime-booking-pro'); ?></th>
                                        <th><?php _e('Action', 'teetime-booking-pro'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->custom_slot_rules as $rule_key => $rule_value):
                                        $r_start_morning = isset($rule_value['start_morning']) ? $rule_value['start_morning'] : '--';
                                        $r_start_midday = isset($rule_value['start_midday']) ? $rule_value['start_midday'] : '--';
                                        $r_start_evening = isset($rule_value['start_evening']) ? $rule_value['start_evening'] : '--';
                                        $r_end = isset($rule_value['end']) ? $rule_value['end'] : '--';
                                        $r_duration = isset($rule_value['duration']) ? $rule_value['duration'] : '--';
                                    ?>
                                        <tr>
                                            <td><?php echo $rule_key; ?></td>
                                            <td><?php echo $r_start_morning; ?></td>
                                            <td><?php echo $r_start_midday; ?></td>
                                            <td><?php echo $r_start_evening; ?></td>
                                            <td><?php echo $r_end; ?></td>
                                            <td><?php echo $r_duration; ?></td>
                                            <td>
                                                <button type="button" class="button button-primary ttbp_edit ttbp_open_modal" data-modal="ttbp_add_rule" data-date="<?php echo $rule_key; ?>" data-start_morning="<?php echo $r_start_morning; ?>" data-start_midday="<?php echo $r_start_midday; ?>" data-start_evening="<?php echo $r_start_evening; ?>" data-end="<?php echo $r_end; ?>" data-duration="<?php echo $r_duration; ?>"><?php _e('Edit', 'teetime-booking-bro'); ?></button>
                                                <button type="button" class="button ttbp_delete" data-date="<?php echo $rule_key; ?>"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </th>
                </tr>
                <tr>
                    <th colspan="2">
                        <button type="submit" class="ttbp_button"><?php _e('Save Changes', 'teetime-booking-pro'); ?></button>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>



    <?php
    /* Popup Add Rule Form */
    TTBP__get_template('admin_menu/modals/custom-timings.php');
    /* Popup Add Pricing Form */
    TTBP__get_template('admin_menu/modals/custom-pricing.php');
    ?>



</div>