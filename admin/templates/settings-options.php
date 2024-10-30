<div class="wrap">
    <h1>IWD Agency - Dominate Checkout Connector</h1>
    <form id="iwd_wc_opc_general_settings" method="post" action="options.php">
        <?php settings_fields('iwd_connector_settings'); ?>
        <?php do_settings_sections('iwd_connector_settings'); ?>
        <h2>Info</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Version</th>
                <td><?php echo esc_html(IWD_CONNECTOR_VERSION); ?></td>
            </tr>
            <tr>
                <th scope="row">Documentation</th>
                <td><a href="https://help.dominate.co/checkout-suite" target="_blank">User Guide</a></td>
            </tr>
            <tr>
                <th scope="row">Register Dominate Account</th>
                <td><a href="https://www.dominate.co/account/create" target="_blank">Registration</a></td>
            </tr>
            <tr>
                <th scope="row">Account Details</th>
                <td><a href="https://www.dominate.co/account" target="_blank">My Account</a></td>
            </tr>
        </table>
        <h2>General</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="iwd_connector_enabled">Enable Checkout Suite </label></th>
                <td>
                    <input name="iwd_connector_enabled" id="iwd_connector_enabled" type="checkbox"
                           value="1" <?php checked(1, get_option('iwd_connector_enabled'), true); ?>>
                    Check this option to enable Checkout Suite plugin features
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="iwd_subscriptions_enabled">Enable Subscriptions </label></th>
                <td>
                    <input name="iwd_subscriptions_enabled" id="iwd_subscriptions_enabled" type="checkbox"
                           value="1" <?php checked(1, get_option('iwd_subscriptions_enabled'), true); ?>>
                    Check this option to enable Subscriptions plugin features
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="iwd_connector_integration_key">Integration API Key</label></th>
                <td>
                    <input class="iwd_admin_input" name="iwd_connector_integration_key"
                           id="iwd_connector_integration_key" type="text"
                           value="<?php echo esc_html(get_option('iwd_connector_integration_key')); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="iwd_connector_secret_key">Integration API Secret Key</label></th>
                <td>
                    <input class="iwd_admin_input" name="iwd_connector_secret_key" id="iwd_connector_secret_key"
                           type="password" value="<?php echo esc_html(get_option('iwd_connector_secret_key')); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">Connection Status</th>
                <td>
                    <button type="submit" name="Connection Test" id="iwd-connector-connection-test">Connection Test<br>
                    </button>
                    <p>Check to make sure your payment methods are configured within Dominate's Checkout Admin Panel to continue using</p>
                </td>
            </tr>
            <tr>
                <td class="error_massage" colspan="2"></td>
            </tr>
        </table>

        <h2>Newsletter</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label>Download/export the newsletter subscribers</label></th>
                <td>
                    <a href="<?= get_bloginfo('url'); ?>/wp-admin/admin.php?page=iwd_connector_opc_panel&action=dominate_csv_file"
                       class="dominate_newsletter_subscribers">Download CSV</a>
                </td>
            </tr>
        </table>

        <h2>Notifications</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="iwd_connector_enabled">Notify customers when an order fails to create</label></th>
                <td>
                    <input name="iwd_notification_enabled"  id="iwd_notification_enabled" type="checkbox" value="1" <?php checked( 1, get_option( 'iwd_notification_enabled' ), true ); ?>>
                    Check this option to enable plugin features
                </td>
            </tr>

        </table>

		<?php submit_button(); ?>

    </form>
</div>
