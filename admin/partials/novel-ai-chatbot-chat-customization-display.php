<?php
/**
 * نمایش صفحه سفارشی‌سازی چت‌بات در بخش مدیریت
 *
 * این فایل برای نمایش بخش سفارشی‌سازی ظاهر و تنظیمات چت‌بات در پیشخوان وردپرس استفاده می‌شود.
 *
 * @link        https://example.com/
 * @since       1.0.0
 *
 * @package     Novel_AI_Chatbot
 * @subpackage  Novel_AI_Chatbot/admin/partials
 */

// Ensure WordPress media scripts are enqueued
if (!did_action('wp_enqueue_media')) {
    wp_enqueue_media();
}

$chat_customization_options = get_option('novel_ai_chatbot_chat_customization_options', array());
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap');
body, .wrap, .novel-ai-chatbot-settings-page, .form-table, .novel-ai-chatbot-post-types-list, .novel-ai-chatbot-ai-selection, .sitemap-info {
    font-family: 'Vazirmatn', Tahoma, Arial, sans-serif !important;
}
</style>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('novel_ai_chatbot_chat_customization_group');
        do_settings_sections('novel-ai-chatbot-chat-customization');
        ?>
        
        <h2 class="title">سفارشی‌سازی SVG/PNG</h2>
        <div class="card">
            <p class="description">
                <?php _e('انتخاب عکس‌های SVG یا PNG برای آیکون چت، لوگوی صفحه اصلی و پیش نمایش صفحه خوش آمدگویی. فقط فایل‌های SVG و PNG مجوز استفاده هستند.', 'novel-ai-chatbot'); ?>
                <br>
                <?php _e('پشتیبانی:', 'novel-ai-chatbot'); ?> <a href="mailto:info@novelnetware.com">info@novelnetware.com</a>
            </p>
            
            <table class="form-table">
                <!-- Widget Icon Field -->
                <tr>
                    <th scope="row"><label><?php _e('آیکون چت', 'novel-ai-chatbot'); ?></label></th>
                    <td>
                        <div class="upload-field-wrapper">
                            <input type="hidden" name="novel_ai_chatbot_chat_customization_options[widget_icon_svg]" id="novel_ai_chatbot_widget_icon_svg" value="<?php echo esc_attr($chat_customization_options['widget_icon_svg'] ?? ''); ?>" />
                            <button type="button" class="button button-primary select-media-button" data-target="novel_ai_chatbot_widget_icon_svg" data-preview="widget-icon-svg-preview" data-width="60" data-height="60">
                                <?php _e('انتخاب عکس', 'novel-ai-chatbot'); ?>
                            </button>
                            <button type="button" class="button button-secondary remove-media-button" data-target="novel_ai_chatbot_widget_icon_svg" data-preview="widget-icon-svg-preview" style="<?php echo empty($chat_customization_options['widget_icon_svg']) ? 'display:none;' : ''; ?>">
                                <?php _e('حذف عکس', 'novel-ai-chatbot'); ?>
                            </button>
                            <div id="widget-icon-svg-preview" class="image-preview-container">
                                <?php if (!empty($chat_customization_options['widget_icon_svg'])) : ?>
                                    <img src="<?php echo esc_url($chat_customization_options['widget_icon_svg']); ?>?t=<?php echo time(); ?>" width="60" height="60" class="image-preview" alt="<?php _e('Widget Icon Preview', 'novel-ai-chatbot'); ?>" />
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                
                <!-- Header Logo Field -->
                <tr>
                    <th scope="row"><label><?php _e('لوگوی صفحه اصلی', 'novel-ai-chatbot'); ?></label></th>
                    <td>
                        <div class="upload-field-wrapper">
                            <input type="hidden" name="novel_ai_chatbot_chat_customization_options[header_logo_svg]" id="novel_ai_chatbot_header_logo_svg" value="<?php echo esc_attr($chat_customization_options['header_logo_svg'] ?? ''); ?>" />
                            <button type="button" class="button button-primary select-media-button" data-target="novel_ai_chatbot_header_logo_svg" data-preview="header-logo-svg-preview" data-width="100" data-height="40">
                                <?php _e('انتخاب عکس', 'novel-ai-chatbot'); ?>
                            </button>
                            <button type="button" class="button button-secondary remove-media-button" data-target="novel_ai_chatbot_header_logo_svg" data-preview="header-logo-svg-preview" style="<?php echo empty($chat_customization_options['header_logo_svg']) ? 'display:none;' : ''; ?>">
                                <?php _e('حذف عکس', 'novel-ai-chatbot'); ?>
                            </button>
                            <div id="header-logo-svg-preview" class="image-preview-container">
                                <?php if (!empty($chat_customization_options['header_logo_svg'])) : ?>
                                    <img src="<?php echo esc_url($chat_customization_options['header_logo_svg']); ?>?t=<?php echo time(); ?>" width="100" height="40" class="image-preview" alt="<?php _e('Header Logo Preview', 'novel-ai-chatbot'); ?>" />
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                
                <!-- Welcome SVG Field -->
                <tr>
                    <th scope="row"><label><?php _e('پیش نمایش عکس خوش آمدگویی', 'novel-ai-chatbot'); ?></label></th>
                    <td>
                        <div class="upload-field-wrapper">
                            <input type="hidden" name="novel_ai_chatbot_chat_customization_options[welcome_svg]" id="novel_ai_chatbot_welcome_svg" value="<?php echo esc_attr($chat_customization_options['welcome_svg'] ?? ''); ?>" />
                            <button type="button" class="button button-primary select-media-button" data-target="novel_ai_chatbot_welcome_svg" data-preview="welcome-svg-preview" data-width="100" data-height="100">
                                <?php _e('انتخاب عکس', 'novel-ai-chatbot'); ?>
                            </button>
                            <button type="button" class="button button-secondary remove-media-button" data-target="novel_ai_chatbot_welcome_svg" data-preview="welcome-svg-preview" style="<?php echo empty($chat_customization_options['welcome_svg']) ? 'display:none;' : ''; ?>">
                                <?php _e('حذف عکس', 'novel-ai-chatbot'); ?>
                            </button>
                            <div id="welcome-svg-preview" class="image-preview-container">
                                <?php if (!empty($chat_customization_options['welcome_svg'])) : ?>
                                    <img src="<?php echo esc_url($chat_customization_options['welcome_svg']); ?>?t=<?php echo time(); ?>" width="100" height="100" class="image-preview" alt="<?php _e('پیش نمایش عکس', 'novel-ai-chatbot'); ?>" />
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(__('ثبت تغییرات', 'novel-ai-chatbot'), 'primary large'); ?>
    </form>
