<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://Novelnetware.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/admin
 * @author     Novelnetware <info@novelnetware.com>
 */
class Novel_AI_Chatbot_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    // Hooks for our custom admin pages for reliable script loading
    private $live_chat_hook;
    private $history_hook;
    private $analytics_hook;
    private $operator_reg_hook;
    private $role_management_hook;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version           The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Add filter to load our Vue apps as JavaScript modules
        add_filter( 'script_loader_tag', array( $this, 'add_type_attribute_to_admin_scripts' ), 10, 2 );
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles( $hook ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/novel-ai-chatbot-admin.css', array(), $this->version, 'all' );
    }

  /**
     * Register the JavaScript for the admin area.
     * This method now conditionally loads scripts for different admin pages
     * to avoid conflicts and improve performance.
     *
     * @since 1.4.0 (Replaces original method)
     * @param string $hook The current admin page hook.
     */
   /**
     * Register the JavaScript for the admin area, loading scripts conditionally.
     */
    public function enqueue_scripts( $hook ) {
        // General scripts for settings pages
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script($this->plugin_name . '-admin-general', plugin_dir_url( __FILE__ ) . 'js/novel-ai-chatbot-admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, true);
        wp_enqueue_script('novel-ai-chatbot-admin-media', plugin_dir_url( __FILE__ ) . 'js/novel-ai-chatbot-admin-media.js', array( 'jquery', 'media-upload', 'thickbox' ), $this->version, true);
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_media();
        
        wp_localize_script(
            $this->plugin_name . '-admin-general', 'novel_ai_chatbot_admin_vars',
            array(
                'ajax_url'            => admin_url( 'admin-ajax.php' ),
                'nonce'               => wp_create_nonce( 'novel-ai-chatbot-admin-nonce' ),
                'detectingSitemap'    => __( 'در حال شناسایی sitemap.xml...', 'novel-ai-chatbot' ),
                'sitemapDetected'     => __( 'نقشه سایت شناسایی شد', 'novel-ai-chatbot' ),
                'sitemapNotFound'     => __( 'نقشه سایت یافت نشد. لطفاً به صورت دستی وارد کنید.', 'novel-ai-chatbot' ),
                'sitemapError'        => __( 'خطا در شناسایی نقشه سایت.', 'novel-ai-chatbot' ),
                'startingCollection'  => __( 'شروع جمع‌آوری محتوا...', 'novel-ai-chatbot' ),
                'collectionInProgress'=> __( 'جمع‌آوری در حال انجام است...', 'novel-ai-chatbot' ),
                'processingContent'   => __( 'در حال پردازش محتوا', 'novel-ai-chatbot' ),
                'collectionFinished'  => __( 'جمع‌آوری محتوا با موفقیت به پایان رسید!', 'novel-ai-chatbot' ),
                'collectionError'     => __( 'خطا در جمع‌آوری محتوا', 'novel-ai-chatbot' ),
                'ajaxError'           => __( 'یک خطای ایجکس رخ داد.', 'novel-ai-chatbot' ),
                'startCollection'     => __( 'شروع جمع‌آوری محتوا', 'novel-ai-chatbot' ),
            )
        );

        // --- Live Chat Page ---
        if ( $hook === $this->live_chat_hook ) {
            wp_enqueue_script($this->plugin_name . '-live-chat-app', plugin_dir_url( __FILE__ ) . 'js/novel-ai-chatbot-live-chat.js', array(), $this->version, true);
            wp_localize_script($this->plugin_name . '-live-chat-app', 'nac_live_chat_vars', ['ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'novel-ai-live-chat-nonce' ), 'agent_id' => get_current_user_id(), 'i18n' => ['pending_chats' => __( 'چت‌های در انتظار', 'novel-ai-chatbot' ), 'active_chats' => __( 'چت‌های فعال من', 'novel-ai-chatbot' ), 'no_pending_chats' => __( 'در حال حاضر هیچ چت در انتظاری وجود ندارد.', 'novel-ai-chatbot' ), 'claim_chat' => __( 'پذیرفتن چت', 'novel-ai-chatbot' ), 'resolve_chat' => __( 'پایان دادن به چت', 'novel-ai-chatbot' )]]);
        }

        // --- Chat History Page ---
       if ( $hook === $this->history_hook ) {
            wp_enqueue_script($this->plugin_name . '-history-app', plugin_dir_url( __FILE__ ) . 'js/novel-ai-chatbot-history.js', array(), $this->version, true);
            wp_localize_script($this->plugin_name . '-history-app', 'nac_history_vars', ['ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'novel-ai-history-nonce' )]);
        }
        
        // --- Analytics Page ---
        // Note: The hook for the analytics page would be novel-چت-بات_page_novel-ai-chatbot-analytics
       if ( $hook === $this->analytics_hook ) {
            wp_enqueue_script($this->plugin_name . '-analytics-app', plugin_dir_url( __FILE__ ) . 'js/novel-ai-chatbot-analytics.js', array(), $this->version, true);
            wp_localize_script($this->plugin_name . '-analytics-app', 'nac_analytics_vars', ['ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'novel-ai-analytics-nonce' )]);
        }
    }

    /**
     * Add the top-level menu page for the plugin.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main Menu Page (Requires at least one submenu to be accessible)
        add_menu_page('تنظیمات نووِل چَت بات', 'نووِل چَت بات', 'read', $this->plugin_name . '-settings', array( $this, 'display_settings_page' ), 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'assets/svg/novelnetware-logo.svg' ) ), 6);
        
        // Submenus with specific capabilities
        add_submenu_page($this->plugin_name . '-settings', 'تنظیمات کلی', 'تنظیمات کلی', 'view_chatbot_settings', $this->plugin_name . '-settings', array( $this, 'display_settings_page' ));
        add_submenu_page($this->plugin_name . '-settings', 'سفارشی‌سازی چت', 'سفارشی‌سازی چت', 'view_chatbot_settings', $this->plugin_name . '-chat-customization', array( $this, 'display_chat_customization_page' ));
        add_submenu_page($this->plugin_name . '-settings', 'جمع‌آوری محتوا', 'جمع‌آوری محتوا', 'view_chatbot_settings', $this->plugin_name . '-content-collection', array( $this, 'display_content_collection_page' ));
        
        $this->live_chat_hook = add_submenu_page($this->plugin_name . '-settings', 'چت آنلاین', 'چت آنلاین', 'manage_live_chat', $this->plugin_name . '-live-chat', array( $this, 'display_live_chat_page' ));
        $this->history_hook = add_submenu_page($this->plugin_name . '-settings', 'تاریخچه چت‌ها', 'تاریخچه چت‌ها', 'view_chatbot_history', $this->plugin_name . '-chat-history', array( $this, 'display_chat_history_page' ));
        $this->analytics_hook = add_submenu_page($this->plugin_name . '-settings', 'تحلیل‌ها', 'تحلیل‌ها', 'view_chatbot_analytics', $this->plugin_name . '-analytics', array( $this, 'display_analytics_page' ));
        
        $this->operator_reg_hook = add_submenu_page($this->plugin_name . '-settings', 'مدیریت اپراتورها', 'مدیریت اپراتورها', 'manage_chatbot_operators', $this->plugin_name . '-operators', array( $this, 'display_operator_management_page' ));
        $this->role_management_hook = add_submenu_page($this->plugin_name . '-settings', 'مدیریت دسترسی‌ها', 'مدیریت دسترسی‌ها', 'manage_chatbot_roles', $this->plugin_name . '-roles', array( $this, 'display_role_management_page' ));
    }

    /**
     * Render the settings page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/novel-ai-chatbot-settings-display.php';
    }

    /**
     * Render the chat customization page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_chat_customization_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/novel-ai-chatbot-chat-customization-display.php';
    }

    /**
     * Render the content collection page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_content_collection_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/novel-ai-chatbot-content-collection-display.php';
    }

    /**
     * Render the live chat page for the plugin.
     *
     * @since 1.2.0
     */
    public function display_live_chat_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/novel-ai-chatbot-live-chat-display.php';
    }

    /**
     * Render the chat history page for the plugin.
     *
     * @since 1.3.0
     */
    public function display_chat_history_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/novel-ai-chatbot-history-display.php';
    }

    /**
     * Register plugin settings and fields.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register main settings group
        register_setting(
            'novel_ai_chatbot_settings_group',
            'novel_ai_chatbot_options',
            array( $this, 'sanitize_settings' )
        );

        // Add settings section for General Settings
        add_settings_section(
            'novel_ai_chatbot_general_section',
            __( 'تنظیمات کلی', 'novel-ai-chatbot' ),
            array( $this, 'print_general_section_info' ),
            'novel-ai-chatbot-settings'
        );

        // Add settings field for Post Types
        add_settings_field(
            'novel_ai_chatbot_post_types',
            __( 'انتخاب انواع پست‌ها برای خلاصه سازی AI', 'novel-ai-chatbot' ),
            array( $this, 'post_types_callback' ),
            'novel-ai-chatbot-settings',
            'novel_ai_chatbot_general_section'
        );

        // Add settings field for Sitemap URL
        add_settings_field(
            'novel_ai_chatbot_sitemap_url',
            __( 'آدرس Sitemap.xml', 'novel-ai-chatbot' ),
            array( $this, 'sitemap_url_callback' ),
            'novel-ai-chatbot-settings',
            'novel_ai_chatbot_general_section'
        );

        // Add settings field for AI Model selection
        add_settings_field(
            'novel_ai_chatbot_ai_model',
            __( 'انتخاب مدل AI', 'novel-ai-chatbot' ),
            array( $this, 'ai_model_callback' ),
            'novel-ai-chatbot-settings',
            'novel_ai_chatbot_general_section'
        );

        // Add settings field for OpenAI API Key (NEW)
        add_settings_field(
            'novel_ai_chatbot_openai_api_key',
            __( 'کلید OpenAI API', 'novel-ai-chatbot' ),
            array( $this, 'openai_api_key_callback' ),
            'novel-ai-chatbot-settings',
            'novel_ai_chatbot_general_section'
        );

        // Add settings field for Gemini API Key (NEW)
        add_settings_field(
            'novel_ai_chatbot_gemini_api_key',
            __( 'کلید Gemini API', 'novel-ai-chatbot' ),
            array( $this, 'gemini_api_key_callback' ),
            'novel-ai-chatbot-settings',
            'novel_ai_chatbot_general_section'
        );

        // Add settings field for Deepseek API Key (NEW)
        add_settings_field(
            'novel_ai_chatbot_deepseek_api_key',
            __( 'کلید Deepseek API', 'novel-ai-chatbot' ),
            array( $this, 'deepseek_api_key_callback' ),
            'novel-ai-chatbot-settings',
            'novel_ai_chatbot_general_section'
        );


        // Register chat customization settings
        register_setting(
            'novel_ai_chatbot_chat_customization_group',
            'novel_ai_chatbot_chat_customization_options',
            array( $this, 'sanitize_chat_customization_settings' )
        );

        add_settings_section(
            'novel_ai_chatbot_chat_design_section',
            __( 'طراحی چت‌باکس', 'novel-ai-chatbot' ),
            array( $this, 'print_chat_design_section_info' ),
            'novel-ai-chatbot-chat-customization'
        );

        // Shortcode usage section
        add_settings_section(
            'novel_ai_chatbot_shortcode_section',
            __( 'گزینه‌های نمایش چت‌باکس', 'novel-ai-chatbot' ),
            array( $this, 'print_shortcode_section_info' ),
            'novel-ai-chatbot-chat-customization'
        );

        add_settings_field(
            'novel_ai_chatbot_display_method',
            __( 'روش نمایش چت‌باکس', 'novel-ai-chatbot' ),
            array( $this, 'chatbox_display_method_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_shortcode_section'
        );

        // Add settings field for Widget Position
        add_settings_field(
            'novel_ai_chatbot_widget_position',
            __( 'موقعیت ویجت شناور', 'novel-ai-chatbot' ),
            array( $this, 'widget_position_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_shortcode_section'
        );

        // Chatbox Customization fields (expanded)
        add_settings_field(
            'novel_ai_chatbot_chat_primary_color',
            __( 'رنگ اصلی (سرچت‌باکس, دکمه‌ها)', 'novel-ai-chatbot' ),
            array( $this, 'chat_primary_color_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_chat_bg_color',
            __( 'رنگ پس‌زمینه چت‌باکس', 'novel-ai-chatbot' ),
            array( $this, 'chat_bg_color_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_user_msg_bg_color',
            __( 'رنگ پس‌زمینه پیام کاربر', 'novel-ai-chatbot' ),
            array( $this, 'user_msg_bg_color_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_bot_msg_bg_color',
            __( 'رنگ پس‌زمینه پیام ربات', 'novel-ai-chatbot' ),
            array( $this, 'bot_msg_bg_color_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_chat_text_color',
            __( 'رنگ متن', 'novel-ai-chatbot' ),
            array( $this, 'chat_text_color_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_header_text',
            __( 'متن سرچت‌باکس', 'novel-ai-chatbot' ),
            array( $this, 'header_text_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_initial_bot_message',
            __( 'پیام اولیه ربات', 'novel-ai-chatbot' ),
            array( $this, 'initial_bot_message_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_initial_popup_message',
            __( 'پیام اولیه نمایش چت‌باکس', 'novel-ai-chatbot' ),
            array( $this, 'initial_popup_message_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
        add_settings_field(
            'novel_ai_chatbot_assistant_title',
            __( 'عنوان ربات', 'novel-ai-chatbot' ),
            array( $this, 'assistant_title_callback' ),
            'novel-ai-chatbot-chat-customization',
            'novel_ai_chatbot_chat_design_section'
        );
    }

    /**
     * Sanitize main settings.
     *
     * @since    1.0.0
     * @param    array    $input    The input settings.
     * @return   array              The sanitized settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized_input = array();

        // Sanitize AI Model
        $allowed_ai_models = array( 'openai', 'gemini', 'deepseek' );
        if ( isset( $input['ai_model'] ) && in_array( $input['ai_model'], $allowed_ai_models ) ) {
            $sanitized_input['ai_model'] = sanitize_text_field( $input['ai_model'] );
        } else {
            $sanitized_input['ai_model'] = 'gemini'; // Default
        }

        // Sanitize Sitemap URL
        if ( isset( $input['sitemap_url'] ) ) {
            $sanitized_input['sitemap_url'] = esc_url_raw( $input['sitemap_url'] );
        }

        // Sanitize Post Types (ensure they are valid post types)
        if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $valid_post_types = get_post_types( array( 'public' => true ), 'names' );
            $sanitized_post_types = array();
            foreach ( $input['post_types'] as $post_type ) {
                if ( array_key_exists( $post_type, $valid_post_types ) ) {
                    $sanitized_post_types[] = sanitize_text_field( $post_type );
                }
            }
            $sanitized_input['post_types'] = $sanitized_post_types;
        } else {
            $sanitized_input['post_types'] = array(); // No post types selected by default
        }

        // Sanitize API Keys (NEW)
        if ( isset( $input['openai_api_key'] ) ) {
            $sanitized_input['openai_api_key'] = sanitize_text_field( $input['openai_api_key'] );
        }
        if ( isset( $input['gemini_api_key'] ) ) {
            $sanitized_input['gemini_api_key'] = sanitize_text_field( $input['gemini_api_key'] );
        }
        if ( isset( $input['deepseek_api_key'] ) ) {
            $sanitized_input['deepseek_api_key'] = sanitize_text_field( $input['deepseek_api_key'] );
        }

        return $sanitized_input;
    }

    /**
     * Sanitize chat customization settings.
     *
     * @since    1.0.0
     * @param    array    $input    The input settings.
     * @return   array              The sanitized settings.
     */
    public function sanitize_chat_customization_settings( $input ) {
        $sanitized_input = array();

        // Sanitize display method
        $allowed_display_methods = array('floating', 'shortcode');
        if (isset($input['display_method']) && in_array($input['display_method'], $allowed_display_methods)) {
            $sanitized_input['display_method'] = sanitize_text_field($input['display_method']);
        } else {
            $sanitized_input['display_method'] = 'floating'; // Default
        }

        // Sanitize widget position
        $allowed_positions = array('bottom-right', 'bottom-left');
        if (isset($input['widget_position']) && in_array($input['widget_position'], $allowed_positions)) {
            $sanitized_input['widget_position'] = $input['widget_position'];
        } else {
            $sanitized_input['widget_position'] = 'bottom-right'; // Default
        }

        // Sanitize colors
        if ( isset( $input['chat_primary_color'] ) ) {
            $sanitized_input['chat_primary_color'] = sanitize_hex_color( $input['chat_primary_color'] );
        }
        if ( isset( $input['chat_bg_color'] ) ) {
            $sanitized_input['chat_bg_color'] = sanitize_hex_color( $input['chat_bg_color'] );
        }
        if ( isset( $input['user_msg_bg_color'] ) ) {
            $sanitized_input['user_msg_bg_color'] = sanitize_hex_color( $input['user_msg_bg_color'] );
        }
        if ( isset( $input['bot_msg_bg_color'] ) ) {
            $sanitized_input['bot_msg_bg_color'] = sanitize_hex_color( $input['bot_msg_bg_color'] );
        }
        if ( isset( $input['chat_text_color'] ) ) {
            $sanitized_input['chat_text_color'] = sanitize_hex_color( $input['chat_text_color'] );
        }

        // Sanitize text fields
        if ( isset( $input['header_text'] ) ) {
            $sanitized_input['header_text'] = sanitize_text_field( $input['header_text'] );
        }
        if ( isset( $input['initial_bot_message'] ) ) {
            $sanitized_input['initial_bot_message'] = sanitize_text_field( $input['initial_bot_message'] );
        }
        if ( isset( $input['initial_popup_message'] ) ) {
            $sanitized_input['initial_popup_message'] = sanitize_text_field( $input['initial_popup_message'] );
        }

        // Update customization version for cache busting
        $sanitized_input['customization_version'] = time();

        if ( isset( $input['assistant_title'] ) ) {
            $sanitized_input['assistant_title'] = sanitize_text_field( $input['assistant_title'] );
        }

        // Sanitize image fields (SVG/PNG/JPG)
        $image_fields = ['widget_icon_svg', 'header_logo_svg', 'welcome_svg'];
        foreach ($image_fields as $field) {
            if (isset($input[$field]) && !empty($input[$field])) {
                $url = esc_url_raw($input[$field]);
                $file_type = wp_check_filetype($url);
                if (in_array($file_type['ext'], ['svg', 'png', 'jpg', 'jpeg'])) {
                    $sanitized_input[$field] = $url;
                } else {
                    $sanitized_input[$field] = '';
                }
            } else {
                $sanitized_input[$field] = '';
            }
        }

        return $sanitized_input;
    }

    

    /**
     * Print the Section text for General Settings.
     *
     * @since    1.0.0
     */
    public function print_general_section_info() {
        echo '<p>' . esc_html__( 'تنظیمات هسته برای افزونه Novel AI Chatbot، از جمله مدل AI و کلیدهای API.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Print the Section text for Chat Design Settings.
     *
     * @since    1.0.0
     */
    public function print_chat_design_section_info() {
        echo '<p>' . esc_html__( 'تنظیمات ظاهری چت‌باکس خود را سفارشی کنید.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Print the Section text for Shortcode Settings.
     *
     * @since    1.0.0
     */
    public function print_shortcode_section_info() {
        echo '<p>' . esc_html__( 'چگونگی نمایش چت‌باکس را انتخاب کنید. از کد کوتیشن `[novel_ai_chatbot]` برای قرار دادن چت‌باکس مستقیماً در یک پست، صفحه یا ویجت استفاده کنید.', 'novel-ai-chatbot' ) . '</p>';
    }


    /**
     * Renders the Post Types field.
     *
     * @since    1.0.0
     */
    public function post_types_callback() {
        $options = get_option( 'novel_ai_chatbot_options' );
        $selected_post_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array( 'post', 'page' ); // Default to post and page

        $public_post_types = get_post_types( array( 'public' => true ), 'objects' );

        echo '<div class="novel-ai-chatbot-post-types-list">';
        foreach ( $public_post_types as $post_type_object ) {
            // Exclude attachment and nav_menu_item, and any other unwanted post types
            if ( in_array( $post_type_object->name, array( 'attachment', 'nav_menu_item', 'revision', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_navigation' ) ) ) {
                continue;
            }
            $checked = in_array( $post_type_object->name, $selected_post_types ) ? 'checked="checked"' : '';
            echo '<label>';
            echo '<input type="checkbox" name="novel_ai_chatbot_options[post_types][]" value="' . esc_attr( $post_type_object->name ) . '" ' . $checked . ' />';
            echo '<span>' . esc_html( $post_type_object->labels->singular_name ) . ' (' . esc_html( $post_type_object->name ) . ')</span>';
            echo '</label>';
        }
        echo '</div>';
        echo '<p class="description">' . esc_html__( 'انتخاب انواع پست‌های عمومی که باید برای جمع آوری محتوا برای خلاصه سازی AI استفاده شود.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the Sitemap URL field.
     *
     * @since    1.0.0
     */
    public function sitemap_url_callback() {
        $options = get_option( 'novel_ai_chatbot_options' );
        $sitemap_url = isset( $options['sitemap_url'] ) ? esc_attr( $options['sitemap_url'] ) : '';
        echo '<input type="text" id="novel_ai_chatbot_sitemap_url" name="novel_ai_chatbot_options[sitemap_url]" value="' . $sitemap_url . '" class="regular-text" placeholder="' . esc_attr( home_url( '/sitemap.xml' ) ) . '">';
        echo '<div id="novel-ai-chatbot-sitemap-info" class="sitemap-info"><span class="spinner is-active"></span> ' . esc_html__( 'شناسایی sitemap.xml...', 'novel-ai-chatbot' ) . '</div>';
        echo '<p class="description">' . esc_html__( 'آدرس کامل سایت مپینگ خود را وارد کنید. اگر خالی باشد، پلاگین خودکار شناسایی خواهد کرد.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the AI Model selection field.
     *
     * @since    1.0.0
     */
    public function ai_model_callback() {
        $options = get_option( 'novel_ai_chatbot_options' );
        $selected_ai_model = isset( $options['ai_model'] ) ? esc_attr( $options['ai_model'] ) : 'gemini'; // Default to Gemini

        $ai_models = array(
            'openai'   => __( 'OpenAI', 'novel-ai-chatbot' ),
            'gemini'   => __( 'Gemini', 'novel-ai-chatbot' ),
            'deepseek' => __( 'Deepseek', 'novel-ai-chatbot' ),
        );

        echo '<div class="novel-ai-chatbot-ai-selection">';
        foreach ( $ai_models as $value => $label ) {
            $checked = ( $selected_ai_model === $value ) ? 'checked="checked"' : '';
            echo '<label>';
            echo '<input type="radio" name="novel_ai_chatbot_options[ai_model]" value="' . esc_attr( $value ) . '" ' . $checked . ' />';
            echo '<span>' . esc_html( $label ) . '</span>';
            echo '<div class="ai-logo ' . esc_attr( $value ) . '"></div>'; // Placeholder for AI logos
            echo '</label>';
        }
        echo '</div>';
        echo '<p class="description">' . esc_html__( 'انتخاب مدل AI برای قدرت چت‌باکس خود.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the OpenAI API Key field.
     *
     * @since 1.0.0
     */
    public function openai_api_key_callback() {
        $options = get_option( 'novel_ai_chatbot_options' );
        $api_key = isset( $options['openai_api_key'] ) ? esc_attr( $options['openai_api_key'] ) : '';
        echo '<input type="text" id="novel_ai_chatbot_openai_api_key" name="novel_ai_chatbot_options[openai_api_key]" value="' . $api_key . '" class="regular-text" placeholder="' . esc_attr__( 'Enter your OpenAI API Key', 'novel-ai-chatbot' ) . '">';
        echo '<p class="description">' . sprintf(
            esc_html__( 'دریافت کلید API خود از %s.', 'novel-ai-chatbot' ),
            '<a href="https://platform.openai.com/account/api-keys" target="_blank">' . esc_html__( 'OpenAI Platform', 'novel-ai-chatbot' ) . '</a>'
        ) . '</p>';
    }

    /**
     * Renders the Gemini API Key field.
     *
     * @since 1.0.0
     */
    public function gemini_api_key_callback() {
        $options = get_option( 'novel_ai_chatbot_options' );
        $api_key = isset( $options['gemini_api_key'] ) ? esc_attr( $options['gemini_api_key'] ) : '';
        echo '<input type="text" id="novel_ai_chatbot_gemini_api_key" name="novel_ai_chatbot_options[gemini_api_key]" value="' . $api_key . '" class="regular-text" placeholder="' . esc_attr__( 'Enter your Google Gemini API Key', 'novel-ai-chatbot' ) . '">';
        echo '<p class="description">' . sprintf(
            esc_html__( 'دریافت کلید API خود از %s.', 'novel-ai-chatbot' ),
            '<a href="https://ai.google.dev/gemini-api/docs/api-key" target="_blank">' . esc_html__( 'Google AI Studio', 'novel-ai-chatbot' ) . '</a>'
        ) . '</p>';
    }

    /**
     * Renders the Deepseek API Key field.
     *
     * @since 1.0.0
     */
    public function deepseek_api_key_callback() {
        $options = get_option( 'novel_ai_chatbot_options' );
        $api_key = isset( $options['deepseek_api_key'] ) ? esc_attr( $options['deepseek_api_key'] ) : '';
        echo '<input type="text" id="novel_ai_chatbot_deepseek_api_key" name="novel_ai_chatbot_options[deepseek_api_key]" value="' . $api_key . '" class="regular-text" placeholder="' . esc_attr__( 'Enter your Deepseek API Key', 'novel-ai-chatbot' ) . '">';
        echo '<p class="description">' . sprintf(
            esc_html__( 'دریافت کلید API خود از %s.', 'novel-ai-chatbot' ),
            '<a href="https://www.deepseek.com/api" target="_blank">' . esc_html__( 'Deepseek Platform', 'novel-ai-chatbot' ) . '</a>'
        ) . '</p>';
    }

    /**
     * Renders the Chatbox Display Method field.
     *
     * @since 1.0.0
     */
    public function chatbox_display_method_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $selected_method = isset( $options['display_method'] ) ? esc_attr( $options['display_method'] ) : 'floating';

        echo '<label>';
        echo '<input type="radio" name="novel_ai_chatbot_chat_customization_options[display_method]" value="floating" ' . checked( $selected_method, 'floating', false ) . ' />';
        echo ' ' . esc_html__( 'دکمه چت‌باکس چینشی (پیشفرض)', 'novel-ai-chatbot' );
        echo '</label><br>';

        echo '<label>';
        echo '<input type="radio" name="novel_ai_chatbot_chat_customization_options[display_method]" value="shortcode" ' . checked( $selected_method, 'shortcode', false ) . ' />';
        echo ' ' . esc_html__( 'قرار دادن با کد کوتیشن [novel_ai_chatbot]', 'novel-ai-chatbot' );
        echo '</label>';

        echo '<p class="description">' . esc_html__( 'چگونگی نمایش چت‌باکس را انتخاب کنید. "Floating" یک حبه چت در گوشه نمایش خواهد داد. "Shortcode" اجازه می‌دهد چت‌باکس را هر جایی که می‌خواهید قرار دهید با استفاده از `[novel_ai_chatbot]` قرار دهید.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the Widget Position selection field.
     *
     * @since 1.2.0
     */
     public function widget_position_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $position = isset( $options['widget_position'] ) ? esc_attr( $options['widget_position'] ) : 'bottom-right';
        echo '<select name="novel_ai_chatbot_chat_customization_options[widget_position]">';
        echo '<option value="bottom-right" ' . selected( $position, 'bottom-right', false ) . '>' . esc_html__( 'پایین-راست', 'novel-ai-chatbot' ) . '</option>';
        echo '<option value="bottom-left" ' . selected( $position, 'bottom-left', false ) . '>' . esc_html__( 'پایین-چپ', 'novel-ai-chatbot' ) . '</option>';
        echo '</select>';
    }

    /**
     * Renders the Chatbox Primary Color field.
     *
     * @since 1.0.0
     */
    public function chat_primary_color_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $color = isset( $options['chat_primary_color'] ) ? esc_attr( $options['chat_primary_color'] ) : '#3B82F6';
        echo '<input type="text" class="novel-ai-chatbot-color-picker" name="novel_ai_chatbot_chat_customization_options[chat_primary_color]" value="' . $color . '" data-default-color="#3B82F6" />';
        echo '<p class="description">' . esc_html__( 'انتخاب رنگ اصلی برای سرچت‌باکس، دکمه‌ها و دکمه تعویض.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the Chatbox Background Color field.
     *
     * @since    1.0.0
     */
    public function chat_bg_color_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $color = isset( $options['chat_bg_color'] ) ? esc_attr( $options['chat_bg_color'] ) : '#ffffff';
        echo '<input type="text" class="novel-ai-chatbot-color-picker" name="novel_ai_chatbot_chat_customization_options[chat_bg_color]" value="' . $color . '" data-default-color="#ffffff" />';
        echo '<p class="description">' . esc_html__( 'انتخاب رنگ پس‌زمینه برای جسم چت‌باکس.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the User Message Background Color field.
     *
     * @since 1.0.0
     */
    public function user_msg_bg_color_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $color = isset( $options['user_msg_bg_color'] ) ? esc_attr( $options['user_msg_bg_color'] ) : '#e6f0ff';
        echo '<input type="text" class="novel-ai-chatbot-color-picker" name="novel_ai_chatbot_chat_customization_options[user_msg_bg_color]" value="' . $color . '" data-default-color="#e6f0ff" />';
        echo '<p class="description">' . esc_html__( 'انتخاب رنگ پس‌زمینه برای پیام‌های ارسالی توسط کاربر.', 'novel-ai-chatbot' ) . '</p>';
    }

     /**
     * Renders the Bot Message Background Color field.
     *
     * @since 1.0.0
     */
    public function bot_msg_bg_color_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $color = isset( $options['bot_msg_bg_color'] ) ? esc_attr( $options['bot_msg_bg_color'] ) : '#f0f0f0';
        echo '<input type="text" class="novel-ai-chatbot-color-picker" name="novel_ai_chatbot_chat_customization_options[bot_msg_bg_color]" value="' . $color . '" data-default-color="#f0f0f0" />';
        echo '<p class="description">' . esc_html__( 'انتخاب رنگ پس‌زمینه برای پیام‌های دریافتی از ربات.', 'novel-ai-chatbot' ) . '</p>';
    }


    /**
     * Renders the Chatbox Text Color field.
     *
     * @since    1.0.0
     */
    public function chat_text_color_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $color = isset( $options['chat_text_color'] ) ? esc_attr( $options['chat_text_color'] ) : '#333333';
        echo '<input type="text" class="novel-ai-chatbot-color-picker" name="novel_ai_chatbot_chat_customization_options[chat_text_color]" value="' . $color . '" data-default-color="#333333" />';
        echo '<p class="description">' . esc_html__( 'انتخاب رنگ متن پیشفرض برای پیام‌های در چت‌باکس.', 'novel-ai-chatbot' ) . '</p>';
    }

     /**
     * Renders the Chatbox Header Text field.
     *
     * @since 1.0.0
     */
    public function header_text_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $text = isset( $options['header_text'] ) ? esc_attr( $options['header_text'] ) : __( 'Novel AI Chatbot', 'novel-ai-chatbot' );
        echo '<input type="text" id="novel_ai_chatbot_header_text" name="novel_ai_chatbot_chat_customization_options[header_text]" value="' . $text . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'متنی که در سرچت‌باکس نمایش داده خواهد شد.', 'novel-ai-chatbot' ) . '</p>';
    }

   /**
     * Renders the Initial Bot Message field.
     *
     * @since 1.0.0
     */
    public function initial_bot_message_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $text = isset( $options['initial_bot_message'] ) ? esc_attr( $options['initial_bot_message'] ) : __( 'Hello! I am your assistant. Ask me anything.', 'novel-ai-chatbot' );
        echo '<input type="text" id="novel_ai_chatbot_initial_bot_message" name="novel_ai_chatbot_chat_customization_options[initial_bot_message]" value="' . $text . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'پیام اولیه که ربات در هنگام باز شدن چت‌باکس نمایش خواهد داد.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the Initial Floating Popup Message field.
     *
     * @since 1.0.0
     */
    public function initial_popup_message_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $text = isset( $options['initial_popup_message'] ) ? esc_attr( $options['initial_popup_message'] ) : __( 'Hi there! How can I help you today?', 'novel-ai-chatbot' );
        echo '<input type="text" id="novel_ai_chatbot_initial_popup_message" name="novel_ai_chatbot_chat_customization_options[initial_popup_message]" value="' . $text . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'پیامی که در صفحه کوچک چت‌باکس بالای آیکون چت نمایش خواهد شد.', 'novel-ai-chatbot' ) . '</p>';
    }

    /**
     * Renders the Assistant Title field.
     *
     * @since 1.0.0
     */
    public function assistant_title_callback() {
        $options = get_option( 'novel_ai_chatbot_chat_customization_options' );
        $text = isset( $options['assistant_title'] ) ? esc_attr( $options['assistant_title'] ) : __( 'I am your chatbot assistant', 'novel-ai-chatbot' );
        echo '<input type="text" name="novel_ai_chatbot_chat_customization_options[assistant_title]" value="' . $text . '" class="regular-text" />';
        echo '<p class="description">متنی که در صفحه خوش آمدگویی چت‌باکس نمایش خواهد شد (مثلاً "I am your chatbot assistant").</p>';
    }

    /**
     * Render the analytics page for the plugin.
     */
    public function display_analytics_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/novel-ai-chatbot-analytics-display.php';
    }

     // Callbacks for the new placeholder pages
    public function display_operator_registration_page() { echo '<div class="wrap"><h1>ثبت اپراتور</h1><p>این قابلیت در مراحل بعدی پیاده‌سازی خواهد شد.</p></div>'; }
    public function display_role_management_page() {
        // Define our custom capabilities and their Persian labels
        $nac_capabilities = [
            'view_chatbot_settings'     => 'مشاهده تنظیمات',
            'manage_live_chat'          => 'مدیریت چت آنلاین',
            'view_chatbot_history'      => 'مشاهده تاریخچه چت‌ها',
            'view_chatbot_analytics'    => 'مشاهده تحلیل‌ها',
            'manage_chatbot_operators'  => 'مدیریت اپراتورها',
            'manage_chatbot_roles'      => 'مدیریت دسترسی‌ها',
        ];

        // Handle form submission
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['nac_roles_nonce'] ) ) {
            if ( wp_verify_nonce( $_POST['nac_roles_nonce'], 'nac_save_roles_action' ) ) {
                $submitted_caps = isset( $_POST['nac_role_caps'] ) ? (array) $_POST['nac_role_caps'] : array();

                // Loop through all editable roles
                foreach ( get_editable_roles() as $role_slug => $role_details ) {
                    // We never change the administrator role to prevent lock-out
                    if ( 'administrator' === $role_slug ) {
                        continue;
                    }
                    $role = get_role( $role_slug );
                    // Loop through our custom capabilities
                    foreach ( $nac_capabilities as $cap => $label ) {
                        // If the capability was checked for this role, add it. Otherwise, remove it.
                        if ( isset( $submitted_caps[ $role_slug ][ $cap ] ) ) {
                            $role->add_cap( $cap );
                        } else {
                            $role->remove_cap( $cap );
                        }
                    }
                }
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'دسترسی‌ها با موفقیت به‌روزرسانی شدند.', 'novel-ai-chatbot' ) . '</p></div>';
            }
        }
        
        // Pass capabilities to the partial file and include it
        include_once 'partials/novel-ai-chatbot-roles-display.php';
    }

     /**
     * Adds type="module" attribute to our specific admin script tags for proper Vue loading.
     */
    public function add_type_attribute_to_admin_scripts( $tag, $handle ) {
        $module_handles = [
            $this->plugin_name . '-live-chat-app',
            $this->plugin_name . '-history-app',
            $this->plugin_name . '-analytics-app'
        ];
        if ( in_array( $handle, $module_handles ) ) {
            // A more robust way to add the attribute
            $tag = str_replace( " src=", " type='module' src=", $tag );
        }
        return $tag;
    }

    /**
     * Handles and displays the Operator Management page.
     * Allows admins to add new users with the 'nac_operator' role.
     */
    public function display_operator_management_page() {
        // Handle the form submission for adding a new operator
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['nac_add_operator_nonce'] ) ) {
            if ( ! wp_verify_nonce( $_POST['nac_add_operator_nonce'], 'nac_add_operator_action' ) ) {
                // Nonce is invalid
                echo '<div class="notice notice-error"><p>' . esc_html__( 'خطای امنیتی. لطفاً دوباره تلاش کنید.', 'novel-ai-chatbot' ) . '</p></div>';
            } else {
                // Sanitize and validate input
                $username = sanitize_user( $_POST['username'] );
                $email = sanitize_email( $_POST['email'] );
                $password = $_POST['password']; // Password will be handled by wp_create_user
                
                $errors = array();
                if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
                    $errors[] = __( 'تمام فیلدها الزامی هستند.', 'novel-ai-chatbot' );
                }
                if ( ! is_email( $email ) ) {
                    $errors[] = __( 'ایمیل وارد شده معتبر نیست.', 'novel-ai-chatbot' );
                }
                if ( username_exists( $username ) ) {
                    $errors[] = __( 'این نام کاربری قبلاً استفاده شده است.', 'novel-ai-chatbot' );
                }
                if ( email_exists( $email ) ) {
                    $errors[] = __( 'این ایمیل قبلاً استفاده شده است.', 'novel-ai-chatbot' );
                }

                if ( empty( $errors ) ) {
                    // Create the user
                    $user_id = wp_create_user( $username, $password, $email );
                    if ( ! is_wp_error( $user_id ) ) {
                        // Set the user's role to 'nac_operator'
                        $user = new WP_User( $user_id );
                        $user->set_role( 'nac_operator' );
                        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'اپراتور %s با موفقیت ایجاد شد.', 'novel-ai-chatbot' ), '<strong>' . esc_html( $username ) . '</strong>' ) . '</p></div>';
                    } else {
                        // Display error from wp_create_user
                        echo '<div class="notice notice-error"><p>' . esc_html( $user_id->get_error_message() ) . '</p></div>';
                    }
                } else {
                    // Display validation errors
                    echo '<div class="notice notice-error"><p>' . implode( '<br>', array_map( 'esc_html', $errors ) ) . '</p></div>';
                }
            }
        }
        
        // Include the partial file for display
        include_once 'partials/novel-ai-chatbot-operators-display.php';
    }
}