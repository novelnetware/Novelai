<?php
/**
 * Provides the admin-facing view for the Chat History dashboard.
 *
 * @link       https://novelnetware.com/
 * @since      1.3.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/admin/partials
 */
?>

<div class="wrap" id="chat-history-app">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p><?php _e( 'تمام گفتگوهای ذخیره شده را مرور و جستجو کنید.', 'novel-ai-chatbot' ); ?></p>

    <div class="history-filters">
        <input type="search" v-model="searchTerm" @input="debouncedSearch" placeholder="جستجو بر اساس شناسه جلسه...">
    </div>

    <div v-if="isLoading" class="loading-indicator">
        <p>در حال بارگذاری گفتگوها...</p>
    </div>

    <div v-else>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>شناسه جلسه</th>
                    <th>آخرین پیام</th>
                    <th>وضعیت</th>
                    <th>امتیاز کاربر</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody v-if="conversations.length > 0">
                <tr v-for="convo in conversations" :key="convo.session_id">
                    <td><code>{{ convo.session_id.substring(0, 12) }}...</code></td>
                    <td>{{ convo.last_message.substring(0, 50) }}...</td>
                    <td><span :class="['status-badge', 'status-' + convo.status]">{{ translateStatus(convo.status) }}</span></td>
                    <td>{{ convo.user_rating > 0 ? '⭐'.repeat(convo.user_rating) : 'بدون امتیاز' }}</td>
                    <td>{{ timeAgo(convo.last_timestamp) }}</td>
                    <td>
                        <button class="button button-secondary button-small" @click="selectChat(convo)">مشاهده</button>
                    </td>
                </tr>
            </tbody>
            <tbody v-else>
                <tr>
                    <td colspan="6">هیچ گفتگویی یافت نشد.</td>
                </tr>
            </tbody>
        </table>

        <div class="pagination" v-if="pagination.total_pages > 1">
            <button class="button" @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1">
                &laquo; قبلی
            </button>
            <span>صفحه {{ pagination.current_page }} از {{ pagination.total_pages }}</span>
            <button class="button" @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.total_pages">
                بعدی &raquo;
            </button>
        </div>
    </div>

    <div v-if="selectedChat" class="transcript-modal-overlay" @click.self="closeTranscript">
        <div class="transcript-modal">
            <div class="transcript-header">
                <h3>جزئیات گفتگو: <code>{{ selectedChat.session_id }}</code></h3>
                <button class="close-btn" @click="closeTranscript">&times;</button>
            </div>
            <div class="transcript-body">
                <div v-if="isTranscriptLoading" class="loading-indicator"><p>در حال بارگذاری متن گفتگو...</p></div>
                <div v-else v-for="message in selectedChatMessages" :class="['message', 'message-' + message.message_type]">
                    <strong>{{ message.message_type === 'agent' ? 'اپراتور' : (message.message_type === 'user' ? 'کاربر' : 'سیستم') }}:</strong>
                    <p>{{ message.message_content }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .history-filters { margin-bottom: 20px; }
    .pagination { margin-top: 20px; text-align: center; }
    .pagination span { margin: 0 10px; }
    .status-badge { padding: 3px 8px; border-radius: 4px; color: #fff; font-size: 0.9em; }
    .status-bot { background-color: #6c757d; }
    .status-pending { background-color: #ffc107; color:#000; }
    .status-active { background-color: #28a745; }
    .status-resolved { background-color: #17a2b8; }
    .transcript-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 10000; display: flex; align-items: center; justify-content: center; }
    .transcript-modal { background: #fff; width: 90%; max-width: 700px; max-height: 80vh; display: flex; flex-direction: column; border-radius: 5px; }
    .transcript-header { padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
    .transcript-header .close-btn { font-size: 24px; cursor: pointer; background: none; border: none; }
    .transcript-body { padding: 15px; overflow-y: auto; }
</style>