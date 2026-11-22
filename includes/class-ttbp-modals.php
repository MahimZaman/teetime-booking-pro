<?php

if (!defined('ABSPATH')) return;

if (!class_exists('TTBP_Modals')) {
    class TTBP_Modals
    {
        public function blockbook()
        {

            $output = '<form id="ttbp_blockbook_form">
                <div class="ttbp_form_group">
                    <label for="ttbp_golf_course">' . __('Golf Course', 'teetime-booking-pro') . '</label>
                    <select name="ttbp_golf_course" id="ttbp_golf_course">';
            $courses = TTBP_get_courses();
            if (!empty($courses)) {
                foreach ($courses as $term_id => $name) {
                    $output .= '<option value="' . $term_id . '">' . $name . '</option>';
                }
            } else {
                $output .= '<option value="">' . __('No courses are listed', 'teetime-booking-pro') . '</option>';
            }
            $output .= '</select> 
                </div>
                <div class="ttbp_form_group">
                    <label for="ttbp_block_name">' . __('Name', 'teetime-booking-pro') . '</label>
                    <input type="text" name="ttbp_name" id="ttbp_block_name" required/>
                </div>
                <div class="ttbp_form_group">
                    <label for="ttbp_block_email">' . __('Email Address', 'teetime-booking-pro') . '</label>
                    <input type="email" name="ttbp_email" id="ttbp_block_email" required/>
                    <ul class="ttbp_autocomplete"></ul>
                </div>
                <div class="ttbp_form_group">
                    <label for="ttbp_block_phone">' . __('Phone Number', 'teetime-booking-pro') . '</label>
                    <input type="tel" name="ttbp_phone" id="ttbp_block_phone" required/>
                    <ul class="ttbp_autocomplete"></ul>
                </div>
                <div class="ttbp_form_group">
                    <label for="ttbp_block_holes">' . __('Holes', 'teetime-booking-pro') . '</label>
                    <select name="ttbp_holes" id="ttbp_block_holes">
                        <option value="18">18</option>
                        <option value="9">9</option>
                    </select>
                </div>
                <div class="ttbp_form_group">
                    <label for="ttbp_block_ride">' . __('Ride', 'teetime-booking-pro') . '</label>
                    <select name="ttbp_ride" id="ttbp_block_ride">
                        <option value="cart">' . __('Cart', 'teetime-booking-pro') . '</option>
                        <option value="walking">' . __('Walking', 'teetime-booking-pro') . '</option>
                    </select>
                </div>
                <input type="hidden" name="ttbp_block_slots" id="ttbp_block_slots">
                <input type="hidden" name="ttbp_booking_amount" id="ttbp_block_booking_amount">
                <input type="hidden" name="ttbp_total_amount" id="ttbp_block_total_amount">
                <input type="hidden" name="ttbp_per_book_price" id="ttbp_per_book_price">
                <input type="hidden" name="ttbp_discount" id="ttbp_block_discount" />
                <input type="hidden" name="ttbp_coupon_id" id="ttbp_coupon_id" />
                <input type="hidden" name="ttbp_payment_method" value="pos"/>                           
                <span class="form-status"></span>
            </form>';
            return  $output;
        }

        public function new_booking()
        {
            $modal_body = '<form class="ttbp_entry">
        <!-- Golf Course -->
        <div class="ttbp_form_group">
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
        </div>
        <div class="ttbp_form_group">
            <!-- Date -->
            <label class="ttbp_form_label">' . __('Date', 'teetime-booking-pro') . '</label>
            <input type="date" class="ttbp_form_input readonly" value="" name="ttbp_date" id="ttbp_date" required readonly />
        </div>

        <div class="ttbp_form_group">
            <!-- Time -->
            <label class="ttbp_form_label">' . __('Time Slot', 'teetime-booking-pro') . '</label>
            <input type="time" class="ttbp_form_input readonly" value="" name="ttbp_time" id="ttbp_time" required readonly />
        </div>

        <div class="ttbp_form_group">
            <!-- Players -->
            <label class="ttbp_form_label">' . __('No. of Players', 'teetime-booking-pro') . '</label>
            <select name="ttbp_players" class="ttbp_form_input" id="ttbp_players" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>
        </div>

        <div class="ttbp_form_group">
            <!-- Holes -->
            <label class="ttbp_form_label">' . __('Holes', 'teetime-booking-pro') . '</label>
            <select name="ttbp_holes" class="ttbp_form_input" id="ttbp_holes" required>
                <option value="18">18</option>
                <option value="9">9</option>
            </select>
        </div>

        <div class="ttbp_form_group">
            <!-- Rides -->
            <label class="ttbp_form_label">' . __('Ride', 'teetime-booking-pro') . '</label>
            <select name="ttbp_ride" class="ttbp_form_input" id="ttbp_ride" required>
                <option value="cart">Cart</option>
                <option value="walking">Walking</option>
            </select>
        </div>

        <div class="ttbp_form_group">
            <!-- Name -->
            <label class="ttbp_form_label">' . __('Name', 'teetime-booking-pro') . '</label>
            <input type="text" class="ttbp_form_input" value="" name="ttbp_name" id="ttbp_name" required autocomplete="off"/>
        </div>

        <div class="ttbp_form_group">
            <!-- Email -->
            <label class="ttbp_form_label">' . __('Email', 'teetime-booking-pro') . '</label>
            <input type="email" class="ttbp_form_input" value="" name="ttbp_email" id="ttbp_email" required autocomplete="off"/>
            <ul class="ttbp_autocomplete"></ul>
        </div>

        <div class="ttbp_form_group">
            <!-- Phone -->
            <label class="ttbp_form_label">' . __('Phone', 'teetime-booking-pro') . '</label>
            <input type="tel" class="ttbp_form_input" value="" name="ttbp_phone" id="ttbp_phone" required autocomplete="off"/>
            <ul class="ttbp_autocomplete"></ul>
        </div>
        
        <input type="hidden" name="ttbp_booking_id" id="ttbp_booking_id" value="">
        <input type="hidden" name="ttbp_booking_amount">
        <input type="hidden" name="ttbp_total_amount" id="ttbp_entry_total_amount">
        <input type="hidden" name="ttbp_taxable_amount" id="ttbp_entry_taxable_amount" value="' . get_option('ttbp_taxable_amount', 0) . '">
        <input type="hidden" name="ttbp_tax_rate" id="ttbp_entry_tax_rate" value="' . get_option('ttbp_cart_tax_rate', 0) . '">
        <input type="hidden" id="ttbp_total_amount_9_walking">
        <input type="hidden" id="ttbp_total_amount_9_cart">
        <input type="hidden" id="ttbp_total_amount_18_walking">
        <input type="hidden" id="ttbp_total_amount_18_cart">
        <input type="hidden" name="ttbp_payment_status" value="due">
        <input type="hidden" name="ttbp_payment_method" value="pos">
        <span class="ttbp_form_message">Processing...</span>
        <button type="submit" class="ttbp_submit_booking ttbp_button">' . __('Submit', 'teetime-booking-pro') . '</button>
    </form>';
            return $modal_body;
        }

        public function update_booking()
        {
            $delete_modal = '<form id="staff_update_booking">';
            $delete_modal .= '<table class="ttbp_booking_info">';
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
            $delete_modal .= '<td class="info_players"><select id="ttbp_slot_players" name="ttbp_slot_players"></select></td>';
            $delete_modal .= '</tr>';
            $delete_modal .= '<tr>';
            $delete_modal .= '<th>' . __('Holes', 'teetime-booking-pro') . '</th>';
            $delete_modal .= '<td class="info_holes"></td>';
            $delete_modal .= '</tr>';
            $delete_modal .= '<tr>';
            $delete_modal .= '<th>' . __('Ride', 'teetime-booking-pro') . '</th>';
            $delete_modal .= '<td class="info_ride"></td>';
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
            $delete_modal .= '<th>' . __('Payment Method', 'teetime-booking-pro') . '</th>';
            $delete_modal .= '<td class="info_method"></td>';
            $delete_modal .= '</tr>';
            $delete_modal .= '<tr>';
            $delete_modal .= '<th colspan="2">' . __('Discount Coupon', 'teetime-booking-pro') . '</th>';
            $delete_modal .= '</tr>';
            $delete_modal .= '<tr>';
            $delete_modal .= '<td colspan="2">
                                    <div class="ttbp_form_group ttbp_coupon_field">
                                        <select id="ttbp_discount_coupon">
                                            <option value="">' . __('Select a coupon', 'teetime-booking-pro') . '</option>';
            $coupons = get_posts(array(
                'post_type' => 'ttbp_coupon',
                'posts_per_page' => -1,
                'post_status' => 'public',
                'meta_query' => [
                    [
                        'key' => '_ttbp_coupon_use_limit',
                        'value' => 0,
                        'compare' => '>'
                    ]
                ],
            ));

            if (!empty($coupons)) {
                foreach ($coupons as $coupon) {
                    $coupon_id = $coupon->ID;
                    $coupon_code     = get_post_meta($coupon_id, '_ttbp_coupon_code', true);
                    $delete_modal .= '<option value="' . $coupon_code . '">' . get_the_title($coupon_id) . '</option>';
                }
            }

            $delete_modal .= '</select>
                                        <select id="ttbp_coupon_for">
                                            <option value="">Select a player</option>
                                        </select>
                                        <button type="button" class="ttbp_button ttbp_button_has_loader" id="redeem_coupon"><i class="fa-solid fa-paper-plane"></i> <span class="ttbp_button_loader"></span></button>
                                    </div>
                                    <div class="ttbp_applied_coupon">

                                    </div>
                                </td>';
            $delete_modal .= '</tr>';
            $delete_modal .= '<tr>';
            $delete_modal .= '<th colspan="2">' . __('Payment Status', 'teetime-booking-pro') . ' <span class="ttbp_update_notice"></span></th>';
            $delete_modal .= '<tr><td colspan="2" class="info_payment_status"></td></tr>';
            $delete_modal .= '</tr>';
            $delete_modal .= '<tr class="past-delete">';
            $delete_modal .= '<th colspan="2">';
            $delete_modal .= __('Notes', 'teetime-booking-pro');
            $delete_modal .= '</th>';
            $delete_modal .= '</tr>';
            $delete_modal .= '<tr class="past-delete">';
            $delete_modal .= '<td colspan="2">';
            $delete_modal .= '<div>
                                <select id="delete_reason" name="ttbp_delete_reason">
                                    <option value="">Select an option</option>
                                    <option value="no-show">No show</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="weather">Weather</option>
                                    <option value="moved">Moved</option>
                                </select>
                            </div>';
            $delete_modal .= '<div>
            <textarea rows="4" placeholder="Leave a reason of updating past entries." name="ttbp_delete_explain" id="delete_reason_explain"></textarea>
            </div>';
            $delete_modal .= '<div>
                                <input type="hidden" name="ttbp_bookingID">
                                <input class="ttbp_discount_val" type="hidden" name="ttbp_discount_player_1" id="ttbp_discount_player_1" value="0">
                                <input class="ttbp_discount_val" type="hidden" name="ttbp_discount_player_2" id="ttbp_discount_player_2" value="0">
                                <input class="ttbp_discount_val" type="hidden" name="ttbp_discount_player_3" id="ttbp_discount_player_3" value="0">
                                <input class="ttbp_discount_val" type="hidden" name="ttbp_discount_player_4" id="ttbp_discount_player_4" value="0">
                                <input type="hidden" name="ttbp_total_amount" id="ttbp_total_amount">                                
                                <input type="hidden" id="ttbp_booking_total" value="0">
                                <input type="hidden" name="ttbp_payment_method" id="ttbp_payment_method" value="pos">
                                <input type="hidden" name="ttbp_pos_object" id="ttbp_pos_object" value="">
                              </div>';
            $delete_modal .= '</td>';
            $delete_modal .= '</tr>';
            $delete_modal .= '</tbody>';
            $delete_modal .= '</table>';
            $delete_modal .= '<div class="ttbp_payment_notice">';
            $delete_modal .= get_option('ttbp_payment_notice', '');
            $delete_modal .= '</div>';
            $delete_modal .= '</form>';

            return $delete_modal;
        }

        public function update_booking_footer()
        {
            $dm_footer = '';
            $dm_footer .= '<button type="button" class="ttbp_reduce_player ttbp_button">' . __('Update', 'teetime-booking-pro') . '</button>
                    <form id="staff_delete_booking">
                        <input type="hidden" name="ttbp_bookingID">
                        <button type="submit" class="ttbp_delete_booking ttbp_button">Delete</button>
                    </form>';
            return $dm_footer;
        }

        public function blockbook_footer()
        {
            $footer = '';
            $footer .= '<button type="button" id="submit_blockbook" class="ttbp_button ttbp_button_has_loader">' . __('Book Now', 'teetime-booking-pro') . '<span class="ttbp_button_loader"></span></button>';
            $footer .= '<button type="button" id="cancel_blockbook" class="ttbp_button ttbp_danger">' . __('Cancel', 'teetime-booking-pro') . '</button>';

            return $footer;
        }
    }
}
