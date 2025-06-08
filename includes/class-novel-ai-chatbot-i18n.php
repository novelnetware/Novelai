<?php

/**
 * Defines the internationalization functionality
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 */

/**
 * Defines the internationalization functionality.
 *
 * Loads the plugin’s translated strings.
 *
 * @since      1.0.0
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 * @author     Ali <ali@example.com>
 */
class Novel_AI_Chatbot_i18n {


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'novel-ai-chatbot',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );

    }



}