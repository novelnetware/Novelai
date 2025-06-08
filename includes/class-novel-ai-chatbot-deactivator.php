<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 * @author     Ali <ali@example.com>
 */
class Novel_AI_Chatbot_Deactivator {

    /**
     * Short Description. (e.g. `flush_rewrite_rules`)
     *
     * Long Description. (e.g. `Flush rewrite rules for custom post types.`)
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear the scheduled cron job on deactivation.
        wp_clear_scheduled_hook( 'novel_ai_chatbot_daily_content_check' );

        // Remove our custom role
        remove_role( 'nac_operator' );

        // Remove capabilities from the administrator role
        $admin_role = get_role( 'administrator' );
        if ( ! empty( $admin_role ) ) {
            $admin_role->remove_cap( 'manage_live_chat' );
            $admin_role->remove_cap( 'view_chatbot_settings' );
            $admin_role->remove_cap( 'view_chatbot_history' );
            $admin_role->remove_cap( 'view_chatbot_analytics' );
            $admin_role->remove_cap( 'manage_chatbot_operators' );
            $admin_role->remove_cap( 'manage_chatbot_roles' );
        }
    }

}