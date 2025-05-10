<?php

if (!defined('ABSPATH')) exit;

class TTBP_Shortcodes
{

  public function register_shortcodes()
  {
    add_shortcode('ttbp_booking_form', [$this, 'render_booking_form']);

    add_shortcode('tee_time_calendar', [$this, 'ttb_render_tee_time_calendar']);
    add_shortcode('ttbp_my_bookings', [$this, 'ttbp_my_bookings_shortcode']);
  }

  public function render_booking_form()
  {
    ob_start();
    include TTBP_PATH . 'includes/booking-form.php';
    return ob_get_clean();
  }

  public function ttbp_my_bookings_shortcode()
  {
    if (!is_user_logged_in()) {
      return '<p>' . __('Please log in to view your bookings.', 'ttbp') . '</p>';
    }

    $user_id = get_current_user_id();
    $bookings = get_posts([
      'post_type'   => 'ttbp_booking',
      'post_status' => 'publish',
      'author'      => $user_id,
      'numberposts' => -1,
    ]);

    if (empty($bookings)) {
      return '<p>' . __('No bookings found.', 'teetime-booking-pro') . '</p>';
    }

    ob_start();
    require_once TTBP_PATH . 'templates/my-bookings.php';
    return ob_get_clean();
  }


  public function render_time_rows($start_hour, $end_hour, $interval_minutes, $date)
  {
    $current = strtotime($start_hour);
    $end = strtotime($end_hour);
    $output = '';
    $last_hour = -1;

    $booking_amount = get_option('ttbp_booking_amount', 0);
    $total_pay_9 = get_option('ttbp_total_amount_9', 0);
    $total_pay_18 = get_option('ttbp_total_amount_18', 0);
    $pricing_rules = get_option('ttbp_custom_pricing') ? get_option('ttbp_custom_pricing') : [];
    $rule = [];
    $weekDay = date('N', strtotime($date));

    if (isset($pricing_rules[$weekDay])) {
      $rule = $pricing_rules[$weekDay];
    }

    $bookings = get_posts([
      'post_type' => 'ttbp_booking',
      'posts_per_page' => -1,
      'meta_query' => array(
        array(
          'key' => 'ttbp_date',
          'value' => $date,
          'compare' => '='
        ),
      ),
      'order' => 'ASC',
      'orderby' => 'date'
    ]);

    $booked_slots = [];

    if (!empty($bookings)) {
      foreach ($bookings as $booking) {
        $booking_id = $booking->ID;
        $slot = get_post_meta($booking_id, 'ttbp_time', true);
        $courses = get_the_terms($booking_id, 'golf_course');
        $course_id = 0;

        if (!empty($courses)) {
          $course = $courses[0]->name;
          $course_id = $courses[0]->term_id;
        }

        $booked_slots[strtotime($slot)][] = array(
          'name' => get_post_meta($booking_id, 'ttbp_name', true),
          'players' => get_post_meta($booking_id, 'ttbp_players', true),
          'holes' => get_post_meta($booking_id, 'ttbp_holes', true),
          'email' => get_post_meta($booking_id, 'ttbp_email', true),
          'phone' => get_post_meta($booking_id, 'ttbp_phone', true),
          'booking_id' => $booking_id,
          'golf_course' => $course,
          'course_id' => $course_id,
          'date' => get_post_meta($booking_id, 'ttbp_date', true),
          'time' => get_post_meta($booking_id, 'ttbp_time', true),
        );
      }
    }

    if ($current <= $end) {
      while ($current <= $end) {
        $hour = (int)date('G', $current);
        $minutes = (int)date('i', $current);

        if (!empty($rule)) {
          $window_start_timestamp = strtotime($rule['window_start']);
          $window_end_timestamp = strtotime($rule['window_end']);
          if (($current >= $window_start_timestamp) && ($current <= $window_end_timestamp)) {
            $booking_amount = $rule['booking_amount'];
            $total_pay_9 = $rule['total_amount_9'];
            $total_pay_18 = $rule['total_amount_18'];
          } else {
            $booking_amount = get_option('ttbp_booking_amount', 0);
            $total_pay_9 = get_option('ttbp_total_amount_9', 0);
            $total_pay_18 = get_option('ttbp_total_amount_18', 0);
          }
        }

        if ($hour !== $last_hour) {
          $output .= '<div class="time-row time-separator">
                            <div class="time-label">' . date('g:00 A', $current) . '</div>
                            <div class="slot-separator"></div>
                          </div>';
          $last_hour = $hour;
        }

        if (isset($booked_slots[$current])) {
          $output .= $this->render_booked_slots($booked_slots, $booking_amount, $current, $date, $total_pay_9, $total_pay_18);
        } else {
          $output .= $this->render_empty_slots($booking_amount, $current, $date, $total_pay_9, $total_pay_18);
        }

        $current = strtotime("+{$interval_minutes} minutes", $current);
      }
    } else {
      $new_end = strtotime('23:59');

      while ($current <= $new_end) {
        $hour = (int)date('G', $current);
        $minutes = (int)date('i', $current);

        if (!empty($rule)) {
          $window_start_timestamp = strtotime($rule['window_start']);
          $window_end_timestamp = strtotime($rule['window_end']);
          if (($current >= $window_start_timestamp) && ($current <= $window_end_timestamp)) {
            $booking_amount = $rule['booking_amount'];
            $total_pay_9 = $rule['total_amount_9'];
            $total_pay_18 = $rule['total_amount_18'];
          } else {
            $booking_amount = get_option('ttbp_booking_amount', 0);
            $total_pay_9 = get_option('ttbp_total_amount_9', 0);
            $total_pay_18 = get_option('ttbp_total_amount_18', 0);
          }
        }

        if ($hour !== $last_hour) {
          $output .= '<div class="time-row time-separator">
                            <div class="time-label">' . date('g:00 A', $current) . '</div>
                            <div class="slot-separator"></div>
                          </div>';
          $last_hour = $hour;
        }

        if (isset($booked_slots[$current])) {
          $output .= $this->render_booked_slots($booked_slots, $booking_amount, $current, $date, $total_pay_9, $total_pay_18);
        } else {
          $output .= $this->render_empty_slots($booking_amount, $current, $date, $total_pay_9, $total_pay_18);
        }

        $current = strtotime("+{$interval_minutes} minutes", $current);
      }

      $new_current = strtotime(date('H:i', $current));

      while ($new_current <= $end) {
        $hour = (int)date('G', $new_current);
        $minutes = (int)date('i', $new_current);

        if (!empty($rule)) {
          $window_start_timestamp = strtotime($rule['window_start']);
          $window_end_timestamp = strtotime($rule['window_end']);
          if (($new_current >= $window_start_timestamp) && ($new_current <= $window_end_timestamp)) {
            $booking_amount = $rule['booking_amount'];
            $total_pay_9 = $rule['total_amount_9'];
            $total_pay_18 = $rule['total_amount_18'];
          } else {
            $booking_amount = get_option('ttbp_booking_amount', 0);
            $total_pay_9 = get_option('ttbp_total_amount_9', 0);
            $total_pay_18 = get_option('ttbp_total_amount_18', 0);
          }
        }

        if ($hour !== $last_hour) {
          $output .= '<div class="time-row time-separator">
                            <div class="time-label">' . date('g:00 A', $new_current) . '</div>
                            <div class="slot-separator"></div>
                          </div>';
          $last_hour = $hour;
        }

        if (isset($booked_slots[$new_current])) {
          $output .= $this->render_booked_slots($booked_slots, $booking_amount, $new_current, $date, $total_pay_9, $total_pay_18);
        } else {
          $output .= $this->render_empty_slots($booking_amount, $new_current, $date, $total_pay_9, $total_pay_18);
        }

        $new_current = strtotime("+{$interval_minutes} minutes", $new_current);
      }
    }


    echo $output;
  }

