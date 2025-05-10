<?php
if (!defined('ABSPATH')) return;
// Save options
if (isset($_POST['ttbp_email_settings_nonce']) && wp_verify_nonce($_POST['ttbp_email_settings_nonce'], 'save_email_settings')) {
    update_option('ttbp_support_phone', sanitize_email($_POST['support_phone']));
    update_option('ttbp_email_from', sanitize_email($_POST['email_from']));
    update_option('ttbp_email_subject', sanitize_text_field($_POST['email_subject']));
    update_option('ttbp_update_email_subject', sanitize_text_field($_POST['update_email_subject']));
    update_option('ttbp_canceled_email_subject', sanitize_text_field($_POST['canceled_email_subject']));
    update_option('ttbp_email_template', wp_kses_post($_POST['email_template']));
    update_option('ttbp_update_email_template', wp_kses_post($_POST['update_email_template']));
    update_option('ttbp_canceled_email_template', wp_kses_post($_POST['canceled_email_template']));
    echo '<div class="updated"><p>Settings saved.</p></div>';
}

$support_phone = get_option('ttbp_support_phone', '000-000-0000');
$email_from = get_option('ttbp_email_from', get_bloginfo('admin_email'));
$email_subject = get_option('ttbp_email_subject', 'Your Booking Confirmation');
$update_email_subject = get_option('ttbp_update_email_subject', 'Your booking details have been updated');
$canceled_email_subject = get_option('ttbp_canceled_email_subject', 'Your booking is canceled');
$email_template = get_option('ttbp_email_template', '');
$update_email_template = get_option('ttbp_update_email_template', '');
$canceled_email_template = get_option('ttbp_canceled_email_template', '');

if (empty($email_template)) {
    $email_template = TTBP__get_default_email_template();
}

if (empty($update_email_template)) {
    $update_email_template = TTBP__get_update_email_template();
}

if (empty($canceled_email_template)) {
    $canceled_email_template = TTBP__get_canceled_email_template();
}
?>

<div class="wrap">
    <h2>Booking Email Settings</h2>
    <hr>
    <form method="post">
        <?php wp_nonce_field('save_email_settings', 'ttbp_email_settings_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="email_from">Support Phone Number</label></th>
                <td><input type="tel" name="support_phone" id="support_phone" value="<?php echo esc_attr($support_phone); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="email_from">Support Email</label></th>
                <td><input type="email" name="email_from" id="email_from" value="<?php echo esc_attr($email_from); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="email_subject">Email Subject</label></th>
                <td><input type="text" name="email_subject" id="email_subject" value="<?php echo esc_attr($email_subject); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="email_template">Booked Email Template</label></th>
                <td>
                    <?php
                    wp_editor($email_template, 'email_template', [
                        'textarea_name' => 'email_template',
                        'media_buttons' => false,
                        'textarea_rows' => 20,
                    ]);
                    ?>
                    <p class="description">
                        You can use placeholders like
                        <code>{{name}}</code>,
                        <code>{{email}}</code>,
                        <code>{{phone}}</code>,
                        <code>{{date}}</code>,
                        <code>{{time}}</code>,
                        <code>{{players}}</code>,
                        <code>{{holes}}</code>,
                        <code>{{golf_course}}</code>,
                        <code>{{support_email}}</code>.
                        <code>{{booking_amount}}</code>.
                        <code>{{total_amount}}</code>.
                        <code>{{due_amount}}</code>.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="update_email_subject">Update Email Subject</label></th>
                <td><input type="text" name="update_email_subject" id="update_email_subject" value="<?php echo esc_attr($update_email_subject); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="email_template">Booking Updated Template</label></th>
                <td>
                    <?php
                    wp_editor($update_email_template, 'update_email_template', [
                        'textarea_name' => 'update_email_template',
                        'media_buttons' => false,
                        'textarea_rows' => 20,
                    ]);
                    ?>
                    <p class="description">
                        You can use placeholders like
                        <code>{{name}}</code>,
                        <code>{{email}}</code>,
                        <code>{{phone}}</code>,
                        <code>{{date}}</code>,
                        <code>{{time}}</code>,
                        <code>{{players}}</code>,
                        <code>{{holes}}</code>,
                        <code>{{golf_course}}</code>,
                        <code>{{support_email}}</code>.
                        <code>{{booking_amount}}</code>.
                        <code>{{total_amount}}</code>.
                        <code>{{due_amount}}</code>.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="canceled_email_subject">Canceled Email Subject</label></th>
                <td><input type="text" name="canceled_email_subject" id="canceled_email_subject" value="<?php echo esc_attr($canceled_email_subject); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="email_template">Booking Canceled Template</label></th>
                <td>
                    <?php
                    wp_editor($canceled_email_template, 'canceled_email_template', [
                        'textarea_name' => 'canceled_email_template',
                        'media_buttons' => false,
                        'textarea_rows' => 20,
                    ]);
                    ?>
                    <p class="description">
                        You can use placeholders like
                        <code>{{name}}</code>,
                        <code>{{email}}</code>,
                        <code>{{phone}}</code>,
                        <code>{{date}}</code>,
                        <code>{{time}}</code>,
                        <code>{{players}}</code>,
                        <code>{{holes}}</code>,
                        <code>{{golf_course}}</code>,
                        <code>{{support_email}}</code>.
                        <code>{{booking_amount}}</code>.
                        <code>{{total_amount}}</code>.
                        <code>{{due_amount}}</code>.
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button('Save Settings'); ?>
    </form>
</div>