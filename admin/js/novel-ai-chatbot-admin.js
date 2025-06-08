/**
 * Plugin Name: Novel AI Chatbot
 * Plugin URI: https://novelnetware.com/novel-ai-chatbot
 * Description: Enhanced JavaScript for the admin area of the Novel AI Chatbot plugin.
 * Version: 1.7.3
 * Author: Novelnetware
 * Support Email: info@novelnetware.com
 * 
 * Improvements:
 * - Better error handling
 * - Optimized AJAX requests
 * - Added progress tracking
 * - Improved user feedback
 */

(function($) {
    'use strict';

    // This is the recommended way to write jQuery code for WordPress.
    // It replaces jQuery(document).ready(function($) { ... });
    $(function() {

        // Cache DOM elements
        const $sitemapUrlInput = $('#novel_ai_chatbot_sitemap_url');
        const $sitemapInfoDiv = $('#novel-ai-chatbot-sitemap-info');
        const $collectionButton = $('#novel-ai-chatbot-start-collection');
        const $statusDiv = $('#novel-ai-chatbot-collection-status');
        const $progressBar = $('#novel-ai-chatbot-collection-progress');

        // Initialize color pickers
        if ($('.novel-ai-chatbot-color-picker').length) {
            $('.novel-ai-chatbot-color-picker').wpColorPicker();
        }

        /**
         * Detect sitemap.xml dynamically with better error handling
         */
        const detectSitemap = () => {
            const defaultSitemapPath = `${window.location.origin}/sitemap.xml`;
            
            $sitemapInfoDiv.html(`<span class="spinner is-active"></span> ${novel_ai_chatbot_admin_vars.detectingSitemap}`);
            $sitemapInfoDiv.removeClass('error success');

            $.ajax({
                url: novel_ai_chatbot_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'novel_ai_chatbot_check_sitemap',
                    _ajax_nonce: novel_ai_chatbot_admin_vars.nonce,
                    sitemap_url: defaultSitemapPath
                },
                success: (response) => {
                    if (response.success && response.data.found) {
                        $sitemapUrlInput.val(defaultSitemapPath).trigger('change');
                        $sitemapInfoDiv.html(`
                            <span class="dashicons dashicons-yes"></span> 
                            ${novel_ai_chatbot_admin_vars.sitemapDetected}: 
                            <a href="${defaultSitemapPath}" target="_blank">${defaultSitemapPath}</a>
                        `).addClass('success');
                    } else {
                        $sitemapInfoDiv.html(`
                            <span class="dashicons dashicons-no"></span> 
                            ${novel_ai_chatbot_admin_vars.sitemapNotFound}
                        `).addClass('error');
                    }
                },
                error: () => {
                    $sitemapInfoDiv.html(`
                        <span class="dashicons dashicons-warning"></span> 
                        ${novel_ai_chatbot_admin_vars.sitemapError}
                    `).addClass('error');
                }
            });
        };

        // Auto-detect sitemap if field is empty
        if ($sitemapUrlInput.length && !$sitemapUrlInput.val().trim()) {
            detectSitemap();
        }

        // Note: The content collection logic was moved to novel-ai-chatbot-content-collection-display.php
        // and is now more complex. The original JS for it in this file can be removed if you have
        // the newer version in the PHP partial. If you still have the button logic here, it should
        // continue to work inside this safe wrapper.

        // Manual sitemap detection trigger
        $(document).on('click', '#novel-ai-chatbot-detect-sitemap', detectSitemap);

    });

})(jQuery);