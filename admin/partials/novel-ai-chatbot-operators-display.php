<?php
/**
 * Provides the admin-facing view for the Operator Management page.
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p><?php _e( 'در این بخش می‌توانید اپراتورهای جدیدی برای پاسخگویی در چت آنلاین تعریف کنید و لیست اپراتورهای موجود را مشاهده نمایید.', 'novel-ai-chatbot' ); ?></p>
    
    <div id="col-container">
        <div id="col-left">
            <div class="col-wrap">
                <h2><?php _e( 'افزودن اپراتور جدید', 'novel-ai-chatbot' ); ?></h2>
                <form method="post" action="" class="form-wrap">
                    <?php wp_nonce_field( 'nac_add_operator_action', 'nac_add_operator_nonce' ); ?>
                    
                    <div class="form-field">
                        <label for="username"><?php _e( 'نام کاربری', 'novel-ai-chatbot' ); ?></label>
                        <input name="username" id="username" type="text" value="" required>
                    </div>
                    
                    <div class="form-field">
                        <label for="email"><?php _e( 'ایمیل', 'novel-ai-chatbot' ); ?></label>
                        <input name="email" id="email" type="email" value="" required>
                    </div>

                    <div class="form-field">
                        <label for="password"><?php _e( 'رمز عبور', 'novel-ai-chatbot' ); ?></label>
                        <input name="password" id="password" type="password" value="" autocomplete="new-password" required>
                    </div>

                    <?php submit_button( __( 'افزودن اپراتور', 'novel-ai-chatbot' ) ); ?>
                </form>
            </div>
        </div>

        <div id="col-right">
            <div class="col-wrap">
                <h2><?php _e( 'لیست اپراتورها', 'novel-ai-chatbot' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php _e( 'نام کاربری', 'novel-ai-chatbot' ); ?></th>
                            <th scope="col"><?php _e( 'نام نمایشی', 'novel-ai-chatbot' ); ?></th>
                            <th scope="col"><?php _e( 'ایمیل', 'novel-ai-chatbot' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $operators = get_users( array( 'role' => 'nac_operator' ) );
                        if ( ! empty( $operators ) ) {
                            foreach ( $operators as $operator ) {
                                echo '<tr>';
                                echo '<td><strong>' . esc_html( $operator->user_login ) . '</strong></td>';
                                echo '<td>' . esc_html( $operator->display_name ) . '</td>';
                                echo '<td>' . esc_html( $operator->user_email ) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3">' . esc_html__( 'هیچ اپراتوری یافت نشد.', 'novel-ai-chatbot' ) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>