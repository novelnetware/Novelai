// Import Vue functions from the production-ready, ESM-browser build.
import { createApp, ref, onMounted, onUnmounted, nextTick, computed } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.prod.js';

if (document.getElementById('novel-ai-chatbot-app')) {
    createApp({
        setup() {
            // --- STATE REFS ---
            const isOpen = ref(false);
            const isLoading = ref(false);
            const userInput = ref('');
            const messages = ref([]);
            const chatMessagesEl = ref(null);
            const displayMode = ref('popup');
            const chatMode = ref('bot'); // bot, pending, live, resolved
            const liveChatInterval = ref(null);
            const ratingSubmitted = ref(false);
            const hoverRating = ref(0);
            
            // --- COMPUTED ---
            const isLiveModeActive = computed(() => chatMode.value === 'live');
            const showWelcomeScreen = computed(() => messages.value.length === 0 && !isLoading.value);
            const showRatingBox = computed(() => chatMode.value === 'resolved' && !ratingSubmitted.value);
            const showAgentRequestButton = computed(() => chatMode.value === 'bot');

            // --- METHODS ---
            const scrollToBottom = () => {
                nextTick(() => {
                    if (chatMessagesEl.value) {
                        chatMessagesEl.value.scrollTop = chatMessagesEl.value.scrollHeight;
                    }
                });
            };
            
            const processHistory = (history) => {
                messages.value = history.map(msg => ({
                    text: msg.message_content,
                    type: msg.message_type,
                    isError: false
                }));

                const lastMessage = history[history.length - 1];
                if (lastMessage) {
                    const lastStatus = lastMessage.status || 'bot';
                     if (['pending', 'live', 'resolved'].includes(lastStatus)) {
                        chatMode.value = lastStatus;
                        // Restart polling if page is reloaded during a live/pending chat
                        if (lastStatus === 'pending' || lastStatus === 'live') {
                            startLiveChatPolling();
                        }
                    }
                }
                scrollToBottom();
            };

            const loadHistory = async () => {
                isLoading.value = true;
                try {
                    const params = new URLSearchParams({
                        action: 'nac_get_session_history',
                        nonce: novel_ai_chatbot_public_vars.nonce,
                        session_id: novel_ai_chatbot_public_vars.session_id
                    });
                    const response = await fetch(novel_ai_chatbot_public_vars.ajax_url, { method: 'POST', body: params });
                    const data = await response.json();
                    if (data.success && data.data.history) {
                        processHistory(data.data.history);
                    } else if (messages.value.length === 0) {
                        messages.value.push({ text: novel_ai_chatbot_public_vars.initialBotMessage, type: 'bot' });
                    }
                } catch (e) {
                    console.error("History Load Failed:", e);
                } finally {
                    isLoading.value = false;
                }
            };
            
            const toggleChat = () => {
                isOpen.value = !isOpen.value;
                if (isOpen.value && messages.value.length === 0) {
                    loadHistory();
                }
            };

            const sendMessage = async () => {
                const messageText = userInput.value.trim();
                if (!messageText) return;
                
                messages.value.push({ text: messageText, type: 'user' });
                userInput.value = '';
                scrollToBottom();
                isLoading.value = true;

                const action = (chatMode.value === 'live' || chatMode.value === 'pending') ? 'nac_send_live_user_message' : 'novel_ai_chatbot_get_response';
                const params = new URLSearchParams({
                    action,
                    nonce: novel_ai_chatbot_public_vars.nonce,
                    session_id: novel_ai_chatbot_public_vars.session_id,
                    message: messageText,
                    query: messageText // Keep both for different endpoints
                });

                try {
                    const response = await fetch(novel_ai_chatbot_public_vars.ajax_url, { method: 'POST', body: params });
                    const data = await response.json();
                    if (data.success && action === 'novel_ai_chatbot_get_response') {
                        console.error("Send Message Failed:", e);
                    messages.value.push({
                        text: novel_ai_chatbot_public_vars.i18n.request_failed || 'ارسال پیام با خطا مواجه شد.',
                        type: 'bot',
                        isError: true
                    });
                    }
                } catch (e) {
                    messages.value.push({ text: novel_ai_chatbot_public_vars.i18n.request_failed, type: 'bot', isError: true });
                } finally {
                    isLoading.value = false;
                    scrollToBottom();
                }
            };

            const requestLiveAgent = async () => {
                chatMode.value = 'pending';
                messages.value.push({ text: novel_ai_chatbot_public_vars.i18n.wait_for_operator, type: 'system' });
                startLiveChatPolling();
                const params = new URLSearchParams({
                    action: 'nac_request_live_agent',
                    nonce: novel_ai_chatbot_public_vars.nonce,
                    session_id: novel_ai_chatbot_public_vars.session_id
                });
                await fetch(novel_ai_chatbot_public_vars.ajax_url, { method: 'POST', body: params });
            };

            const startLiveChatPolling = () => {
                if (liveChatInterval.value) clearInterval(liveChatInterval.value);
                liveChatInterval.value = setInterval(async () => {
                    if (!isOpen.value) return; // Don't poll if chat is closed
                    const params = new URLSearchParams({ action: 'nac_get_session_history', nonce: novel_ai_chatbot_public_vars.nonce, session_id: novel_ai_chatbot_public_vars.session_id });
                    const response = await fetch(novel_ai_chatbot_public_vars.ajax_url, { method: 'POST', body: params });
                    const data = await response.json();
                    if (data.success && data.data.history.length > messages.value.length) {
                        processHistory(data.data.history);
                    }
                    const lastStatus = data.data.history[data.data.history.length - 1]?.status;
                    if (lastStatus === 'resolved') {
                        stopLiveChatPolling();
                    }
                }, 5000); // Poll every 5 seconds
            };

            const stopLiveChatPolling = () => {
                if (liveChatInterval.value) {
                    clearInterval(liveChatInterval.value);
                    liveChatInterval.value = null;
                }
            };

            const toggleFullscreen = () => { displayMode.value = displayMode.value === 'popup' ? 'fullscreen' : 'popup'; };
            const setHoverRating = (rating) => { hoverRating.value = rating; };
            const resetHoverRating = () => { hoverRating.value = 0; };
            const submitRating = async (rating) => {
                ratingSubmitted.value = true;
                messages.value.push({ text: novel_ai_chatbot_public_vars.i18n.thank_you_feedback, type: 'system' });
                const params = new URLSearchParams({ action: 'nac_submit_rating', nonce: novel_ai_chatbot_public_vars.nonce, session_id: novel_ai_chatbot_public_vars.session_id, rating });
                await fetch(novel_ai_chatbot_public_vars.ajax_url, { method: 'POST', body: params });
            };
            
            onMounted(() => {
                if(isOpen.value) loadHistory();
            });
            onUnmounted(stopLiveChatPolling);

            return {
                isOpen, isLoading, userInput, messages, chatMessagesEl, displayMode, chatMode,
                ratingSubmitted, hoverRating, showWelcomeScreen, showRatingBox, showAgentRequestButton,
                toggleChat, sendMessage, requestLiveAgent, toggleFullscreen, setHoverRating, resetHoverRating, submitRating
            };
        }
    }).mount('#novel-ai-chatbot-app');
}