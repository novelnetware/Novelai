import { createApp, ref, onMounted, watch } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.prod.js';
    if (document.getElementById('chat-history-app')) {
        createApp({
            setup() {
                const conversations = ref([]);
                const pagination = ref({ total_pages: 1, current_page: 1 });
                const searchTerm = ref('');
                const isLoading = ref(true);
                const selectedChat = ref(null);
                const selectedChatMessages = ref([]);
                const isTranscriptLoading = ref(false);
                let searchTimeout;

                const translateStatus = (status) => {
                const translations = {
                    bot: 'ربات',
                    pending: 'در انتظار',
                    active: 'فعال',
                    resolved: 'پایان یافته'
                };
                return translations[status] || status;
            };

                const fetchHistory = async (page = 1) => {
                    isLoading.value = true;
                    try {
                        const params = new URLSearchParams({
                            action: 'nac_get_chat_history_paginated',
                            nonce: nac_history_vars.nonce,
                            page: page,
                            search: searchTerm.value
                        });
                        const response = await fetch(nac_history_vars.ajax_url, { method: 'POST', body: params });
                        const data = await response.json();
                        if (data.success) {
                            conversations.value = data.data.conversations;
                            pagination.value = data.data.pagination;
                        }
                    } catch (error) {
                        console.error("Error fetching chat history:", error);
                    } finally {
                        isLoading.value = false;
                    }
                };
                
                const selectChat = async (convo) => {
                    selectedChat.value = convo;
                    isTranscriptLoading.value = true;
                    try {
                         const params = new URLSearchParams({
                            action: 'nac_get_session_history',
                            nonce: nac_live_chat_vars.nonce, // We can reuse the live chat nonce or create a new one
                            session_id: convo.session_id
                        });
                        const response = await fetch(nac_live_chat_vars.ajax_url, { method: 'POST', body: params });
                        const data = await response.json();
                        if (data.success) {
                            selectedChatMessages.value = data.data.history;
                        }
                    } catch (error) {
                        console.error("Error fetching transcript:", error);
                    } finally {
                        isTranscriptLoading.value = false;
                    }
                };

                const closeTranscript = () => {
                    selectedChat.value = null;
                    selectedChatMessages.value = [];
                };

                const changePage = (newPage) => {
                    if (newPage > 0 && newPage <= pagination.value.total_pages) {
                        fetchHistory(newPage);
                    }
                };
                
                const debouncedSearch = () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        fetchHistory(1); // Reset to first page on new search
                    }, 500); // Wait 500ms after user stops typing
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

                // Watch for changes in searchTerm and trigger a debounced search
            watch(searchTerm, () => {
                debouncedSearch();
            });

            onMounted(() => fetchHistory());

            return {
                conversations, pagination, searchTerm, isLoading,
                selectedChat, selectedChatMessages, isTranscriptLoading,
                fetchHistory, selectChat, closeTranscript, changePage, timeAgo,translateStatus
            };
            }
        }).mount('#chat-history-app');
    }
