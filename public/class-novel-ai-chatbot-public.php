<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/public
 * @author     Ali <ali@example.com>
 */
class Novel_AI_Chatbot_Public {

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

    /**
     * @var Novel_AI_Chatbot_Chat_History $chat_history Instance of the chat history handler.
     */
    private $chat_history;


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
        $this->chat_history = new Novel_AI_Chatbot_Chat_History( $plugin_name, $version );
        add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        if (is_admin()) return;
        $custom_options = get_option('novel_ai_chatbot_chat_customization_options', array());
        $version = isset($custom_options['customization_version']) ? $custom_options['customization_version'] : $this->version;
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/novel-ai-chatbot-public.css', array(), $version, 'all' );

        // Apply dynamic styles from customization options using CSS variables
        $primary_color = isset($custom_options['chat_primary_color']) ? sanitize_hex_color($custom_options['chat_primary_color']) : '#3B82F6';
        $bg_color = isset($custom_options['chat_bg_color']) ? sanitize_hex_color($custom_options['chat_bg_color']) : '#ffffff';
        $user_msg_bg_color = isset($custom_options['user_msg_bg_color']) ? sanitize_hex_color($custom_options['user_msg_bg_color']) : '#e6f0ff';
        $bot_msg_bg_color = isset($custom_options['bot_msg_bg_color']) ? sanitize_hex_color($custom_options['bot_msg_bg_color']) : '#f0f0f0';
        $text_color = isset($custom_options['chat_text_color']) ? sanitize_hex_color($custom_options['chat_text_color']) : '#333333';
        
