<?php
/**
 * Provides the admin-facing view for the Role Management page.
 * The $nac_capabilities variable is available from the parent function.
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/admin/partials
 */
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p><?php _e( 'در این جدول مشخص کنید که هر نقش کاربری به کدام بخش از افزونه دسترسی داشته باشد.', 'novel-ai-chatbot' ); ?></p>
    <form method="post" action="">
        <?php wp_nonce_field( 'nac_save_roles_action', 'nac_roles_nonce' ); ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e( 'نقش کاربری', 'novel-ai-chatbot' ); ?></th>
                    <?php foreach ( $nac_capabilities as $cap => $label ) : ?>
                        <th scope="col" style="text-align: center;"><?php echo esc_html( $label ); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( get_editable_roles() as $role_slug => $role_details ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( translate_user_role( $role_details['name'] ) ); ?></strong></td>
                        <?php foreach ( $nac_capabilities as $cap => $label ) : 
                            $role_object = get_role( $role_slug );
                            $is_admin = ( 'administrator' === $role_slug );
                            $is_checked = $is_admin || $role_object->has_cap( $cap );
                        ?>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="nac_role_caps[<?php echo esc_attr( $role_slug ); ?>][<?php echo esc_attr( $cap ); ?>]"
                                       value="1"
                                       <?php checked( $is_checked ); ?>
                                       <?php disabled( $is_admin ); // Prevent admins from locking themselves out ?>
                                >
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ( ! empty( get_editable_roles() ) ) : ?>
            <p class="description">
                <?php _e( 'توجه: نقش مدیر کل همیشه به تمام بخش‌ها دسترسی کامل دارد.', 'novel-ai-chatbot' ); ?>
            </p>
            <?php submit_button( __( 'ذخیره دسترسی‌ها', 'novel-ai-chatbot' ) ); ?>
        <?php endif; ?>
    </form>
</div>