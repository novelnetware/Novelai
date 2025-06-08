<?php
/**
 * Provides the public-facing view for the plugin, now powered by Vue.js.
 *
 * @link       https://novelnetware.com/
 * @since      1.2.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/public/partials
 */

// We still get these options to pass them as initial state or props if needed,
// but most of the text is now handled in the JS for dynamic display.
$chat_customization_options = get_option( 'novel_ai_chatbot_chat_customization_options', array() );
$widget_icon_svg = isset( $chat_customization_options['widget_icon_svg'] ) ? esc_url( $chat_customization_options['widget_icon_svg'] ) : '';
$header_logo_svg = isset( $chat_customization_options['header_logo_svg'] ) ? esc_url( $chat_customization_options['header_logo_svg'] ) : '';
$welcome_svg = isset( $chat_customization_options['welcome_svg'] ) ? esc_url( $chat_customization_options['welcome_svg'] ) : '';
$header_text = isset( $chat_customization_options['header_text'] ) ? esc_html( $chat_customization_options['header_text'] ) : esc_html__( 'Smart Chatbot', 'novel-ai-chatbot' );
$assistant_title = isset( $chat_customization_options['assistant_title'] ) ? esc_html( $chat_customization_options['assistant_title'] ) : esc_html__( 'I am your chatbot assistant', 'novel-ai-chatbot' );
?>
<div id="novel-ai-chatbot-app" style="direction: rtl; font-family: 'Vazirmatn', Tahoma, Arial, sans-serif;">

    <div class="chat-widget" 
         v-if="!isOpen" 
         @click="toggleChat">
        <?php if ($widget_icon_svg): ?>
            <img src="<?php echo $widget_icon_svg; ?>" alt="آیکون ویجت" width="24" height="24" />
        <?php else: ?>
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="white" width="24" height="24"><path d="M12 3a2 2 0 0 0-1 3.732V8H8c-3.2 0-4 2.667-4 4v7c0 .667.4 2 2 2h1v-4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v4h1c1.6 0 2-1.333 2-2v-7c0-3.2-2.667-4-4-4h-3V6.732A2 2 0 0 0 12 3zm3 18v-3h-2v3h2zm-4 0v-3H9v3h2zm10-3v-5c.667 0 2 .4 2 2v1c0 .667-.4 2-2 2zM3 13v5c-1.6 0-2-1.333-2-2v-1c0-1.6 1.333-2 2-2zm6-1a1 1 0 1 0 0 2h.001a1 1 0 1 0 0-2H9zm5 1a1 1 0 0 1 1-1h.001a1 1 0 1 1 0 2H15a1 1 0 0 1-1-1z"></path></svg>
        <?php endif; ?>
    </div>

    <div class="chat-container" :class="['is-' + displayMode, { 'open': isOpen }]">
     <div class="chat-header">
    <div class="header-title">
        <div class="logo">
            <?php if ($header_logo_svg): ?>
                <img src="<?php echo $header_logo_svg; ?>" alt="لوگوی هدر" />
            <?php else: ?>
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#005FFF"><path d="M12 3a2 2 0 0 0-1 3.732V8H8c-3.2 0-4 2.667-4 4v7c0 .667.4 2 2 2h1v-4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v4h1c1.6 0 2-1.333 2-2v-7c0-3.2-2.667-4-4-4h-3V6.732A2 2 0 0 0 12 3zm3 18v-3h-2v3h2zm-4 0v-3H9v3h2zm10-3v-5c.667 0 2 .4 2 2v1c0 .667-.4 2-2 2zM3 13v5c-1.6 0-2-1.333-2-2v-1c0-1.6 1.333-2 2-2zm6-1a1 1 0 1 0 0 2h.001a1 1 0 1 0 0-2H9zm5 1a1 1 0 0 1 1-1h.001a1 1 0 1 1 0 2H15a1 1 0 0 1-1-1z"></path></svg>
            <?php endif; ?>
        </div>
        <span><?php echo $header_text; ?></span>
    </div>
    
    <div class="header-controls">
        <button class="control-btn" @click="toggleFullscreen" :title="displayMode === 'popup' ? 'تمام صفحه' : 'خروج از تمام صفحه'">
            <svg v-if="displayMode === 'popup'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/></svg>
        </button>
        <button class="control-btn close-btn" @click="toggleChat" aria-label="بستن چت‌بات">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
</div>

        <div class="chat-messages" ref="chatMessagesEl">
            <div class="welcome-screen" v-if="messages.length === 0 && !isLoading">
                <?php if ($welcome_svg): ?>
                    <img src="<?php echo $welcome_svg; ?>" alt="تصویر خوش‌آمد" style="width: 120px; height: 120px;" />
                <?php else: ?>
                    <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;"></svg>
                <?php endif; ?>
                <h3><?php echo $assistant_title; ?></h3>
            </div>
            <div class="message system" v-if="showAgentRequestButton">
                <button class="button-link" @click="requestLiveAgent">
                    صحبت با اپراتور پشتیبانی
                </button>
            </div>

            <div v-for="(message, index) in messages" :key="index" 
                 :class="['message', message.type === 'user' ? 'user-message' : 'bot-message', { 'error-message': message.isError }]">
                {{ message.text }}
            </div>

            <div class="typing-indicator" v-if="isLoading">
                <span></span><span></span><span></span>
            </div>
            <div class="rating-box" v-if="chatMode === 'resolved' && !ratingSubmitted">
                <p>لطفاً به این گفتگو امتیاز دهید:</p>
                <div class="stars" @mouseleave="resetHoverRating">
                    <span v-for="star in 5" :key="star" 
                          @mouseover="setHoverRating(star)" 
                          @click="submitRating(star)"
                          :class="{ 'filled': star <= (hoverRating || 0) }">
                        <svg height="24px" width="24px" viewBox="0 0 32 32"><path d="M28.36,12.24,20.6,11.45l-3.5-7.4a1.21,1.21,0,0,0-2.2,0l-3.5,7.4-7.76.79a1.22,1.22,0,0,0-.68,2.09l5.7,5.15-1.48,7.6a1.21,1.21,0,0,0,1.76,1.27L16,24.5l6.88,3.85a1.21,1.21,0,0,0,1.76-1.27l-1.48-7.6,5.7-5.15A1.22,1.22,0,0,0,28.36,12.24Z"/></svg>
                    </span>
                </div>
            </div>
        </div>

        <div class="input-area">
            <input type="text" 
                   v-model="userInput" 
                   @keydown.enter.prevent="sendMessage" 
                   placeholder="پیام خود را بنویسید...">
                   <button class="agent-request-btn" v-if="chatMode === 'bot'" @click="requestLiveAgent" title="صحبت با اپراتور">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            </button>
            <button class="send-button" @click="sendMessage" aria-label="ارسال">
                <svg viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" transform="rotate(270)" width="20" height="20"><path d="M2.33045 8.38999C0.250452 11.82 9.42048 14.9 9.42048 14.9C9.42048 14.9 12.5005 24.07 15.9305 21.99C19.5705 19.77 23.9305 6.13 21.0505 3.27C18.1705 0.409998 4.55045 4.74999 2.33045 8.38999Z" stroke="#ffffff" stroke-width="1.5"></path><path d="M15.1999 9.12L9.41992 14.9" stroke="#ffffff" stroke-width="1.5"></path></svg>
            </button>
        </div>
    </div>
</div>