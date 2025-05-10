    <?php
    $date = date('Y-m-d');
    $custom_slots = get_option('ttbp_custom_slot_rules') ? get_option('ttbp_custom_slot_rules') : [];
    $custom_slot = isset($custom_slots[$date]) ? $custom_slots[$date] : [];
    $startTime = isset($custom_slot['start_morning']) ? $custom_slot['start_morning'] : get_option('ttbp_default_start_morning');
    $endTime = isset($custom_slot['end']) ? $custom_slot['end'] : get_option('ttbp_default_end');
    // The end time is commented on last feedback. 
    //$endTime = date('H:i', strtotime('-1 minutes', strtotime($startTime)));
    $duration = isset($custom_slot['duration']) ? $custom_slot['duration'] : get_option('ttbp_default_duration');
    ?>

    <div class="calendar-container">
        <div class="calendar-loader">
            <span class="ttbp-loader"></span>
        </div>

        <div class="calendar-header">
            <div class="header-right">
                <div class="calendar-date-picker">
                    <div id="calendar-date-display" class="calendar-date-display">
                        <span id="calendar-date-text">Loading...</span>
                    </div>
                    <input type="hidden" name="ttbp_calendar_date" id="ttbp_calendar_date" readonly>
                </div>
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

    $modal_body = '<form class="ttbp_entry">
        <!-- Golf Course -->
        <label class="ttbp_form_label">' . __('Golf Course', 'teetime-booking-pro') . '</label>
        <select type="text" id="ttbp_golf_course" name="ttbp_golf_course" class="ttbp_form_input" required>';
    $golf_courses = get_terms('golf_course', array(
        'hide_empty' => 0,
    ));
    if (!empty($golf_courses)) {
        foreach ($golf_courses as $golf_course) {
            $modal_body .= '<option value="' . $golf_course->term_id . '">' . __($golf_course->name, 'teetime-booking-pro') . '</option>';
        }
    } else {
        $modal_body .= '<option value="">No golf course available</option>';
    }
    $modal_body .= '</select>
        <!-- Date -->
        <label class="ttbp_form_label">' . __('Date', 'teetime-booking-pro') . '</label>
        <input type="date" class="ttbp_form_input readonly" value="" name="ttbp_date" id="ttbp_date" required readonly />

        <!-- Time -->
        <label class="ttbp_form_label">' . __('Time Slot', 'teetime-booking-pro') . '</label>
        <input type="time" class="ttbp_form_input readonly" value="" name="ttbp_time" id="ttbp_time" required readonly />

        <!-- Players -->
        <label class="ttbp_form_label">' . __('No. of Players', 'teetime-booking-pro') . '</label>
        <select name="ttbp_players" class="ttbp_form_input" id="ttbp_players" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select>

        <!-- Holes -->
        <label class="ttbp_form_label">' . __('Holes', 'teetime-booking-pro') . '</label>
        <select name="ttbp_holes" class="ttbp_form_input" id="ttbp_holes" required>
            <option value="9">9</option>
            <option value="18">18</option>
        </select>

        <!-- Name -->
        <label class="ttbp_form_label">' . __('Name', 'teetime-booking-pro') . '</label>
        <input type="text" class="ttbp_form_input" value="" name="ttbp_name" id="ttbp_name" required />

        <!-- Email -->
        <label class="ttbp_form_label">' . __('Email', 'teetime-booking-pro') . '</label>
        <input type="email" class="ttbp_form_input" value="" name="ttbp_email" id="ttbp_email" required />

        <!-- Phone -->
        <label class="ttbp_form_label">' . __('Phone', 'teetime-booking-pro') . '</label>
        <input type="tel" class="ttbp_form_input" value="" name="ttbp_phone" id="ttbp_phone" required />
        
        <input type="hidden" name="ttbp_booking_id" id="ttbp_booking_id" value="">
        <input type="hidden" name="ttbp_booking_amount">
        <input type="hidden" name="ttbp_total_amount">
        <input type="hidden" name="ttbp_payment_method" value="offline">
        <input type="hidden" name="ttbp_payment_status" value="due">

        <span class="ttbp_form_message">Processing...</span>
        <button type="submit" class="ttbp_submit_booking ttbp_button">' . __('Submit', 'teetime-booking-pro') . '</button>
    </form>';

    $delete_modal = '<table class="ttbp_booking_info">';
    $delete_modal .= '<tbody>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('Golf Course', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_golf_course"></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('Date', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_date"></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('Time Slot', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_time"></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('No. of players', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_players"><select id="ttbp_slot_players"></select></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('Holes', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_holes"></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('Name', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_name"></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('Email', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_email"></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th>' . __('Phone', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<td class="info_phone"></td>';
    $delete_modal .= '</tr>';
    $delete_modal .= '<tr>';
    $delete_modal .= '<th colspan="2">' . __('Payment Status', 'teetime-booking-pro') . '</th>';
    $delete_modal .= '<tr><td colspan="2" class="info_payment_status"></td></tr>';
    $delete_modal .= '</tr>';
    $delete_modal .= '</tbody>';
    $delete_modal .= '</table>';
    $delete_modal .= '<div class="ttbp_payment_notice">';
    $delete_modal .= get_option('ttbp_payment_notice', '');
    $delete_modal .= '</div>';

    $dm_footer = '';
    $dm_footer .= '<form id="staff_update_booking">
                        <input type="hidden" name="ttbp_updated_player">
                        <input type="hidden" name="ttbp_bookingID">
                        <div class="ttbp_hidden_payment_fields"></div>
                        <button type="submit" class="ttbp_reduce_player ttbp_button">
                        Update </button>
                    </form>
                    <form id="staff_delete_booking">
                        <input type="hidden" name="ttbp_bookingID">
                        <button type="submit" class="ttbp_delete_booking ttbp_button">Delete</button>
                    </form>';

    echo TTBP__modal('ttbp_entry', $modal_body, 'Booking Entry');
    echo TTBP__modal('ttbp_booking_info', $delete_modal, 'Booking Info', $dm_footer);
