<?php

/**
 * Fired during plugin activation
 *
 * @link       https://novelnetware.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 * @author     Novelnetware <info@novelnetware.com>
 */
class Novel_AI_Chatbot_Activator {

    /**
     * Short Description. (e.g. `create_custom_post_types`)
     *
     * Long Description. (e.g. `Register custom post types for the plugin.`)
     *
     * @since    1.0.0
     */
    public static function activate() {
    // Create/Update chat history table
    require_once plugin_dir_path( __FILE__ ) . 'class-novel-ai-chatbot-chat-history.php';
    // Use the latest version number to ensure dbDelta runs if needed.
    $chat_history = new Novel_AI_Chatbot_Chat_History( 'novel-ai-chatbot', '1.2.0' );
    $chat_history->create_chat_history_table();

    // Create/Update embeddings table
    global $wpdb;
    $table_name_embeddings = $wpdb->prefix . 'novel_ai_chatbot_embeddings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name_embeddings (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) UNSIGNED NOT NULL,
        post_type VARCHAR(20) NOT NULL,
        chunk_hash VARCHAR(32) NOT NULL,
        chunk_text MEDIUMTEXT NOT NULL,
        embedding_vector LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY chunk_hash (chunk_hash),
        KEY post_id (post_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    // Add a version option to track our new table
    add_option( 'novel_ai_chatbot_embedding_db_version', '1.0.0' );

    // Schedule the daily content check cron job if it's not already scheduled.
    if ( ! wp_next_scheduled( 'novel_ai_chatbot_daily_content_check' ) ) {
        wp_schedule_event( time(), 'daily', 'novel_ai_chatbot_daily_content_check' );
    }
    // Add the custom 'Operator' role
        add_role(
            'nac_operator',
            __( 'اپراتور چت', 'novel-ai-chatbot' ),
            array(
                'read' => true, // Basic access to the dashboard
                'manage_live_chat' => true, // Our custom capability
            )
        );

         // Grant all custom capabilities to the Administrator role by default
        $admin_role = get_role( 'administrator' );
        if ( ! empty( $admin_role ) ) {
            $admin_role->add_cap( 'manage_live_chat' ); // Already created with Operator role
            $admin_role->add_cap( 'view_chatbot_settings' );
            $admin_role->add_cap( 'view_chatbot_history' );
            $admin_role->add_cap( 'view_chatbot_analytics' );
            $admin_role->add_cap( 'manage_chatbot_operators' );
            $admin_role->add_cap( 'manage_chatbot_roles' );
        }
}

}