  function render_booked_slots($booked_slots, $booking_amount, $current, $date, $total_pay_9, $total_pay_18)
  {
    $output = '';
    $output .= '<div class="time-row">
          <div class="time-label">' . date('g:i A', $current) . '</div>
          <div class="price-label">' . TTBP__price($booking_amount) . '</div>
          <div class="booking-slot">';
    $player_count = 0;
    $today = date('Y-m-d');
    $format_date = date('Y-m-d', strtotime($date));
    $lead_time = get_option('ttbp_lead_time');
    $difference = abs($current - time()) / 60;
    foreach ($booked_slots[$current] as $slot_key => $slot_value) {
      $players = $slot_value['players'];
      $holes = $slot_value['holes'];
      $name = $slot_value['name'];
      $email = $slot_value['email'];
      $phone = $slot_value['phone'];
      $booking_id = $slot_value['booking_id'];
      $course_id = $slot_value['course_id'];
      $course_name = $slot_value['golf_course'];
      $slot_date = $slot_value['date'];
      $slot_time = date('H:i', strtotime($slot_value['time']));
      $slot_diff = abs(strtotime($slot_value['time']) - time()) / 60;
      $payment = array(
        'payment_status_1' => get_post_meta($booking_id, 'ttbp_payment_status_1', true),
        'payment_status_2' => get_post_meta($booking_id, 'ttbp_payment_status_2', true),
        'payment_status_3' => get_post_meta($booking_id, 'ttbp_payment_status_3', true),
        'payment_status_4' => get_post_meta($booking_id, 'ttbp_payment_status_4', true),
        'player_name_1' => get_post_meta($booking_id, 'ttbp_player_name_1', true),
        'player_name_2' => get_post_meta($booking_id, 'ttbp_player_name_2', true),
        'player_name_3' => get_post_meta($booking_id, 'ttbp_player_name_3', true),
        'player_name_4' => get_post_meta($booking_id, 'ttbp_player_name_4', true),
      );
      $payment_json = htmlspecialchars(json_encode($payment), ENT_QUOTES, 'UTF-8');

      if ($format_date === $today) {
        for ($partial = 1; $partial <= intval($players); $partial++) {
          $icon = isset($payment['payment_status_' . $partial]) && $payment['payment_status_' . $partial] == 'paid' ? '<i class="fa-solid fa-circle-dollar-to-slot" style="color: #FFD700;"></i>' : '<i class="fa-regular fa-circle"></i>';
          $name_payee = isset($payment['player_name_' . $partial]) && $payment['player_name_' . $partial] !== '' ? $payment['player_name_' . $partial] : $name;
          if ((time() < strtotime($slot_value['time'])) && ($slot_diff >= $lead_time)) {
            $output .= '<div class="slot ttbp_open_modal partial booked_' . $slot_key . '" data-payment="' . $payment_json . '" data-modal="ttbp_booking_info" data-date="' . $slot_date . '" data-time="' . $slot_time . '" data-holes="' . $holes . '" data-players="' . $players . '" data-name="' . $name . '" data-payment_status="' . get_post_meta($booking_id, 'ttbp_payment_status', true) . '" data-booking_amount="' . get_post_meta($booking_id, 'ttbp_booking_amount', true) . '" data-total_amount="' . get_post_meta($booking_id, 'ttbp_total_amount', true) . '" data-booking_id="' . $booking_id . '" data-email="' . $email . '" data-course_name="' . $course_name . '" data-phone="' . $phone . '" data-course="' . $course_id . '"><span>' . $icon . ' ' . $name_payee . ' (' . $holes . ' holes)</span></div>';
          } else {
            $output .= '<div class="slot partial booked_' . $slot_key . '" data-date="' . $slot_date . '" data-time="' . $slot_time . '" data-holes="' . $holes . '" data-players="' . $players . '" data-name="' . $name . '" data-booking_id="' . $booking_id . '" data-email="' . $email . '" data-course_name="' . $course_name . '" data-phone="' . $phone . '" data-course="' . $course_id . '"><span>' . $icon . ' ' . $name_payee . ' (' . $holes . ' holes)</span></div>';
          }
        }
      } else {
        for ($partial = 1; $partial <= intval($players); $partial++) {
          $icon = isset($payment['payment_status_' . $partial]) && $payment['payment_status_' . $partial] == 'paid' ? '<i class="fa-solid fa-circle-dollar-to-slot" style="color: #FFD700;"></i>' : '<i class="fa-regular fa-circle"></i>';
          $name_payee = isset($payment['player_name_' . $partial]) && $payment['player_name_' . $partial] !== '' ? $payment['player_name_' . $partial] : $name;
          $output .= '<div class="slot ttbp_open_modal partial booked_' . $slot_key . '" data-payment="' . $payment_json . '" data-modal="ttbp_booking_info" data-date="' . $slot_date . '" data-time="' . $slot_time . '" data-holes="' . $holes . '" data-players="' . $players . '" data-name="' . $name . '" data-payment_status="' . get_post_meta($booking_id, 'ttbp_payment_status', true) . '" data-booking_amount="' . get_post_meta($booking_id, 'ttbp_booking_amount', true) . '" data-total_amount="' . get_post_meta($booking_id, 'ttbp_total_amount', true) . '" data-booking_id="' . $booking_id . '" data-email="' . $email . '" data-course_name="' . $course_name . '" data-phone="' . $phone . '" data-course="' . $course_id . '"><span>' . $icon . ' ' . $name_payee . ' (' . $holes . ' holes)</span></div>';
        }
      }
      $player_count += intval($players);
    }

    if ($format_date === $today) {
      for ($empty = 1; $empty <= (4 - intval($player_count)); $empty++) {
        if ((time() < $current) && ($difference >= $lead_time)) {
          $output .= '<div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="' . (4 - intval($player_count)) . '" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>';
        } else {
          $output .= '<div class="slot empty ttbp_not_allowed" data-players="' . (4 - intval($player_count)) . '" data-date="' . $date . '" data-time="' . date('H:i', $current) . '"><i class="fa-solid fa-ban"></i></div>';
        }
      }
    } else {
      for ($empty = 1; $empty <= (4 - intval($player_count)); $empty++) {
        $output .= '<div class="slot empty ttbp_open_modal" data-players="' . (4 - intval($player_count)) . '" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>';
      }
    }
    $output .= '</div></div>';

    return $output;
  }

