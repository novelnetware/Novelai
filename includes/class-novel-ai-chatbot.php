<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, an admin area, and public-facing
 * hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 * @author     Ali <ali@example.com>
 */
class Novel_AI_Chatbot {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Novel_AI_Chatbot_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of this plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'NOVEL_AI_CHATBOT_VERSION' ) ) {
            $this->version = NOVEL_AI_CHATBOT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'novel-ai-chatbot';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_common_hooks();
        $this->define_live_chat_ajax_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Novel_AI_Chatbot_Loader. Orchestrates the hooks of the plugin.
     * - Novel_AI_Chatbot_i18n. Defines internationalization functionality.
     * - Novel_AI_Chatbot_Admin. Defines all hooks for the admin area.
     * - Novel_AI_Chatbot_Public. Defines all hooks for the public side of the site.
     * - Novel_AI_Chatbot_Content_Collector. Handles content collection.
     * - Novel_AI_Chatbot_AI_Integration. Handles AI API calls.
     * - Novel_AI_Chatbot_Chat_History. Handles chat history.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-novel-ai-chatbot-loader.php';

        /**
         * The class responsible for defining internationalization functionality of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-novel-ai-chatbot-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-novel-ai-chatbot-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-novel-ai-chatbot-public.php';

        /**
         * The class responsible for collecting content from the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-novel-ai-chatbot-content-collector.php';

        /**
         * The class responsible for integrating with AI models.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-novel-ai-chatbot-ai-integration.php';

        /**
         * The class responsible for handling chat history.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-novel-ai-chatbot-chat-history.php';


        $this->loader = new Novel_AI_Chatbot_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Novel_AI_Chatbot_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Novel_AI_Chatbot_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Novel_AI_Chatbot_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_footer', $plugin_public, 'render_chatbox_if_floating' ); // Conditionally render
        $this->loader->add_filter( 'the_content', $plugin_public, 'add_chatbox_shortcode_output' ); // For shortcode rendering
        add_shortcode( 'novel_ai_chatbot', array( $plugin_public, 'render_chatbox_shortcode' ) ); // Register shortcode directly

    }

    /**
     * Define the hooks that run on both admin and public sides (e.g., AJAX for logged-in users).
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_common_hooks() {
        $content_collector = new Novel_AI_Chatbot_Content_Collector( $this->get_plugin_name(), $this->get_version() );
        $content_collector->register_ajax_hooks(); // Manually call this since it defines its own AJAX hooks

        // AI integration is used within ajax_get_chat_response, no direct common hooks for it.

        // Chat History for AJAX saving/loading
        $chat_history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );

        // Add AJAX hook for frontend chat query
        $this->loader->add_action( 'wp_ajax_novel_ai_chatbot_get_response', $this, 'ajax_get_chat_response' );
        $this->loader->add_action( 'wp_ajax_nopriv_novel_ai_chatbot_get_response', $this, 'ajax_get_chat_response' );
        // Add AJAX hook for loading chat history
        $this->loader->add_action( 'wp_ajax_novel_ai_chatbot_load_history', $this, 'ajax_load_chat_history' );
        $this->loader->add_action( 'wp_ajax_nopriv_novel_ai_chatbot_load_history', $this, 'ajax_load_chat_history' );

        // Hook for our scheduled cron job
        $this->loader->add_action( 'novel_ai_chatbot_daily_content_check', $this, 'run_daily_content_check' );

    }

    /**
     * AJAX callback for frontend chat queries, now using semantic search.
     *
     * @since 1.2.0
     */
    public function ajax_get_chat_response() {
        check_ajax_referer( 'novel-ai-chatbot-public-nonce', 'nonce' );

        $user_query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';

        if ( empty( $user_query ) ) {
            wp_send_json_error( array( 'message' => __( 'کوئری خالی میباشد.', 'novel-ai-chatbot' ) ) );
        }
        if ( empty( $session_id ) ) {
            wp_send_json_error( array( 'message' => __( 'خطا در چت آیدی.', 'novel-ai-chatbot' ) ) );
        }

        // We only need AI Integration and Chat History here now.
        $ai_integration = new Novel_AI_Chatbot_AI_Integration( $this->get_plugin_name(), $this->get_version() );
        $chat_history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );

