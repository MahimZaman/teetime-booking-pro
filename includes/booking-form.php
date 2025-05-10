<?php if (!defined('ABSPATH')) exit;

$query_booking_id = isset($_GET['booking_id']) ? sanitize_text_field($_GET['booking_id']) : false;
$query_confirmation = isset($_GET['booking_confirmation']) ? sanitize_text_field($_GET['booking_confirmation']) : false;

?>

<?php if ($query_booking_id && $query_confirmation): ?>
    <?php require_once TTBP_PATH . 'templates/booking_confirmation.php' ?>
<?php else: ?>

    <form id="ttbp-booking-form" class="ttbp-form">
        <h3><?php _e('Book Your Tee Time', 'teetime-booking-pro'); ?></h3>

        <div class="ttbp_booking_step" id="ttbp_step_1">
            <!-- Golf Course -->
            <label for="ttbp_golf_course" class="ttbp_booking_form_label"><?php _e('Select Golf Course', 'teetime-booking-pro'); ?></label>

            <select type="text" id="ttbp_golf_course" name="ttbp_golf_course" required>
                <?php
                $golf_courses = get_terms('golf_course', array(
                    'hide_empty' => 0,
                ));
                if (!empty($golf_courses)) {
                    foreach ($golf_courses as $golf_course) {
                        echo '<option value="' . $golf_course->term_id . '">' . __($golf_course->name, 'teetime-booking-pro') . '</option>';
                    }
                } else {
                    echo '<option value="">No golf course available</option>';
                }
                ?>
            </select>

            <!-- Date Picker -->
            <div class="date-picker">
                <button type="button" class="date_nav" id="prev-date"><i class="fa-solid fa-chevron-left"></i></button>

                <div id="date-display" class="date-display">
                    <span id="date-text">Loading...</span>
                </div>
                <input type="hidden" name="ttbp_date" id="ttbp_date">
                <button type="button" class="date_nav" id="next-date"><i class="fa-solid fa-chevron-right"></i></button>
            </div>

            <!-- Time of the date -->
            <div class="ttbp_tod">
                <label for="morning" class="ttbp_tod_btn">
                    <input type="radio" name="ttbp_tod" id="morning" value="morning">
                    <span><?php _e('Morning', 'teetime-booking-pro'); ?></span>
                </label>
                <label for="midday" class="ttbp_tod_btn">
                    <input type="radio" name="ttbp_tod" id="midday" value="midday">
                    <span><?php _e('Midday', 'teetime-booking-pro'); ?></span>
                </label>
                <label for="evening" class="ttbp_tod_btn">
                    <input type="radio" name="ttbp_tod" id="evening" value="evening">
                    <span><?php _e('Afternoon', 'teetime-booking-pro'); ?></span>
                </label>
            </div>

            <!-- Players -->
            <label for="ttbp_players" class="ttbp_booking_form_label"><?php _e('Players', 'teetime-booking-pro'); ?></label>
            <div class="ttbp_players">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <label for="ttbp_player_<?php echo $i; ?>" class="ttbp_player_btn">
                        <input type="radio" name="ttbp_players" id="ttbp_player_<?php echo $i ?>" value="<?php echo $i; ?>">
                        <span><?php echo $i; ?></span>
                    </label>
                <?php endfor; ?>
            </div>

            <!-- Holes -->
            <div class="ttbp_holes">
                <label for="ttbp_hole_9" class="ttbp_hole_btn">
                    <input type="radio" name="ttbp_holes" id="ttbp_hole_9" value="9">
                    <span>9 <?php _e('Holes', 'teetime-booking-pro'); ?></span>
                </label>
                <label for="ttbp_hole_18" class="ttbp_hole_btn">
                    <input type="radio" name="ttbp_holes" id="ttbp_hole_18" value="18">
                    <span>18 <?php _e('Holes', 'teetime-booking-pro'); ?></span>
                </label>
            </div>

            <!-- Tee Time Dropdown -->
            <label class="ttbp_booking_form_label"><?php _e('Select your slot', 'teetime-booking-pro'); ?></label>
            <div for class="ttbp_slots">
                <p class="ttbp_placeholder">Select options to get slots</p>
            </div>
        </div>

        <div class="ttbp_booking_step" id="ttbp_step_2">
            <div class="ttbp_checkout">
                <!-- Billing Information -->
                <div id="ttbp_billing_form">
                    <button type="button" id="ttbp_previous"><i class="fa-solid fa-angle-left"></i> <?php _e('Previous', 'teetime-booking-pro'); ?></button>
                    <div class="ttbp_section_heading">
                        <h2><?php _e('Customer Information', 'teetime-booking-pro'); ?></h2>
                        <span class="ttbp_line"></span>
                    </div>
                    <div class="ttbp_form_group">
                        <label class="ttbp_label"><?php _e('Name', 'teetime-booking-pro'); ?> <span class="ttbp-required">*</span></label>
                        <input type="text" name="ttbp_name" id="ttbp_name" class="ttbp_input_text" required>
                    </div>
                    <div class="ttbp_form_group">
                        <label class="ttbp_label"><?php _e('Email', 'teetime-booking-pro'); ?> <span class="ttbp-required">*</span></label>
                        <input type="email" name="ttbp_email" id="ttbp_email" class="ttbp_input_text" required>
                    </div>
                    <div class="ttbp_form_group">
                        <label class="ttbp_label"><?php _e('Phone Number', 'teetime-booking-pro'); ?> <span class="ttbp-required">*</span></label>
                        <input type="tel" name="ttbp_phone" id="ttbp_phone" class="ttbp_input_text" required>
                    </div>
                    <input type="hidden" name="ttbp_booking_amount" id="ttbp_booking_amount" />
                    <input type="hidden" name="ttbp_total_amount" id="ttbp_total_amount" />
                    <input type="hidden" name="ttbp_payment_status" id="ttbp_payment_status" value="due" />


                    <!-- Payment Methods -->
                    <div class="ttbp_section_heading" style="margin-top: 20px;">
                        <h2><?php _e('Payment Methods', 'teetime-booking-pro'); ?></h2>
                        <span class="ttbp_line"></span>
                    </div>
                    <div id="ttbp_payment_methods">
                        <?php
                        $offline = get_option('ttbp_offline_stat');
                        $clover = get_option('ttbp_clover_stat');
                        ?>
                        <?php if ($offline === 'enable'): ?>
                            <div class="ttbp_payment_method">
                                <div class="ttbp_payment_tab">
                                    <input type="radio" id="payment_method_offline" name="ttbp_payment_method" class="ttbp_method_select" value="offline" />
                                    <label for="payment_method_offline"><?php _e(get_option('ttbp_offline_pay_label', 'Credit/Debit'), 'teetime-booking-pro'); ?></label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($clover === 'enable'): ?>
                            <div class="ttbp_payment_method">
                                <div class="ttbp_payment_tab">
                                    <input type="radio" name="ttbp_payment_method" id="payment_method_clover" class="ttbp_method_select" value="clover" />
                                    <label for="payment_method_clover"><?php _e(get_option('ttbp_clover_pay_label', 'Credit/Debit'), 'teetime-booking-pro'); ?></label>
                                </div>

                                <!-- <div id="payment-request-button"></div> -->
                                <div class="ttbp_payment_content" data-tab="payment_method_clover">
                                    <div class="ttbp_card_fields">
                                        <div class="ttbp_clover_form_row">
                                            <div id="ttbp-card-number"></div>
                                            <div class="ttbp-card-errors" id="ttbp-card-number-errors" role="alert"></div>
                                        </div>
                                        <div class="ttbp_clover_form_row">
                                            <div id="ttbp-card-date"></div>
                                            <div class="ttbp-card-errors" id="ttbp-card-date-errors" role="alert"></div>
                                        </div>
                                        <div class="ttbp_clover_form_row">
                                            <div id="ttbp-card-cvv"></div>
                                            <div class="ttbp-card-errors" id="ttbp-card-cvv-errors" role="alert"></div>
                                        </div>
                                        <div class="ttbp_clover_form_row">
                                            <div id="ttbp-card-postcode"></div>
                                            <div class="ttbp-card-errors" id="ttbp-card-postcode-errors" role="alert"></div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="ttbp_clover_token" id="ttbp_clover_token">
                                    <div id="ttbp-clover-response" role="alert"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Booking Review -->
                <div class="ttbp_booking_summary">
                    <div class="" id="ttbp_booking_review">
                        <div class="ttbp_loader_placeholder">
                            <span class="ttbp_loader_bar" style="width: 100%"></span>
                            <span class="ttbp_loader_bar" style="width: 80%"></span>
                            <span class="ttbp_loader_bar" style="width: 60%"></span>
                        </div>
                    </div>
                    <label for="ttbp_marketing" class="ttbp_marketing">
                        <input type="checkbox" name="ttbp_marketing" id="ttbp_marketing" value="yes" class="ttbp_marketing" />
                        <span><?php _e(get_option('ttbp_opt_in_label'), 'teetime-booking-pro'); ?></span>
                    </label>
                    <div id="ttbp_payment_processing" class="status-container status-processing">
                        <div class="status-icon">
                            <div class="processing-spinner"></div>
                        </div>
                        <div class="status-message">Processing your booking request...</div>
                    </div>
                    <!-- Result -->
                    <div class="status-container status-error" id="ttbp_error">
                        <div class="status-message"></div>
                    </div>
                    <button type="submit" class="ttbp_booking_submit"><?php _e('Book Now', 'teetime-booking-pro'); ?></button>
                </div>
            </div>


        </div>

    </form>

<?php endif; ?>