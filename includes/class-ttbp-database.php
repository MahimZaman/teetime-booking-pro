<?php

if (!defined('ABSPATH')) exit;

class TTBP_Database
{

    private static $table_name;

    public static function init()
    {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'tee_times';
    }

    // Create table on activation
    public static function create_tables()
    {
        global $wpdb;

        self::init(); // Set table name
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . self::$table_name . " (
            id INT NOT NULL AUTO_INCREMENT,
            date DATE NOT NULL,
            time_slot varchar(255) NOT NULL,
            max_players INT NOT NULL DEFAULT 4,
            booked_players INT NOT NULL DEFAULT 0,
            booking_id INT,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Optional helper: Insert new tee time slot
    public static function insert_slot($date, $time, $booking_id, $booked_players = 1, $max_players = 4)
    {
        global $wpdb;
        self::init();

        $wpdb->insert(
            self::$table_name,
            [
                'date'         => $date,
                'time_slot'    => $time,
                'max_players'  => $max_players,
                'booked_players' => $booked_players,
                'booking_id' => $booking_id
            ],
            ['%s', '%s', '%d', '%d', '%d']
        );
    }

    // Optional helper: Insert new tee time slot
    public static function update_slot($booking_id, $date = null, $time = null, $booked_players = null, $max_players = 4)
    {
        global $wpdb;
        self::init();

        $update_columns = [];
        $update_column_datatype = [];

        if ($date !== null) {
            $update_columns['date'] = $date;
            $update_column_datatype[] = '%s';
        }

        if ($time !== null) {
            $update_columns['time_slot'] = $time;
            $update_column_datatype[] = '%s';
        }

        if ($booked_players !== null) {
            $update_columns['booked_players'] = $booked_players;
            $update_column_datatype[] = '%d';
        }

        $update_columns['max_players'] = $max_players;
        $update_column_datatype[] = '%d';

        $wpdb->update(
            self::$table_name,
            $update_columns,
            [
                'booking_id' => $booking_id,
            ],
            $update_column_datatype,
            ['%d']
        );
    }

    // Optional helper: Insert new tee time slot
    public static function delete_slot($booking_id)
    {
        global $wpdb;
        self::init();

        $wpdb->delete(
            self::$table_name,
            [
                'booking_id' => $booking_id
            ],
            ['%d']
        );
    }

    // Optional helper: Get available slots by date
    public static function get_available_slots($date, $time_slot)
    {
        global $wpdb;
        self::init();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SUM(booked_players) as total_booked 
                 FROM " . self::$table_name . "
                 WHERE date = %s AND time_slot = %s",
                $date,
                $time_slot
            ),
            ARRAY_A
        );
    }
}
