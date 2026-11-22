<?php

if (!defined('ABSPATH')) exit;

class TTBP_CPT
{

    public function register_coupons_post_type()
    {
        $labels = [
            'name'               => __('Coupons', 'teetime-booking-pro'),
            'singular_name'      => __('Coupon', 'teetime-booking-pro'),
            'menu_name'          => __('Coupon', 'teetime-booking-pro'),
            'name_admin_bar'     => __('Coupon', 'teetime-booking-pro'),
            'add_new'            => __('Add New', 'teetime-booking-pro'),
            'add_new_item'       => __('Add New Coupon', 'teetime-booking-pro'),
            'edit_item'          => __('Edit Coupon', 'teetime-booking-pro'),
            'new_item'           => __('New Coupon', 'teetime-booking-pro'),
            'view_item'          => __('View Coupon', 'teetime-booking-pro'),
            'all_items'          => __('All Coupon', 'teetime-booking-pro'),
            'search_items'       => __('Search Coupon', 'teetime-booking-pro'),
            'not_found'          => __('No Coupon found.', 'teetime-booking-pro'),
            'not_found_in_trash' => __('No Coupon found in Trash.', 'teetime-booking-pro')
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'ttbp-coupon'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => ['title']
        ];

        register_post_type('ttbp_coupon', $args);
    }

    public function register_post_type()
    {
        $labels = [
            'name'               => __('Bookings', 'teetime-booking-pro'),
            'singular_name'      => __('Booking', 'teetime-booking-pro'),
            'menu_name'          => __('Tee Time Bookings', 'teetime-booking-pro'),
            'name_admin_bar'     => __('Booking', 'teetime-booking-pro'),
            'add_new'            => __('Add New', 'teetime-booking-pro'),
            'add_new_item'       => __('Add New Booking', 'teetime-booking-pro'),
            'edit_item'          => __('Edit Booking', 'teetime-booking-pro'),
            'new_item'           => __('New Booking', 'teetime-booking-pro'),
            'view_item'          => __('View Booking', 'teetime-booking-pro'),
            'all_items'          => __('All Bookings', 'teetime-booking-pro'),
            'search_items'       => __('Search Bookings', 'teetime-booking-pro'),
            'not_found'          => __('No bookings found.', 'teetime-booking-pro'),
            'not_found_in_trash' => __('No bookings found in Trash.', 'teetime-booking-pro')
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-calendar-alt',
            'query_var'          => true,
            'rewrite'            => ['slug' => 'ttbp-booking'],
            'capability_type'    => 'post',
            'capabilities' => array(
                'create_posts' => 'do_not_allow',
            ),
            'map_meta_cap' => true,
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => ['title']
        ];

        register_post_type('ttbp_booking', $args);
    }

    public function register_taxonomy()
    {
        $labels = [
            'name'              => _x('Golf Courses', 'taxonomy general name', 'teetime-booking-pro'),
            'singular_name'     => _x('Golf Course', 'taxonomy singular name', 'teetime-booking-pro'),
            'search_items'      => __('Search Golf Courses', 'teetime-booking-pro'),
            'all_items'         => __('All Golf Courses', 'teetime-booking-pro'),
            'parent_item'       => __('Parent Golf Course', 'teetime-booking-pro'),
            'parent_item_colon' => __('Parent Golf Course:', 'teetime-booking-pro'),
            'edit_item'         => __('Edit Golf Course', 'teetime-booking-pro'),
            'update_item'       => __('Update Golf Course', 'teetime-booking-pro'),
            'add_new_item'      => __('Add New Golf Course', 'teetime-booking-pro'),
            'new_item_name'     => __('New Golf Course Name', 'teetime-booking-pro'),
            'menu_name'         => __('Golf Courses', 'teetime-booking-pro'),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'golf-course'],
            'show_in_rest'      => true,
        ];

        register_taxonomy('golf_course', ['ttbp_booking'], $args);
    }
}
