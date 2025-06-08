// Import Vue functions from the production-ready, ESM-browser build for isolation.
import { createApp, ref, onMounted, onUnmounted, nextTick } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.prod.js';

if (document.getElementById('live-chat-admin-app')) {
    createApp({
        setup() {
            const pendingChats = ref([]);
            const activeChats = ref([]);
            const currentChat = ref(null);
            const messages = ref([]);
            const newMessage = ref('');
            const isLoading = ref(true);
            const isFetchingHistory = ref(false);
            const chatMessagesEl = ref(null);
            const i18n = ref(nac_live_chat_vars.i18n || {});
            let pollingInterval = null;

            const scrollToBottom = () => {
                nextTick(() => {
                    if (chatMessagesEl.value) {
                        chatMessagesEl.value.scrollTop = chatMessagesEl.value.scrollHeight;
                    }
                });
            };

            const fetchChats = async () => {
                if (!isLoading.value) isLoading.value = true;
                try {
                    const params = new URLSearchParams({
                        action: 'nac_get_live_chats',
                        nonce: nac_live_chat_vars.nonce
                    });
                    const response = await fetch(nac_live_chat_vars.ajax_url, { method: 'POST', body: params });
                    const data = await response.json();
                    if (data.success) {
                        pendingChats.value = data.data.pending;
                        // Filter active chats for the current agent
                        activeChats.value = data.data.active.filter(c => c.agent_id == nac_live_chat_vars.agent_id);
                    }
                } catch (error) {
                    console.error("Error fetching chats:", error);
                } finally {
                    isLoading.value = false;
                }
            };
            
            const selectChat = async (chat) => {
                currentChat.value = chat;
                isFetchingHistory.value = true;
                messages.value = [];
                try {
                    const params = new URLSearchParams({
                        action: 'nac_get_session_history',
                        nonce: nac_live_chat_vars.nonce,
                        session_id: chat.session_id
                    });
                    const response = await fetch(nac_live_chat_vars.ajax_url, { method: 'POST', body: params });
                    const data = await response.json();
                    if (data.success) {
                        messages.value = data.data.history;
                        scrollToBottom();
                    }
                } catch (error) {
                    console.error("Error fetching history:", error);
                } finally {
                    isFetchingHistory.value = false;
                }
            };

            const claimChat = async (sessionId) => {
                try {
                    const params = new URLSearchParams({
                        action: 'nac_claim_chat',
                        nonce: nac_live_chat_vars.nonce,
                        session_id: sessionId
                    });
                    const response = await fetch(nac_live_chat_vars.ajax_url, { method: 'POST', body: params });
                    const data = await response.json();
                    if (data.success) {
                        await fetchChats(); // Refresh lists
                        // Automatically select the claimed chat
                        const claimedChat = activeChats.value.find(c => c.session_id === sessionId);
                        if(claimedChat) selectChat(claimedChat);
                    } else {
                        alert(data.data.message || 'خطا در پذیرش چت');
                    }
                } catch (error) {
                    console.error("Error claiming chat:", error);
                }
            };
            
            const sendAgentMessage = async () => {
                if (!newMessage.value.trim() || !currentChat.value) return;
                const messageText = newMessage.value;
                newMessage.value = '';

                messages.value.push({ message_content: messageText, message_type: 'agent' });
                scrollToBottom();
                
                try {
                    const params = new URLSearchParams({
                        action: 'nac_send_agent_message',
                        nonce: nac_live_chat_vars.nonce,
                        session_id: currentChat.value.session_id,
                        message: messageText
                    });
                    await fetch(nac_live_chat_vars.ajax_url, { method: 'POST', body: params });
                } catch (error) {
                    console.error("Error sending message:", error);
                }
            };

            const resolveChat = async (sessionId) => {
                if (!confirm('آیا از پایان دادن به این گفتگو مطمئن هستید؟')) return;
                try {
                    const params = new URLSearchParams({ action: 'nac_resolve_chat', nonce: nac_live_chat_vars.nonce, session_id: sessionId });
                    await fetch(nac_live_chat_vars.ajax_url, { method: 'POST', body: params });
                    currentChat.value = null;
                    messages.value = [];
                    await fetchChats();
                } catch (error) {
                    console.error("Error resolving chat:", error);
                }
            };

              const timeAgo = (dateString) => {
                if (!dateString) {
                    return 'نامشخص';
                }

                // Create a reliable Date object from MySQL's format 'YYYY-MM-DD HH:MM:SS'
                // By appending 'Z', we tell the browser the time is in UTC.
                const date = new Date(dateString.replace(' ', 'T') + 'Z');
                const now = new Date();
                
                // Get the difference in seconds, considering the user's local timezone.
                const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

                if (isNaN(seconds) || seconds < 0) {
                    return "همین الان"; // Handle edge cases or future dates gracefully
                }
                if (seconds < 30) {
                    return "همین الان";
                }
                
                const intervals = {
                    'سال': 31536000,
                    'ماه': 2592000,
                    'روز': 86400,
                    'ساعت': 3600,
                    'دقیقه': 60,
                    'ثانیه': 1
                };

                let counter;
                for (const key in intervals) {
                    counter = Math.floor(seconds / intervals[key]);
                    if (counter > 0) {
                        if (key === 'روز' && counter === 1) {
                            return 'دیروز';
                        }
                        return `${counter} ${key} پیش`;
                    }
                }
                
                return "همین الان";
            };

            onMounted(() => {
                fetchChats();
                pollingInterval = setInterval(fetchChats, 10000);
            });
            onUnmounted(() => {
                clearInterval(pollingInterval);
            });

            return {
                pendingChats, activeChats, currentChat, messages, newMessage, isLoading,
                isFetchingHistory, chatMessagesEl, i18n, selectChat, claimChat,
                sendAgentMessage, resolveChat, timeAgo
            };
        }
    }).mount('#live-chat-admin-app');
}