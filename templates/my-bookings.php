<div class="ttbp_bookings_wrapper">
    <table class="ttbp_bookings_table">
        <thead>
            <tr>
                <th><?php _e('Date', 'ttbp'); ?></th>
                <th><?php _e('Time', 'ttbp'); ?></th>
                <th><?php _e('Players', 'ttbp'); ?></th>
                <th><?php _e('Holes', 'ttbp'); ?></th>
                <th><?php _e('Golf Course', 'ttbp'); ?></th>
                <th><?php _e('Action', 'ttbp'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking) : ?>
                <tr>
                    <td><?php echo esc_html(get_post_meta($booking->ID, 'ttbp_date', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($booking->ID, 'ttbp_time', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($booking->ID, 'ttbp_players', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($booking->ID, 'ttbp_holes', true)); ?></td>
                    <td>
                        <?php
                        $terms = get_the_terms($booking->ID, 'golf_course');
                        echo $terms && !is_wp_error($terms) ? esc_html($terms[0]->name) : '-';
                        ?>
                    </td>
                    <td>
                        <button
                            class="ttbp_button ttbp_delete_booking my-bookings"
                            data-id="<?php echo esc_attr($booking->ID); ?>">
                            <?php _e('Delete', 'ttbp'); ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>