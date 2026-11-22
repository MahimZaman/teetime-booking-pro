    <?php
    $date = date('Y-m-d');
    $custom_slots = get_option('ttbp_custom_slot_rules') ? get_option('ttbp_custom_slot_rules') : [];
    $custom_slot = isset($custom_slots[$date]) ? $custom_slots[$date] : [];
    $startTime = isset($custom_slot['start_morning']) ? $custom_slot['start_morning'] : get_option('ttbp_default_start_morning');
    $endTime = isset($custom_slot['end']) ? $custom_slot['end'] : get_option('ttbp_default_end');
    // The end time is commented on last feedback. 
    //$endTime = date('H:i', strtotime('-1 minutes', strtotime($startTime)));
    $duration = isset($custom_slot['duration']) ? $custom_slot['duration'] : get_option('ttbp_default_duration');
    $a_ins = new TTBP_Analytics();
    $analytics = $a_ins->get_analytics(date('Y-m-d'));
    ?>

    <div class="calendar-container">
        <div class="calendar-loader">
            <span class="ttbp-loader"></span>
        </div>

        <div class="calendar-header">

            <ul class="day_analytics header-start">
                <li class="da_bookings">
                    <span class="da_value"><?php echo isset($analytics['bookings']) ? $analytics['bookings'] : '--'; ?></span>
                    <span class="da_label"><?php _e('Bookings', 'teetime-booking-pro'); ?></span>
                </li>
                <li class="da_occupancy">
                    <span class="da_value"><?php echo isset($analytics['occupancy']) ? $analytics['occupancy'] . '%' : '--'; ?></span>
                    <span class="da_label"><?php _e('Occupancy', 'teetime-booking-pro'); ?></span>
                </li>
                <li class="da_noshows">
                    <span class="da_value"><?php echo isset($analytics['no_shows']) ? $analytics['no_shows'] : '--'; ?></span>
                    <span class="da_label"><?php _e('No Shows', 'teetime-booking-pro'); ?></span>
                </li>
                <li class="da_checked-in">
                    <span class="da_value"><?php echo isset($analytics['checked_in']) ? $analytics['checked_in'] : '--'; ?></span>
                    <span class="da_label"><?php _e('Checked-in', 'teetime-booking-pro'); ?></span>
                </li>
                <li class="da_revenue">
                    <span class="da_value"><?php echo isset($analytics['revenue']) ? TTBP__price($analytics['revenue']) : '--'; ?></span>
                    <span class="da_label"><?php _e('Revenue', 'teetime-booking-pro'); ?></span>
                </li>
            </ul>
            <div class="header-center">
                <div class="calendar-date-picker">
                    <div id="calendar-date-display" class="calendar-date-display">
                        <span id="calendar-date-text">Loading...</span>
                    </div>
                    <input type="hidden" name="ttbp_calendar_date" id="ttbp_calendar_date" readonly>
                </div>
            </div>
            <div class="header-end">
                <button type="button" class="ttbp_button" id="starter_btn"><a target="_blank" href="<?php echo site_url('?view=starter_sheet&date=' . date('Y-m-d')) ?>"><?php _e('Starter Sheet', 'teetime-booking-pro'); ?></a></button>
                <button type="button" id="enable_blockmode" class="ttbp_button"><?php _e('Select Block', 'teetime-booking-pro'); ?></button>
                <button type="button" id="book_block" class="ttbp_button ttbp_open_modal" data-modal="ttbp_blockbook"><?php _e('Book Block', 'teetime-booking-pro'); ?></button>
            </div>
        </div>

        <div class="calendar-nav">
            <button type="button" class="active" data-date="<?php echo date('Y-m-d') ?>"><?php echo __('Today', 'teetime-booking-pro'); ?></button>
            <button type="button" data-date="<?php echo date('Y-m-d', strtotime('+1 days')) ?>"><?php echo date('D j', strtotime('+1 days')) ?></button>
            <button type="button" data-date="<?php echo date('Y-m-d', strtotime('+2 days')) ?>"><?php echo date('D j', strtotime('+2 days')) ?></button>
            <button type="button" data-date="<?php echo date('Y-m-d', strtotime('+3 days')) ?>"><?php echo date('D j', strtotime('+3 days')) ?></button>
            <button type="button" data-date="<?php echo date('Y-m-d', strtotime('+4 days')) ?>"><?php echo date('D j', strtotime('+4 days')) ?></button>
            <button type="button" data-date="<?php echo date('Y-m-d', strtotime('+5 days')) ?>"><?php echo date('D j', strtotime('+5 days')) ?></button>
            <button type="button" data-date="<?php echo date('Y-m-d', strtotime('+6 days')) ?>"><?php echo date('D j', strtotime('+6 days')) ?></button>
        </div>

        <div class="time-slots">
            <?php
            $this->render_time_rows($startTime, $endTime, $duration, date('Y-m-d'));
            ?>
        </div>
    </div>

    <?php
    $modals = new TTBP_Modals();
    echo TTBP__modal('ttbp_entry', $modals->new_booking(), 'Booking Entry');
    echo TTBP__modal('ttbp_booking_info', $modals->update_booking(), 'Booking Info', $modals->update_booking_footer());
    echo TTBP__modal('ttbp_blockbook', $modals->blockbook(), 'Block Booking', $modals->blockbook_footer());
    ?>