</div>

<!-- JavaScript for instant updates -->
<script>
jQuery(document).ready(function($) {
    // Initialize media uploader for all image fields
    $('.select-media-button').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var targetInput = $button.data('target');
        var previewId = $button.data('preview');
        var width = $button.data('width');
        var height = $button.data('height');
        
        // Create media frame
        var frame = wp.media({
            title: '<?php _e("انتخاب عکس", "novel-ai-chatbot"); ?>',
            button: { text: '<?php _e("Use this image", "novel-ai-chatbot"); ?>' },
            multiple: false,
            library: { type: 'image' }
        });
        
        // When image is selected
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            var fileExt = attachment.filename.split('.').pop().toLowerCase();
            
            // Validate file type
            if (['svg', 'png', 'jpg', 'jpeg'].includes(fileExt)) {
                // Update both hidden and URL input fields instantly
                $('#' + targetInput).val(attachment.url).trigger('change');
                
                // Update preview with cache buster
                var previewUrl = attachment.url + (attachment.url.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now();
                var previewHtml = '<img src="' + previewUrl + '" width="' + width + '" height="' + height + '" class="image-preview" />';
                
                $('#' + previewId).html(previewHtml);
                
                // Show remove button if not exists
                if (!$button.siblings('.remove-media-button').length) {
                    $button.after(
                        '<button type="button" class="button button-secondary remove-media-button" ' +
                        'data-target="' + targetInput + '" data-preview="' + previewId + '">' +
                        '<?php _e("Remove File", "novel-ai-chatbot"); ?>' +
                        '</button>'
                    );
                }
            } else {
                alert('<?php _e("فقط فایل‌های SVG, PNG, JPG مجوز استفاده هستند.", "novel-ai-chatbot"); ?>');
            }
        });
        
        frame.open();
    });
    
    // Handle image removal
    $(document).on('click', '.remove-media-button', function() {
        var $button = $(this);
        var targetInput = $button.data('target');
        var previewId = $button.data('preview');
        
        // Clear both input fields instantly
        $('#' + targetInput).val('').trigger('change');
        
        // Clear preview
        $('#' + previewId).html('');
        
        // Remove remove button
        $button.remove();
    });
});
</script>

<style>
.image-preview-container {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    min-height: 60px;
    min-width: 60px;
    max-width: 120px;
    max-height: 120px;
    background: #f8f8f8;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    margin-top: 4px;
    overflow: hidden;
}
.image-preview {
    display: block;
    width: 60px;
    height: 60px;
    object-fit: contain;
    background: transparent;
    margin: 0 auto;
}
#header-logo-svg-preview .image-preview {
    width: 100px;
    height: 40px;
}
#welcome-svg-preview .image-preview {
    width: 100px;
    height: 100px;
}
</style>