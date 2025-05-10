<?php
if (!defined('ABSPATH')) return;
?>

<div class="wrap ttbp-wrap">
    <h2><?php _e('Manage Payment Methods'); ?></h2>
    <hr>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="ttbp_payment_form">
        <?php wp_nonce_field('ttbp_payment_settings', 'ttbp_payment_nonce'); ?>
        <input type="hidden" name="action" value="ttbp_payment_methods">
        <table class="form-table">
            <tbody>
                <tr class="ttbp_trow_title">
                    <th colspan="2"><?php _e('Offline Payment', 'teetime-booking-pro'); ?></th>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_offline_stat"><?php _e('Status', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <select type="text" name="ttbp_offline_stat" id="ttbp_offline_stat">
                            <option value="enable" <?php selected(get_option('ttbp_offline_stat'), 'enable'); ?>><?php _e('Enable', 'teetime-booking-pro'); ?></option>
                            <option value="disable" <?php selected(get_option('ttbp_offline_stat'), 'disable'); ?>><?php _e('Disable', 'teetime-booking-pro'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_offline_pay_label"><?php _e('Title', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ttbp_offline_pay_label" id="ttbp_offline_pay_label" value="<?php echo get_option('ttbp_offline_pay_label', ''); ?>">
                    </td>
                </tr>
                <tr class="ttbp_trow_title">
                    <th colspan="2"><?php _e('Clover Payment', 'teetime-booking-pro'); ?></th>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_clover_stat"><?php _e('Status', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <select type="text" name="ttbp_clover_stat" id="ttbp_clover_stat">
                            <option value="enable" <?php selected(get_option('ttbp_clover_stat'), 'enable'); ?>><?php _e('Enable', 'teetime-booking-pro'); ?></option>
                            <option value="disable" <?php selected(get_option('ttbp_clover_stat'), 'disable'); ?>><?php _e('Disable', 'teetime-booking-pro'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_clover_pay_label"><?php _e('Title', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ttbp_clover_pay_label" id="ttbp_clover_pay_label" value="<?php echo get_option('ttbp_clover_pay_label', ''); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_clover_env"><?php _e('Environment', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <select type="text" name="ttbp_clover_env" id="ttbp_clover_env">
                            <option value="test" <?php selected(get_option('ttbp_clover_env'), 'test'); ?>><?php _e('Sandbox', 'teetime-booking-pro'); ?></option>
                            <option value="live" <?php selected(get_option('ttbp_clover_env'), 'live'); ?>><?php _e('Production', 'teetime-booking-pro'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_clover_merchant_id"><?php _e((get_option('ttbp_clover_env') == 'test' ? 'Sandbox ' : '') . 'Merchant ID', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ttbp_clover_merchant_id" id="ttbp_clover_merchant_id" value="<?php echo get_option('ttbp_clover_merchant_id', ''); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_clover_public_key"><?php _e((get_option('ttbp_clover_env') == 'test' ? 'Sandbox ' : '') . 'Public Key', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ttbp_clover_public_key" id="ttbp_clover_public_key" value="<?php echo get_option('ttbp_clover_public_key', ''); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ttbp_clover_private_key"><?php _e((get_option('ttbp_clover_env') == 'test' ? 'Sandbox ' : '') . 'Private Key', 'teetime-booking-pro'); ?></label>
                    </th>
                    <td>
                        <input type="password" name="ttbp_clover_private_key" id="ttbp_clover_private_key" value="<?php echo get_option('ttbp_clover_private_key', ''); ?>">
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button('Save Settings'); ?>
    </form>
</div>