        // **NEW**: Get widget position
        $widget_position = isset($custom_options['widget_position']) ? $custom_options['widget_position'] : 'bottom-right';
        $position_css = '';
        if ($widget_position === 'bottom-left') {
            $position_css = "
                #novel-ai-chatbot-app {
                    right: auto;
                    left: 20px;
                }
            ";
        } else {
            $position_css = "
                #novel-ai-chatbot-app {
                    left: auto;
                    right: 20px;
                }
            ";
        }

        $custom_css = "
            :root {
                --novel-ai-chatbot-primary-color: {$primary_color};
                --novel-ai-chatbot-bg-color: {$bg_color};
                --novel-ai-chatbot-user-msg-bg-color: {$user_msg_bg_color};
                --novel-ai-chatbot-bot-msg-bg-color: {$bot_msg_bg_color};
                --novel-ai-chatbot-text-color: {$text_color};
            }
            {$position_css}
        ";
        wp_add_inline_style( $this->plugin_name, $custom_css );
    }

    /**
     * Darkens or lightens a hex color.
     * @param string $hex The hex color (e.g., #RRGGBB or #RGB).
     * @param int $percent Percentage to darken (negative for lighten).
     * @return string The new hex color.
     */
    private function darken_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $rgb = array_map('hexdec', str_split($hex, 2));

        foreach ($rgb as &$color) {
            $color = (int) round(max(0, min(255, $color + ($color * $percent / 100))));
        }

        return '#' . implode('', array_map(function($c) {
            return str_pad(dechex($c), 2, '0', STR_PAD_LEFT);
        }, $rgb));
    }


    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
   public function enqueue_scripts() {
        if (is_admin()) return;

        $custom_options = get_option('novel_ai_chatbot_chat_customization_options', array());
        $version = isset($custom_options['customization_version']) ? $custom_options['customization_version'] : time();
        
        // Load our main script. Note that 'vue' is removed from dependencies.
       wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/novel-ai-chatbot-public.js', array( 'jquery' ), $version, true );


        $display_method = isset( $custom_options['display_method'] ) ? $custom_options['display_method'] : 'floating';
        $initial_bot_message_text = isset( $custom_options['initial_bot_message'] ) ? esc_html( $custom_options['initial_bot_message'] ) : esc_html__( 'Hello there! Ask me anything about this website.', 'novel-ai-chatbot' );
        $initial_popup_message_text = isset( $custom_options['initial_popup_message'] ) ? esc_html( $custom_options['initial_popup_message'] ) : esc_html__( 'Hello! How can I help you today?', 'novel-ai-chatbot' );

        // Generate session ID for the current user/session
        $session_id = $this->chat_history->generate_session_id();

        // Localize script to pass PHP variables to JavaScript
        wp_localize_script(
            $this->plugin_name,
            'novel_ai_chatbot_public_vars',
            array(
                'ajax_url'            => admin_url( 'admin-ajax.php' ),
                'nonce'               => wp_create_nonce( 'novel-ai-chatbot-public-nonce' ),
                'errorMessage'        => __( 'متاسفانه یک مشکل رخ داده است.', 'novel-ai-chatbot' ),
                'unknownError'        => __( 'یک خطای نامشخص رخ داده است.', 'novel-ai-chatbot' ),
                'networkError'        => __( 'خطای شبکه. لطفا دوباره تلاش کنید.', 'novel-ai-chatbot' ),
                'display_method'      => $display_method,
                'session_id'          => $session_id,
                'initialBotMessage'   => $initial_bot_message_text,
                'initialPopupMessage' => $initial_popup_message_text,
                'loadingHistory'      => __( 'در حال بارگذاری تاریخچه چت...', 'novel-ai-chatbot' ),
                'errorLoadingHistory' => __( 'خطای بارگذاری تاریخچه چت. یک تبادل جدید را شروع کنید.', 'novel-ai-chatbot' ),
                'widget_position'     => isset($custom_options['widget_position']) ? $custom_options['widget_position'] : 'bottom-right',
                'request_failed'      => __( 'ارسال پیام با خطا مواجه شد.', 'novel-ai-chatbot' ),
            )
        );
    }

    /**
     * Renders the chatbox HTML into the footer if the display method is 'floating'.
     *
     * @since 1.0.0
     */
    public function render_chatbox_if_floating() {
        if (is_admin()) return;
        $custom_options = get_option( 'novel_ai_chatbot_chat_customization_options', array() );
        $display_method = isset( $custom_options['display_method'] ) ? $custom_options['display_method'] : 'floating';
        if ( 'floating' === $display_method ) {
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/novel-ai-chatbot-chatbox-display.php';
        }
    }

    /**
     * Shortcode callback to render the chatbox.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string The HTML for the chatbox.
     */
    public function render_chatbox_shortcode( $atts ) {
        if (is_admin()) return '';
        $this->enqueue_styles();
        $this->enqueue_scripts();
        $custom_options = get_option( 'novel_ai_chatbot_chat_customization_options', array() );
        $display_method = isset( $custom_options['display_method'] ) ? $custom_options['display_method'] : 'floating';
        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/novel-ai-chatbot-chatbox-display.php';
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Adds the chatbox HTML to the content using a filter, if the display method is shortcode.
     * This is an alternative way to ensure the chatbox is included when the shortcode is used
     * without explicit shortcode in post content. Can be removed if only explicit shortcode is desired.
     * This is just for demonstration of filtering 'the_content'.
     *
     * @since 1.0.0
     * @param string $content The post content.
     * @return string The modified content.
     */
    public function add_chatbox_shortcode_output( $content ) {
        $custom_options = get_option( 'novel_ai_chatbot_chat_customization_options', array() );
        $display_method = isset( $custom_options['display_method'] ) ? $custom_options['display_method'] : 'floating';

        // This filter adds the chatbox if the shortcode method is selected AND the shortcode isn't already in content.
        // It's generally better to let the user explicitly use the shortcode.
        // So, consider removing this method if you only want the shortcode to work via explicit placement.
        if ( 'shortcode' === $display_method && ! has_shortcode( $content, 'novel_ai_chatbot' ) ) {
            // For now, let's not auto-inject via filter if shortcode is chosen.
            // Users should explicitly place the shortcode.
            // This method can be kept for other purposes or removed.
        }
        return $content;
    }
    /**
     * Adds type="module" attribute to our specific script tag.
     *
     * @since 1.4.0
     * @param string $tag    The <script> tag for the enqueued script.
     * @param string $handle The script's handle.
     * @param string $src    The script's source URL.
     * @return string The modified <script> tag.
     */
    public function add_type_attribute( $tag, $handle, $src ) {
    if ( $this->plugin_name === $handle ) {
        // Ensure the tag is correctly formed with type="module"
        $tag = '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr($handle) . '-js"></script>';
    }
    return $tag;
}
}