/**
 * Plugin Name: Novel AI Chatbot
 * Plugin URI: https://novelnetware.com/novel-ai-chatbot
 * Description: Enhanced media selection JS for the admin area of the Novel AI Chatbot plugin.
 * Version: 1.7.3
 * Author: Novelnetware
 * Support Email: info@novelnetware.com
 */
(function($) {
    'use strict';

    // This is the recommended way to write jQuery code for WordPress.
    $(function() {

        // Configuration object for media fields
        const mediaFields = {
            'novel_ai_chatbot_widget_icon_svg': {
                previewId: 'widget-icon-svg-preview',
                dimensions: { width: 60, height: 60 }
            },
            'novel_ai_chatbot_header_logo_svg': {
                previewId: 'header-logo-svg-preview',
                dimensions: { width: 100, height: 40 }
            },
            'novel_ai_chatbot_welcome_svg': {
                previewId: 'welcome-svg-preview',
                dimensions: { width: 100, height: 100 }
            }
        };

        // Allowed file extensions
        const allowedExtensions = ['svg', 'png', 'jpg', 'jpeg'];

        /**
         * Update preview for a media field
         * @param {string} inputId - The ID of the input field
         */
        function updatePreview(inputId) {
            const config = mediaFields[inputId];
            if (!config) return;

            const $input = $('#' + inputId);
            const $preview = $('#' + config.previewId);
            const url = $input.val();
            
            if (!url) {
                $preview.html('');
                return;
            }

            const cacheBusterUrl = url + (url.includes('?') ? '&' : '?') + 't=' + Date.now();
            const ext = url.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(ext)) {
                $preview.html('<div class="notice notice-error inline"><p>' + 
                    'Invalid file type. Only SVG, PNG, JPG allowed.</p></div>');
                return;
            }
            
            const img = $('<img>', {
                src: cacheBusterUrl,
                width: config.dimensions.width,
                height: config.dimensions.height,
                alt: 'Preview',
                class: 'image-preview'
            });

            $preview.html(img);
        }

        /**
         * Handle media selection
         */
        $('.select-media-button').on('click', function(e) {
            e.preventDefault();
            const targetInput = $(this).data('target');
            const config = mediaFields[targetInput];
            
            if (!config) return;

            const frame = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                const ext = attachment.filename.split('.').pop().toLowerCase();

                if (allowedExtensions.includes(ext)) {
                    $('#' + targetInput)
                        .val(attachment.url)
                        .trigger('change');
                    
                    if (!$(this).siblings('.remove-media-button').length) {
                        $(this).after(
                            '<button type="button" class="button button-secondary remove-media-button" ' +
                            'data-target="' + targetInput + '">Remove Image</button>'
                        );
                    }
                } else {
                    alert('Only SVG, PNG, JPG files are allowed. Selected file: ' + attachment.filename);
                }
            });

            frame.open();
        });

        /**
         * Handle image removal
         */
        $(document).on('click', '.remove-media-button', function() {
            const targetInput = $(this).data('target');
            const config = mediaFields[targetInput];
            
            if (!config) return;

            $('#' + targetInput).val('').trigger('change');
            $(this).remove();
        });

        // Initialize previews and set up change handlers
        Object.keys(mediaFields).forEach(inputId => {
            updatePreview(inputId);
            $('#' + inputId).on('change', function() {
                updatePreview(inputId);
            });
        });

    });

})(jQuery);