  function render_empty_slots($booking_amount, $current, $date, $total_pay_9, $total_pay_18)
  {
    $output = '';
    $today = date('Y-m-d');
    $format_date = date('Y-m-d', strtotime($date));
    $lead_time = get_option('ttbp_lead_time');
    $difference = abs($current - time()) / 60;

    if ($format_date === $today) {
      if ((time() < $current) && ($difference >= $lead_time)) {
        $output .= '<div class="time-row">
        <div class="time-label">' . date('g:i A', $current) . '</div>
        <div class="price-label empty-price">' . TTBP__price($booking_amount) . '</div>
        <div class="booking-slot">
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
        </div>
      </div>';
      } else {
        $output .= '<div class="time-row">
        <div class="time-label">' . date('g:i A', $current) . '</div>
        <div class="price-label empty-price">' . TTBP__price($booking_amount) . '</div>
        <div class="booking-slot">
          <div class="slot empty ttbp_not_allowed" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" ><i class="fa-solid fa-ban"></i></div>
          <div class="slot empty ttbp_not_allowed" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" ><i class="fa-solid fa-ban"></i></div>
          <div class="slot empty ttbp_not_allowed" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" ><i class="fa-solid fa-ban"></i></div>
          <div class="slot empty ttbp_not_allowed" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" ><i class="fa-solid fa-ban"></i></div>
        </div>
      </div>';
      }
    } else {
      $output .= '<div class="time-row">
        <div class="time-label">' . date('g:i A', $current) . '</div>
        <div class="price-label empty-price">' . TTBP__price($booking_amount) . '</div>
        <div class="booking-slot">
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
          <div class="slot empty ttbp_open_modal" data-booking_amount="' . $booking_amount . '" data-total_pay_9="' . $total_pay_9 . '" data-total_pay_18="' . $total_pay_18 . '" data-players="4" data-date="' . $date . '" data-time="' . date('H:i', $current) . '" data-modal="ttbp_entry"><i class="fa-solid fa-circle-plus"></i></div>
        </div>
      </div>';
    }

    return $output;
  }


  function ttb_render_tee_time_calendar()
  {
    $is_staff = get_user_meta(get_current_user_id(), 'ttbp_is_staff', true);
    if (!$is_staff && !current_user_can('manage_options')) {
      echo '<p>' . __('You are not allowed to access the calendar. Only for the staff.', 'ttbp') . '</p>';
      return;
    }
    ob_start();
    require_once TTBP_PATH . 'templates/calendar-view.php';
    return ob_get_clean();
  }
}
