<?php
/**
 * نمایش صفحه جمع‌آوری محتوا در بخش مدیریت
 *
 * این فایل برای نمایش بخش جمع‌آوری و پردازش محتوای سایت جهت استفاده در چت‌بات استفاده می‌شود.
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

<div class="wrap" style="direction: rtl; text-align: right;">
    <h1>جمع‌آوری محتوا</h1>
    <p>از این صفحه برای جمع‌آوری و پردازش محتوای سایت جهت استفاده در چت‌بات استفاده کنید. هوش مصنوعی از این اطلاعات برای پاسخ به کاربران بهره می‌برد.</p>
    <p>قبل از شروع جمع‌آوری محتوا، مطمئن شوید کلیدهای API را در بخش «تنظیمات کلی» وارد کرده‌اید.</p>
    <div id="novel-ai-chatbot-content-collection-status">
        <h2 class="title">وضعیت جمع‌آوری</h2>
        <div id="novel-ai-chatbot-progress-bar-container" style="display: none;">
            <div id="novel-ai-chatbot-progress-bar"></div>
            <div id="novel-ai-chatbot-progress-text">0%</div>
        </div>
        <p id="novel-ai-chatbot-collection-message" class="notice notice-info"></p>
        <button id="novel-ai-chatbot-start-collection" class="button button-primary">شروع جمع‌آوری محتوا</button>
        <button id="novel-ai-chatbot-clear-collection" class="button button-secondary" style="margin-right: 10px;">پاک‌سازی محتواهای جمع‌آوری‌شده</button>
        <p class="description">با پاک‌سازی، تمام داده‌های جمع‌آوری‌شده حذف می‌شوند و باید مجدداً جمع‌آوری انجام شود.</p>
        <h3>خلاصه محتواهای جمع‌آوری‌شده</h3>
        <p id="novel-ai-chatbot-summary-count">تعداد کل آیتم‌های جمع‌آوری‌شده: در حال محاسبه...</p>
        <p class="description">در این بخش تعداد آیتم‌های جمع‌آوری و خلاصه‌شده توسط هوش مصنوعی نمایش داده می‌شود.</p>
    </div>
    <hr>
    <h2>تاریخچه جمع‌آوری محتوا (۱۰ مورد آخر)</h2>
    <table class="wp-list-table widefat fixed striped" style="direction: rtl; text-align: right;">
        <thead>
            <tr>
                <th>عنوان</th>
                <th>لینک</th>
                <th>نوع</th>
                <th>آخرین ویرایش</th>
                <th>خلاصه هوش مصنوعی</th>
            </tr>
        </thead>
        <tbody id="novel-ai-chatbot-collection-table-body">
            <tr><td colspan="5">در حال بارگذاری محتوا...</td></tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        const collectionMessage = $('#novel-ai-chatbot-collection-message');
        const startCollectionBtn = $('#novel-ai-chatbot-start-collection');
        const clearCollectionBtn = $('#novel-ai-chatbot-clear-collection');
        const progressBarContainer = $('#novel-ai-chatbot-progress-bar-container');
        const progressBar = $('#novel-ai-chatbot-progress-bar');
        const progressText = $('#novel-ai-chatbot-progress-text');
        const summaryCount = $('#novel-ai-chatbot-summary-count');
        const collectionTableBody = $('#novel-ai-chatbot-collection-table-body');

        let currentOffset = 0;
        let totalItems = 0;
        let isCollecting = false;

        // Function to update collected content summary and table
        function updateCollectionSummary() {
            $.ajax({
                url: novel_ai_chatbot_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'novel_ai_chatbot_get_collection_summary',
                    _ajax_nonce: novel_ai_chatbot_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        summaryCount.text(novel_ai_chatbot_admin_vars.totalCollectedItems + ': ' + response.data.total_collected);
                        collectionTableBody.empty();
                        if (response.data.latest_items && response.data.latest_items.length > 0) {
                            $.each(response.data.latest_items, function(index, item) {
                                const row = `
                                    <tr>
                                        <td>${item.title ? item.title : 'N/A'}</td>
                                        <td><a href="${item.url}" target="_blank">${item.url ? item.url : 'N/A'}</a></td>
                                        <td>${item.type ? item.type : 'N/A'}</td>
                                        <td>${item.date ? item.date : 'N/A'}</td>
                                        <td>${item.summary ? item.summary.substring(0, 100) + '...' : 'Not summarized'}</td>
                                    </tr>
                                `;
                                collectionTableBody.append(row);
                            });
                        } else {
                            collectionTableBody.append('<tr><td colspan="5">' + novel_ai_chatbot_admin_vars.noCollectedContent + '</td></tr>');
                        }
                    } else {
                        summaryCount.text(novel_ai_chatbot_admin_vars.errorLoadingSummary);
                        console.error('خطا در دریافت جمع‌آوری محتوا:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    summaryCount.text(novel_ai_chatbot_admin_vars.networkError);
                    console.error("خطا در دریافت جمع‌آوری محتوا:", status, error);
                }
            });
        }

        // Initial load of collection summary
        updateCollectionSummary();

        startCollectionBtn.on('click', function() {
            if (isCollecting) return;
            isCollecting = true;
            startCollectionBtn.prop('disabled', true).text(novel_ai_chatbot_admin_vars.collectionInProgress);
            clearCollectionBtn.prop('disabled', true);
            collectionMessage.removeClass('notice-error notice-success').addClass('notice-info').text(novel_ai_chatbot_admin_vars.startingCollection);
            progressBarContainer.show();
            progressBar.width('0%');
            progressText.text('0%');
            currentOffset = 0;
            totalItems = 0;
            collectContentBatch();
        });

        clearCollectionBtn.on('click', function() {
            if (!confirm(novel_ai_chatbot_admin_vars.confirmClearCollection)) {
                return;
            }
            clearCollectionBtn.prop('disabled', true);
            collectionMessage.removeClass('notice-info notice-success').addClass('notice-warning').text(novel_ai_chatbot_admin_vars.clearingCollection);

            $.ajax({
                url: novel_ai_chatbot_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'novel_ai_chatbot_clear_collection',
                    _ajax_nonce: novel_ai_chatbot_admin_vars.nonce
                },
                success: function(response) {
                    clearCollectionBtn.prop('disabled', false);
                    if (response.success) {
                        collectionMessage.removeClass('notice-warning').addClass('notice-success').text(response.data.message);
                        updateCollectionSummary(); // Refresh summary after clearing
                    } else {
                        collectionMessage.removeClass('notice-warning').addClass('notice-error').text(novel_ai_chatbot_admin_vars.collectionError + ': ' + (response.data.message || novel_ai_chatbot_admin_vars.unknownError));
                    }
                },
                error: function(xhr, status, error) {
                    clearCollectionBtn.prop('disabled', false);
                    collectionMessage.removeClass('notice-warning').addClass('notice-error').text(novel_ai_chatbot_admin_vars.networkError);
                    console.error("خطا در جمع‌آوری محتوا:", status, error);
                }
            });
        });

        function collectContentBatch() {
            $.ajax({
                url: novel_ai_chatbot_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'novel_ai_chatbot_collect_content',
                    _ajax_nonce: novel_ai_chatbot_admin_vars.nonce,
                    offset: currentOffset
                },
                success: function(response) {
                    if (response.success) {
                        currentOffset = response.data.next_offset;
                        totalItems = response.data.total_pages;

                        const percentage = totalItems > 0 ? Math.min(100, Math.floor((currentOffset / totalItems) * 100)) : 100;
                        progressBar.width(percentage + '%');
                        progressText.text(percentage + '%');

                        collectionMessage.removeClass('notice-error notice-success').addClass('notice-info').text(novel_ai_chatbot_admin_vars.processingContent + ': ' + response.data.processed_pages + '/' + totalItems);

                        if (response.data.finished) {
                            collectionMessage.removeClass('notice-info').addClass('notice-success').text(novel_ai_chatbot_admin_vars.collectionFinished);
                            startCollectionBtn.prop('disabled', false).text(novel_ai_chatbot_admin_vars.startCollection);
                            clearCollectionBtn.prop('disabled', false);
                            progressBarContainer.hide();
                            isCollecting = false;
                            updateCollectionSummary(); // Final refresh
                        } else {
                            collectContentBatch(); // Call next batch
                        }
                    } else {
                        collectionMessage.removeClass('notice-info notice-success').addClass('notice-error').text(novel_ai_chatbot_admin_vars.collectionError + ': ' + (response.data.message || novel_ai_chatbot_admin_vars.unknownError));
                        startCollectionBtn.prop('disabled', false).text(novel_ai_chatbot_admin_vars.startCollection);
                        clearCollectionBtn.prop('disabled', false);
                        progressBarContainer.hide();
                        isCollecting = false;
                    }
                },
                error: function(xhr, status, error) {
                    collectionMessage.removeClass('notice-info notice-success').addClass('notice-error').text(novel_ai_chatbot_admin_vars.ajaxError + ': ' + status + ' ' + error);
                    startCollectionBtn.prop('disabled', false).text(novel_ai_chatbot_admin_vars.startCollection);
                    clearCollectionBtn.prop('disabled', false);
                    progressBarContainer.hide();
                    isCollecting = false;
                    console.error("خطا در جمع‌آوری محتوا:", status, error);
                }
            });
        }

        // Extend localization for new messages
        $.extend(novel_ai_chatbot_admin_vars, {
            confirmClearCollection: '<?php esc_html_e( 'آیا از پاک کردن همه محتوای جمع‌آوری‌شده مطمئن هستید؟ این عملیات قابل بازگشت نیست.', 'novel-ai-chatbot' ); ?>',
            clearingCollection: '<?php esc_html_e( 'پاک کردن محتوای جمع‌آوری‌شده...', 'novel-ai-chatbot' ); ?>',
            totalCollectedItems: '<?php esc_html_e( 'تعداد کل آیتم‌های جمع‌آوری‌شده', 'novel-ai-chatbot' ); ?>',
            noCollectedContent: '<?php esc_html_e( 'هنوز محتوایی جمع‌آوری نشده است.', 'novel-ai-chatbot' ); ?>',
            errorLoadingSummary: '<?php esc_html_e( 'خطا در دریافت جمع‌آوری محتوا.', 'novel-ai-chatbot' ); ?>'
        });
    });
</script>