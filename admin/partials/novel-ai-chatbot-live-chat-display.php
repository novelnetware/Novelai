<?php
/**
 * Provides the admin-facing view for the Live Chat dashboard.
 *
 * @link       https://novelnetware.com/
 * @since      1.2.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/admin/partials
 */
?>

<div class="wrap" id="live-chat-admin-app">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p><?php _e( 'در این صفحه می‌توانید گفتگوهای کاربران را به صورت زنده مدیریت کرده و به آن‌ها پاسخ دهید.', 'novel-ai-chatbot' ); ?></p>

    <div class="live-chat-layout">
        <div class="chat-sidebar">
            <div class="chat-list-section">
                <h3>{{ i18n.pending_chats }} <span class="chat-count">{{ pendingChats.length }}</span></h3>
                <ul class="chat-list" v-if="pendingChats.length > 0">
                    <li v-for="chat in pendingChats" :key="chat.session_id" @click="selectChat(chat)" :class="{ 'selected': currentChat && currentChat.session_id === chat.session_id }">
                        <div class="chat-list-item">
                            <span>{{ 'کاربر ' + chat.session_id.substring(0, 8) }}</span>
                            <small>{{ timeAgo(chat.last_timestamp) }}</small>
                            <button class="button button-primary button-small" @click.stop="claimChat(chat.session_id)">
                                {{ i18n.claim_chat }}
                            </button>
                        </div>
                    </li>
                </ul>
                <p v-else>{{ i18n.no_pending_chats }}</p>
            </div>

            <div class="chat-list-section">
                <h3>{{ i18n.active_chats }} <span class="chat-count">{{ activeChats.length }}</span></h3>
                <ul class="chat-list" v-if="activeChats.length > 0">
                     <li v-for="chat in activeChats" :key="chat.session_id" @click="selectChat(chat)" :class="{ 'selected': currentChat && currentChat.session_id === chat.session_id }">
                        <div class="chat-list-item">
                            <span>{{ 'کاربر ' + chat.session_id.substring(0, 8) }}</span>
                            <small>{{ timeAgo(chat.last_timestamp) }}</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="chat-main">
            <div v-if="currentChat" class="chat-window">
                <div class="chat-header">
                    <h4>گفتگو با کاربر {{ currentChat.session_id.substring(0, 8) }}</h4>
                    <button class="button button-secondary" @click="resolveChat(currentChat.session_id)">{{ i18n.resolve_chat }}</button>
                </div>
                <div class="chat-messages" ref="chatMessagesEl">
                    <div v-for="(message, index) in messages" :key="index" 
                         :class="['message', 'message-' + message.message_type]">
                         <strong>{{ message.message_type === 'agent' ? 'شما' : (message.message_type === 'user' ? 'کاربر' : 'سیستم') }}:</strong>
                         <p>{{ message.message_content }}</p>
                    </div>
                     <div class="typing-indicator" v-if="isFetchingHistory">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="chat-input-area">
                    <textarea v-model="newMessage" @keydown.enter.prevent="sendAgentMessage" placeholder="پاسخ خود را بنویسید و Enter را بزنید..."></textarea>
                    <button class="button button-primary" @click="sendAgentMessage">ارسال</button>
                </div>
            </div>
            <div v-else class="no-chat-selected">
                <p>برای مشاهده گفتگو، یک چت را از لیست انتخاب کنید.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .live-chat-layout { display: flex; gap: 20px; }
    .chat-sidebar { flex: 1; max-width: 350px; }
    .chat-main { flex: 3; border: 1px solid #ccd0d4; background: #fff; }
    .chat-list-section { margin-bottom: 20px; }
    .chat-list { border: 1px solid #ccd0d4; background: #fff; max-height: 250px; overflow-y: auto; }
    .chat-list li { padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; }
    .chat-list li:hover { background-color: #f0f0f1; }
    .chat-list li.selected { background-color: #e0e5ff; }
    .chat-list-item { display: flex; justify-content: space-between; align-items: center; }
    .chat-count { background: #d5d8dc; color: #50575e; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; }
    .no-chat-selected { text-align: center; padding-top: 50px; color: #777; }
    .chat-window { display: flex; flex-direction: column; height: 600px; }
    .chat-window .chat-header { display:flex; justify-content:space-between; align-items:center; padding: 10px; background: #f0f0f1; border-bottom: 1px solid #ccd0d4; }
    .chat-window .chat-messages { flex: 1; padding: 15px; overflow-y: auto; }
    .chat-window .message { margin-bottom: 10px; padding: 8px 12px; border-radius: 6px; }
    .chat-window .message-user { background: #e0e5ff; }
    .chat-window .message-agent { background: #d1f4d1; text-align: right; }
    .chat-window .message-system { background: #fef4c3; text-align: center; font-style: italic; color: #555; }
    .chat-window .message p { margin: 0; }
    .chat-window .chat-input-area { padding: 10px; border-top: 1px solid #ccd0d4; display: flex; gap: 10px; }
    .chat-window .chat-input-area textarea { width: 100%; height: 60px; padding: 8px; resize: vertical; }
</style>