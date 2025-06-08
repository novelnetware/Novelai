<?php
/**
 * نمایش صفحه تنظیمات افزونه در بخش مدیریت
 *
 * این فایل برای نمایش بخش تنظیمات افزونه در پیشخوان وردپرس استفاده می‌شود.
 *
 * @link        https://example.com/
 * @since       1.0.0
 *
 * @package     Novel_AI_Chatbot
 * @subpackage  Novel_AI_Chatbot/admin/partials
 */
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap');
body, .wrap, .novel-ai-chatbot-settings-page, .form-table, .novel-ai-chatbot-post-types-list, .novel-ai-chatbot-ai-selection, .sitemap-info {
    font-family: 'Vazirmatn', Tahoma, Arial, sans-serif !important;
}
</style>

<div class="wrap novel-ai-chatbot-settings-page" style="direction: rtl; text-align: right;">
    <h1>تنظیمات نووِل چَت بات</h1>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'novel_ai_chatbot_settings_group' ); // Settings group name
        do_settings_sections( 'novel-ai-chatbot-settings' );  // Page slug
        submit_button();
        ?>
    </form>
</div>