<?php
/**
 * Plugin Name: Novel AI Chatbot
 * Plugin URI:  https://novelnetware.com/novel-ai-chatbot
 * Description: An intelligent chatbot plugin for WordPress, powered by various AI models.
 * Version:     1.8.2
 * Author:      Novelnetware
 * Author URI:  https://novelnetware.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: novel-ai-chatbot
 * Domain Path: /languages
 * Support Email: info@novelnetware.com
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-novel-ai-chatbot-activator.php
 */
function activate_novel_ai_chatbot() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-novel-ai-chatbot-activator.php';
    Novel_AI_Chatbot_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-novel-ai-chatbot-deactivator.php
 */
function deactivate_novel_ai_chatbot() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-novel-ai-chatbot-deactivator.php';
    Novel_AI_Chatbot_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_novel_ai_chatbot' );
register_deactivation_hook( __FILE__, 'deactivate_novel_ai_chatbot' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-novel-ai-chatbot.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then there is no need to explicitly call any action or filter hook.
 *
 * @since    1.0.0
 */
function run_novel_ai_chatbot() {

    $plugin = new Novel_AI_Chatbot();
    $plugin->run();

}
run_novel_ai_chatbot();