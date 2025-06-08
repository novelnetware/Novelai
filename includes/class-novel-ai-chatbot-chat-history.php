<?php

/**
 * Handles chat history storage and retrieval.
 *
 * @link       https://novelnetware.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 */

class Novel_AI_Chatbot_Chat_History {

    private $plugin_name;
    private $version;
    private $table_name;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'novel_ai_chatbot_chats'; // wp_novel_ai_chatbot_chats
    }

    /**
     * Creates or updates the custom database table for chat history, now with live chat support.
     *
     * @since 1.2.0
     */
    public function create_chat_history_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT 0,
            agent_id bigint(20) UNSIGNED DEFAULT 0,
            status varchar(20) DEFAULT 'bot' NOT NULL, -- Can be: bot, pending, active, resolved
            message_type varchar(10) NOT NULL, -- 'user', 'bot', 'agent', 'system'
            message_content text NOT NULL,
            user_rating int(1) DEFAULT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY session_id (session_id),
            KEY status (status),
            KEY agent_id (agent_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Add version to options to prevent repeated table creation on every activation
        add_option( $this->plugin_name . '_chat_history_db_version', '1.2.0' ); // Bump version
    }

    /**
     * Drops the custom database table for chat history upon plugin deactivation/uninstall.
     *
     * @since 1.0.0
     */
    public function drop_chat_history_table() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS $this->table_name" );
        delete_option( $this->plugin_name . '_chat_history_db_version' );
    }

    /**
     * Saves a message to the chat history.
     *
     * @since 1.2.0 (Replaces original method)
     * @param string $session_id      Unique ID for the current chat session.
     * @param string $message_type    'user', 'bot', 'agent', or 'system'.
     * @param string $message_content The content of the message.
     * @param int    $user_id         (Optional) WordPress user ID if logged in.
     * @param int    $agent_id        (Optional) WordPress agent ID if applicable.
     * @param string $status          (Optional) The status of the chat.
     * @return bool True on success, false on failure.
     */
    public function save_message( $session_id, $message_type, $message_content, $user_id = 0, $agent_id = 0, $status = 'bot' ) {
        global $wpdb;

        // Ensure session_id and message_type are valid
        if ( empty( $session_id ) || ! in_array( $message_type, array( 'user', 'bot', 'agent', 'system' ) ) ) {
            return false;
        }

        $result = $wpdb->insert(
            $this->table_name,
            array(
                'session_id'      => sanitize_text_field( $session_id ),
                'user_id'         => absint( $user_id ),
                'agent_id'        => absint( $agent_id ),
                'status'          => sanitize_text_field( $status ),
                'message_type'    => sanitize_text_field( $message_type ),
                'message_content' => sanitize_textarea_field( $message_content ),
                'timestamp'       => current_time( 'mysql' ),
            ),
            array( '%s', '%d', '%d', '%s', '%s', '%s', '%s' )
        );

        return (bool) $result;
    }

    /**
     * Retrieves chat history for a given session.
     *
     * @since 1.0.0
     * @param string $session_id Unique ID for the chat session.
     * @param int    $limit      (Optional) Number of messages to retrieve. Default 50.
     * @return array An array of message objects.
     */
    public function get_session_history( $session_id, $limit = 50 ) {
        global $wpdb;

        if ( empty( $session_id ) ) {
            return array();
        }

        $query = $wpdb->prepare(
            "SELECT message_type, message_content, timestamp
             FROM {$this->table_name}
             WHERE session_id = %s
             ORDER BY timestamp ASC
             LIMIT %d",
            sanitize_text_field( $session_id ),
            absint( $limit )
        );

        $results = $wpdb->get_results( $query );

        return $results;
    }

    /**
     * Generates a unique session ID for the user.
     * Can be stored in a cookie or session.
     *
     * @since 1.0.0
     * @return string A unique session ID.
     */
    public function generate_session_id() {
        if ( isset( $_COOKIE[ $this->plugin_name . '_session_id' ] ) ) {
            return sanitize_text_field( $_COOKIE[ $this->plugin_name . '_session_id' ] );
        }

        // Generate a new session ID if not found
        $session_id = wp_generate_uuid4(); // WordPress built-in UUID generator
        setcookie( $this->plugin_name . '_session_id', $session_id, time() + ( 86400 * 30 ), '/' ); // Cookie for 30 days
        return $session_id;
    }

    /**
     * Clears chat history for a specific session.
     *
     * @since 1.0.0
     * @param string $session_id Unique ID for the chat session.
     * @return bool True on success, false on failure.
     */
    public function clear_session_history( $session_id ) {
        global $wpdb;

        if ( empty( $session_id ) ) {
            return false;
        }

        $result = $wpdb->delete(
            $this->table_name,
            array( 'session_id' => sanitize_text_field( $session_id ) ),
            array( '%s' )
        );

        return (bool) $result;
    }

    /**
     * Get chat sessions (for admin purposes, e.g., viewing history).
     *
     * @since 1.0.0
     * @param int $limit Number of sessions to retrieve.
     * @param int $offset Offset for pagination.
     * @return array
     */
    public function get_chat_sessions( $limit = 20, $offset = 0 ) {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT DISTINCT session_id, user_id, MAX(timestamp) as last_message_time
             FROM {$this->table_name}
             GROUP BY session_id, user_id
             ORDER BY last_message_time DESC
             LIMIT %d OFFSET %d",
            absint( $limit ),
            absint( $offset )
        );
        return $wpdb->get_results( $query );
    }

    /**
     * Get total number of unique chat sessions.
     *
     * @since 1.0.0
     * @return int
     */
    public function count_chat_sessions() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM {$this->table_name}" );
    }

    /**
     * Changes the status of a specific chat session.
     *
     * @since 1.2.0
     * @param string $session_id The ID of the session.
     * @param string $new_status The new status ('pending', 'active', 'resolved').
     * @return bool True on success, false on failure.
     */
    public function change_chat_status( $session_id, $new_status ) {
        global $wpdb;

        // Ensure status is one of the allowed values
        if ( ! in_array( $new_status, [ 'bot', 'pending', 'active', 'resolved' ] ) ) {
            return false;
        }

        // We need to update all records for this session_id as status is not on a session master table
        // This is a simplification. A better schema would have a separate sessions table.
        $result = $wpdb->update(
            $this->table_name,
            [ 'status' => $new_status ],
            [ 'session_id' => sanitize_text_field( $session_id ) ],
            [ '%s' ],
            [ '%s' ]
        );
        
        return $result !== false;
    }

    /**
     * Assigns a chat to an agent and updates its status to 'active'.
     *
     * @since 1.2.0
     * @param string $session_id The ID of the session to claim.
     * @param int $agent_id The WordPress user ID of the agent.
     * @return bool True on success, false on failure.
     */
    public function claim_chat( $session_id, $agent_id ) {
        global $wpdb;
        
        $agent_id = absint( $agent_id );
        $session_id = sanitize_text_field( $session_id );

        // Update the agent_id and status for all messages in the session
        $updated = $wpdb->update(
            $this->table_name,
            [
                'status'   => 'active',
                'agent_id' => $agent_id
            ],
            [ 'session_id' => $session_id, 'status' => 'pending' ], // Only claim pending chats
            [ '%s', '%d' ],
            [ '%s', '%s' ]
        );

        if ( $updated !== false && $updated > 0 ) {
            // Add a system message to notify that an agent has joined
            $agent_info = get_userdata( $agent_id );
            $agent_name = $agent_info ? $agent_info->display_name : 'Agent';
            $system_message = sprintf( __( '%s به چت پیوست.', 'novel-ai-chatbot' ), $agent_name );
            $this->save_message( $session_id, 'system', $system_message, 0, $agent_id, 'active' );
            return true;
        }

        return false;
    }
    
    /**
     * Saves an agent's message to the chat history.
     *
     * @since 1.2.0
     * @param string $session_id The session ID.
     * @param int $agent_id The agent's user ID.
     * @param string $message_content The content of the message.
     * @return bool True on success, false on failure.
     */
    public function add_agent_message($session_id, $agent_id, $message_content) {
        return $this->save_message($session_id, 'agent', $message_content, 0, absint($agent_id), 'active');
    }


    /**
     * Gets a list of conversations grouped by session, based on their status.
     *
     * @since 1.2.0
     * @param string|array $status The status or statuses to fetch ('pending', 'active').
     * @param int $limit The maximum number of sessions to return.
     * @return array A list of conversations.
     */
    public function get_conversations_by_status( $status, $limit = 50 ) {
        global $wpdb;

        $status_placeholder = is_array($status) ? implode(', ', array_fill(0, count($status), '%s')) : '%s';
        
        $sql = $wpdb->prepare(
            "SELECT T1.session_id, T1.user_id, T1.agent_id, T1.status, T1.last_message, T1.last_timestamp
             FROM (
                 SELECT
                     session_id,
                     user_id,
                     agent_id,
                     status,
                     message_content as last_message,
                     timestamp as last_timestamp,
                     ROW_NUMBER() OVER(PARTITION BY session_id ORDER BY timestamp DESC) as rn
                 FROM {$this->table_name}
             ) T1
             WHERE T1.rn = 1 AND T1.status IN ({$status_placeholder})
             ORDER BY T1.last_timestamp DESC
             LIMIT %d",
            (is_array($status) ? $status : [$status]),
            absint($limit)
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Gets the assigned agent ID for a given session.
     * @param string $session_id
     * @return int Agent's user ID or 0 if not found.
     */
    public function get_session_agent_id( $session_id ) {
        global $wpdb;
        $agent_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT agent_id FROM {$this->table_name} WHERE session_id = %s AND agent_id != 0 ORDER BY timestamp DESC LIMIT 1",
            $session_id
        ) );
        return absint( $agent_id );
    }
    /**
     * Saves a user's rating for a specific chat session.
     *
     * @since 1.3.0
     * @param string $session_id The session ID to rate.
     * @param int    $rating     The rating from 1 to 5.
     * @return bool True on success, false on failure.
     */
    public function save_rating( $session_id, $rating ) {
        global $wpdb;

        $rating = absint( $rating );
        if ( $rating < 1 || $rating > 5 ) {
            return false; // Invalid rating value
        }

        // Update all rows for the session with the rating.
        // A better schema might have a separate sessions table, but this works.
        $result = $wpdb->update(
            $this->table_name,
            [ 'user_rating' => $rating ],
            [ 'session_id' => sanitize_text_field( $session_id ) ],
            [ '%d' ],
            [ '%s' ]
        );

        return $result !== false;
    }

    /**
     * Counts the total number of unique conversations, with an optional search term.
     *
     * @since 1.3.0
     * @param string $search_term A session ID to search for.
     * @return int The total number of conversations.
     */
    public function count_conversations( $search_term = '' ) {
        global $wpdb;

        $sql = "SELECT COUNT(DISTINCT session_id) FROM {$this->table_name}";
        if ( ! empty( $search_term ) ) {
            $sql .= $wpdb->prepare( " WHERE session_id LIKE %s", '%' . $wpdb->esc_like( $search_term ) . '%' );
        }

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Gets a paginated list of unique conversations.
     *
     * @since 1.3.0
     * @param string $search_term  A session ID to search for.
     * @param int    $per_page     Items per page.
     * @param int    $current_page The current page number.
     * @return array A list of conversation objects.
     */
    public function get_conversations_paginated( $search_term = '', $per_page = 20, $current_page = 1 ) {
        global $wpdb;

        $offset = ( $current_page - 1 ) * $per_page;
        $search_clause = '';
        if ( ! empty( $search_term ) ) {
            $search_clause = $wpdb->prepare( "WHERE T1.session_id LIKE %s", '%' . $wpdb->esc_like( $search_term ) . '%' );
        }

        // This is a complex query to get the last message, status, and rating for each session.
        $sql = $wpdb->prepare(
            "SELECT 
                T1.session_id,
                T1.status,
                T1.last_message,
                T1.user_rating,
                T1.last_timestamp
             FROM (
                 SELECT
                     session_id,
                     status,
                     message_content as last_message,
                     user_rating,
                     timestamp as last_timestamp,
                     ROW_NUMBER() OVER(PARTITION BY session_id ORDER BY timestamp DESC) as rn
                 FROM {$this->table_name}
             ) T1
             {$search_clause}
             WHERE T1.rn = 1
             ORDER BY T1.last_timestamp DESC
             LIMIT %d OFFSET %d",
            absint( $per_page ),
            absint( $offset )
        );

        return $wpdb->get_results( $sql );
    }
    /**
     * Gets summary statistics for the analytics dashboard.
     *
     * @since 1.3.0
     * @return array An array of key stats.
     */
    public function get_analytics_summary_stats() {
        global $wpdb;

        $stats = [];

        // Total conversations
        $stats['total_conversations'] = $this->count_conversations();

        // Total messages
        $stats['total_messages'] = (int) $wpdb->get_var("SELECT COUNT(id) FROM {$this->table_name}");
        
        // Average rating
        $stats['average_rating'] = (float) $wpdb->get_var("SELECT AVG(user_rating) FROM {$this->table_name} WHERE user_rating > 0");

        return $stats;
    }

    /**
     * Gets the number of new conversations started per day for the last N days.
     *
     * @since 1.3.0
     * @param int $days The number of days to look back.
     * @return array An array of objects with 'date' and 'count'.
     */
    public function get_daily_chat_counts( $days = 30 ) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT 
                DATE(first_message_time) as chat_date, 
                COUNT(session_id) as chat_count
             FROM (
                SELECT 
                    session_id, 
                    MIN(timestamp) as first_message_time
                FROM {$this->table_name}
                GROUP BY session_id
             ) as sessions
             WHERE first_message_time >= %s
             GROUP BY chat_date
             ORDER BY chat_date ASC",
             date('Y-m-d', strtotime("-{$days} days"))
        );

        return $wpdb->get_results($sql);
    }

    /**
     * Gets the distribution of chat statuses (bot, resolved, etc.).
     *
     * @since 1.3.0
     * @return array An array of objects with 'status' and 'count'.
     */
    public function get_chat_status_distribution() {
        global $wpdb;

        // Get the latest status for each session
        $sql = "SELECT T1.status, COUNT(T1.session_id) as count
                FROM (
                    SELECT
                        session_id,
                        status,
                        ROW_NUMBER() OVER(PARTITION BY session_id ORDER BY timestamp DESC) as rn
                    FROM {$this->table_name}
                ) T1
                WHERE T1.rn = 1
                GROUP BY T1.status";
        
        return $wpdb->get_results($sql);
    }

    /**
     * Gets the distribution of user ratings.
     *
     * @since 1.3.0
     * @return array An array of objects with 'rating' and 'count'.
     */
    public function get_ratings_distribution() {
        global $wpdb;

        // Get the final rating for each session that has one
        $sql = "SELECT T1.user_rating as rating, COUNT(T1.session_id) as count
                FROM (
                     SELECT
                        session_id,
                        user_rating,
                        ROW_NUMBER() OVER(PARTITION BY session_id ORDER BY timestamp DESC) as rn
                    FROM {$this->table_name}
                    WHERE user_rating IS NOT NULL AND user_rating > 0
                ) T1
                WHERE T1.rn = 1
                GROUP BY T1.user_rating
                ORDER BY rating ASC";

        return $wpdb->get_results($sql);
    }

}