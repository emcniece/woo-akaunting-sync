<div class="wrap">
    <h1>WooCommerce Akaunting Sync</h1>
    <form action="options.php" method="post">
        <?php settings_fields('wasync-options'); ?>
        <?php do_settings_sections('wasync-options'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">Akaunting URL</th>
                <td>
                    <input type="text" name="wasync_url" required="required" value="<?php echo esc_attr(get_option('wasync_url')); ?>" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Akaunting Username</th>
                <td>
                    <input type="text" name="wasync_user" required="required" value="<?php echo esc_attr(get_option('wasync_user')); ?>" />
                    </td>
            </tr>
            <tr valign="top">
                <th scope="row">Akaunting Password</th>
                <td>
                    <input type="password" name="wasync_password" required="required" value="<?php echo esc_attr(get_option('wasync_password')); ?>" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Server ping</th>
                <td>
                    <textarea style="width:100%;min-height:130px;"><?php echo $pingresp['body'] ?></textarea>
                </td>
            </tr>

        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
        </p>
    </form>
</div>