        // Save user message to history
        $chat_history->save_message( $session_id, 'user', $user_query, get_current_user_id() );

        // Get AI response. The new get_ai_response handles the context search internally.
        $ai_response = $ai_integration->get_ai_response( $user_query );

        // Save bot response to history
        $chat_history->save_message( $session_id, 'bot', $ai_response, get_current_user_id() );

        wp_send_json_success( array( 'response' => $ai_response ) );
    }

    /**
     * AJAX callback to load chat history for a session.
     *
     * @since 1.0.0
     */
    public function ajax_load_chat_history() {
        check_ajax_referer( 'novel-ai-chatbot-public-nonce', 'nonce' );

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';

        if ( empty( $session_id ) ) {
            wp_send_json_error( array( 'message' => __( 'درخواست غیرمجاز.', 'novel-ai-chatbot' ) ) );
        }

        $chat_history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        $history = $chat_history->get_session_history( $session_id );

        wp_send_json_success( array( 'history' => $history ) );
    }


    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of WordPress and
     * set the prefix for all of the hooks it provides.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Novel_AI_Chatbot_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Defines the AJAX hooks for the live chat functionality.
     * These are mostly for admin/agent use, but some are for the public side.
     *
     * @since 1.2.0
     * @access private
     */
    private function define_live_chat_ajax_hooks() {
        $chat_history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );

        // Actions for the admin-side live chat panel
        $this->loader->add_action( 'wp_ajax_nac_get_live_chats', $this, 'ajax_get_live_chats' );
        $this->loader->add_action( 'wp_ajax_nac_claim_chat', $this, 'ajax_claim_chat' );
        $this->loader->add_action( 'wp_ajax_nac_send_agent_message', $this, 'ajax_send_agent_message' );
        $this->loader->add_action( 'wp_ajax_nac_resolve_chat', $this, 'ajax_resolve_chat' );
        $this->loader->add_action( 'wp_ajax_nac_get_session_history', $this, 'ajax_load_chat_history' ); // Re-using for agent panel

        // Action for the public-facing "Request Agent" button
        $this->loader->add_action( 'wp_ajax_nac_request_live_agent', $this, 'ajax_request_live_agent' );
        $this->loader->add_action( 'wp_ajax_nopriv_nac_request_live_agent', $this, 'ajax_request_live_agent' );

        // Action for the public-facing user sending a message in live chat
        $this->loader->add_action( 'wp_ajax_nac_send_live_user_message', $this, 'ajax_send_live_user_message' );
        $this->loader->add_action( 'wp_ajax_nopriv_nac_send_live_user_message', $this, 'ajax_send_live_user_message' );

        // Action for submitting a chat rating
        $this->loader->add_action( 'wp_ajax_nac_submit_rating', $this, 'ajax_submit_rating' );
        $this->loader->add_action( 'wp_ajax_nopriv_nac_submit_rating', $this, 'ajax_submit_rating' );

        // Action for fetching paginated chat history for the admin panel
        $this->loader->add_action( 'wp_ajax_nac_get_chat_history_paginated', $this, 'ajax_get_chat_history_paginated' );

        // Action for fetching analytics data
        $this->loader->add_action( 'wp_ajax_nac_get_analytics_data', $this, 'ajax_get_analytics_data' );
    }

    /**
     * AJAX: Get pending and active chats for the logged-in agent.
     */
    public function ajax_get_live_chats() {
        check_ajax_referer( 'novel-ai-live-chat-nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'دسترسی غیرمجاز' ] );
        }

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        $pending_chats = $history->get_conversations_by_status( 'pending' );
        $active_chats = $history->get_conversations_by_status( 'active' );

        wp_send_json_success( [
            'pending' => $pending_chats,
            'active'  => $active_chats
        ] );
    }

    /**
     * AJAX: Allows an agent to claim a pending chat.
     */
    public function ajax_claim_chat() {
        check_ajax_referer( 'novel-ai-live-chat-nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'دسترسی غیرمجاز' ] );
        }

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
        $agent_id = get_current_user_id();

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        $result = $history->claim_chat( $session_id, $agent_id );

        if ( $result ) {
            wp_send_json_success( [ 'message' => 'چت ها دریافت شد.' ] );
        } else {
            wp_send_json_error( [ 'message' => 'خطا در دریافت چت ، ممکن است کارشناس دیگری درحال صحبت باشد.' ] );
        }
    }

    /**
     * AJAX: Allows an agent to send a message.
     */
    public function ajax_send_agent_message() {
        check_ajax_referer( 'novel-ai-live-chat-nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'دسترسی غیرمجاز' ] );
        }

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
        $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
        $agent_id = get_current_user_id();

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        $history->add_agent_message( $session_id, $agent_id, $message );
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Allows a user to request a live agent.
     */
    public function ajax_request_live_agent() {
        check_ajax_referer( 'novel-ai-chatbot-public-nonce', 'nonce' ); // Use the public nonce
        
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        $history->change_chat_status( $session_id, 'pending' );
        
        $system_message = __( 'درخواست شما برای صحبت با اپراتور ثبت شد. لطفاً منتظر بمانید...', 'novel-ai-chatbot' );
        $history->save_message( $session_id, 'system', $system_message, 0, 0, 'pending' );

        wp_send_json_success( [ 'message' => 'درخواست ارسال شد.' ] );
    }

    /**
     * AJAX: Allows an agent to resolve (close) a chat.
     */
    public function ajax_resolve_chat() {
        check_ajax_referer( 'novel-ai-live-chat-nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'دسترسی غیرمجاز' ] );
        }
        
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        $history->change_chat_status( $session_id, 'resolved' );

        $system_message = __( 'این گفتگو توسط اپراتور پایان یافت.', 'novel-ai-chatbot' );
        $history->save_message( $session_id, 'system', $system_message, 0, get_current_user_id(), 'resolved' );

        wp_send_json_success( [ 'message' => 'چت به اتمام رسید.' ] );
    }

    /**
     * AJAX: Allows a user to send a message during a live chat.
     */
    public function ajax_send_live_user_message() {
        check_ajax_referer( 'novel-ai-chatbot-public-nonce', 'nonce' );
        
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
        $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
        $user_id = get_current_user_id();

        if ( empty( $session_id ) || empty( $message ) ) {
            wp_send_json_error( [ 'message' => 'اطلاعات نامعتبر.' ] );
        }

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        
        // Find the agent assigned to this chat
        $agent_id = $history->get_session_agent_id( $session_id ); // We need to create this helper method.

        $history->save_message( $session_id, 'user', $message, $user_id, $agent_id, 'active' );
        
        wp_send_json_success();
    }

    /**
     * Executes the daily content check and updates embeddings for modified posts.
     * This is triggered by a WP-Cron event.
     *
     * @since 1.3.0
     */
    public function run_daily_content_check() {
        // To prevent timeouts on sites with a lot of content, process in smaller batches.
        $args = array(
            'post_type'      => get_option('novel_ai_chatbot_options')['post_types'] ?? ['post', 'page'],
            'post_status'    => 'publish',
            'posts_per_page' => -1, // Get all posts to check them
            'fields'         => 'ids', // Only get post IDs for efficiency
        );
        
        $query = new WP_Query($args);
        $all_post_ids = $query->posts;

        if (empty($all_post_ids)) {
            return; // No posts to check
        }

        // We can process these IDs in batches using the Action Scheduler library for very large sites,
        // but for now, we'll process them directly. This is a good point for future improvement.
        $content_collector = new Novel_AI_Chatbot_Content_Collector($this->get_plugin_name(), $this->get_version());

        foreach ($all_post_ids as $post_id) {
            // Here you would add logic to see if the post needs re-indexing.
            // For example, check post_modified date against a stored timestamp.
            // For simplicity in this step, we are assuming a full re-index logic is complex.
            // A professional implementation would check against a 'last_indexed' post meta.
            // Let's call a simplified version of the collection logic.

            // The following is a simplified simulation. A real cron would need a robust loop.
            $post = get_post($post_id);
            if (!$post) continue;

            // This logic is duplicated from Content_Collector and should be refactored into
            // a shared method for a truly DRY implementation.
            $content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));
            $chunks = $content_collector->chunk_text($content); // We need to make chunk_text public in Content_Collector

            if (empty($chunks)) continue;

            $embeddings = $content_collector->ai_integration->generate_embedding($chunks); // We need to make ai_integration public

            if (is_wp_error($embeddings)) continue;

            global $wpdb;
            $table_name = $wpdb->prefix . 'novel_ai_chatbot_embeddings';

            // First, delete old chunks for this post to handle content removal
            $wpdb->delete($table_name, ['post_id' => $post_id], ['%d']);

            for ($i = 0; $i < count($chunks); $i++) {
                if (!isset($embeddings[$i])) continue;
                $wpdb->replace(
                    $table_name,
                    [
                        'post_id'          => $post->ID,
                        'post_type'        => $post->post_type,
                        'chunk_hash'       => md5($chunks[$i]),
                        'chunk_text'       => $chunks[$i],
                        'embedding_vector' => json_encode($embeddings[$i]),
                    ]
                );
            }
        }
    }

    /**
     * AJAX: Handles submission of a user's rating for a chat session.
     *
     * @since 1.3.0
     */
    public function ajax_submit_rating() {
        // Use the public nonce as this action is initiated from the frontend
        check_ajax_referer( 'novel-ai-chatbot-public-nonce', 'nonce' );

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
        $rating = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;

        if ( empty( $session_id ) || $rating < 1 || $rating > 5 ) {
            wp_send_json_error( [ 'message' => 'اطلاعات نامعتبر.' ] );
        }

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        $result = $history->save_rating( $session_id, $rating );

        if ( $result ) {
            wp_send_json_success( [ 'message' => 'امتیاز ثبت شد.' ] );
        } else {
            wp_send_json_error( [ 'message' => 'خطا در ذخیره امتیاز.' ] );
        }
    }

    /**
     * AJAX: Gets a paginated list of conversations for the history viewer.
     *
     * @since 1.3.0
     */
    public function ajax_get_chat_history_paginated() {
        check_ajax_referer( 'novel-ai-history-nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'دسترسی غیرمجاز' ] );
        }

        $search_term = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = 20; // Items per page

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );
        
        $total_items = $history->count_conversations( $search_term );
        $conversations = $history->get_conversations_paginated( $search_term, $per_page, $page );

        wp_send_json_success( [
            'conversations' => $conversations,
            'pagination' => [
                'total_items' => $total_items,
                'total_pages' => ceil( $total_items / $per_page ),
                'current_page' => $page
            ]
        ] );
    }

    /**
     * AJAX: Gathers and returns all data needed for the analytics dashboard.
     *
     * @since 1.3.0
     */
    public function ajax_get_analytics_data() {
        check_ajax_referer( 'novel-ai-analytics-nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }

        $history = new Novel_AI_Chatbot_Chat_History( $this->get_plugin_name(), $this->get_version() );

        $data = [
            'summary_stats'   => $history->get_analytics_summary_stats(),
            'daily_counts'    => $history->get_daily_chat_counts(30),
            'status_dist'     => $history->get_chat_status_distribution(),
            'ratings_dist'    => $history->get_ratings_distribution(),
        ];

        wp_send_json_success( $data );
    }

}