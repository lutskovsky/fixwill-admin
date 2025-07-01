<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="close"></div>

        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full max-h-[80vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        WhatsApp чат с {{ phone }}
                    </h3>
                    <button class="text-gray-400 hover:text-gray-600" @click="close">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"></path>
                        </svg>
                    </button>
                </div>

                <!-- Chat History -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div v-if="loading" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    </div>

                    <div v-else-if="messages.length === 0" class="text-center text-gray-500 py-8">
                        Нет сообщений
                    </div>

                    <div v-for="message in messages" v-else :key="message.id"
                         :class="['flex', message.source === 'operator' ? 'justify-end' : 'justify-start' ]">
                        <div :class="[
                            'max-w-[70%] rounded-lg p-3',
                            message.source === 'operator'
                                ? 'bg-green-500 text-white'
                                : 'bg-gray-100 text-gray-800'
                        ]">
                            <div class="font-semibold text-sm mb-1">
                                {{ message.source === 'operator' ? 'Оператор' : 'Клиент' }}
                                <span class="font-normal text-xs opacity-75 ml-2">
                                    {{ formatDate(message.sent_at) }}
                                </span>
                            </div>
                            <div class="whitespace-pre-wrap">{{ message.text }}</div>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="border-t p-4">
                    <form class="flex gap-2" @submit.prevent="sendMessage">
                        <input
                            v-model="newMessage"
                            :disabled="sending"
                            class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Введите сообщение..."
                            type="text"
                        />
                        <button
                            :disabled="!newMessage.trim() || sending"
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            type="submit"
                        >
                            <span v-if="sending">Отправка...</span>
                            <span v-else>Отправить</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {nextTick, onUnmounted, ref, watch} from 'vue';
import axios from 'axios';

export default {
    props: {
        isOpen: {
            type: Boolean,
            required: true
        },
        phone: {
            type: String,
            required: true
        },
        encryptedPhone: {
            type: String,
            required: true
        }
    },

    emits: ['close'],

    setup(props, {emit}) {
        const messages = ref([]);
        const loading = ref(false);
        const sending = ref(false);
        const newMessage = ref('');
        const chatContainer = ref(null);
        const currentChatId = ref(null);
        const pollInterval = ref(null);

        const formatDate = (date) => {
            if (!date) return '';
            const d = new Date(date);
            const day = d.getDate().toString().padStart(2, '0');
            const month = (d.getMonth() + 1).toString().padStart(2, '0');
            const year = d.getFullYear().toString().substr(2);
            const hours = d.getHours().toString().padStart(2, '0');
            const minutes = d.getMinutes().toString().padStart(2, '0');
            return `${day}.${month}.${year} ${hours}:${minutes}`;
        };

        const scrollToBottom = () => {
            nextTick(() => {
                if (chatContainer.value) {
                    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
                }
            });
        };

        const loadMessages = async () => {
            if (!props.isOpen) return;

            loading.value = true;
            try {
                const response = await axios.get(route('messages.history'), {
                    params: {
                        phone: props.phone,
                        encryptedPhone: props.encryptedPhone,
                        type: 'whatsapp'
                    }
                });
                messages.value = response.data.data || [];

                // Store chat ID if available
                if (messages.value.length > 0) {
                    currentChatId.value = messages.value[0].chat_id;
                }

                scrollToBottom();
            } catch (error) {
                console.error('Error loading messages:', error);
                alert('Ошибка загрузки сообщений');
            } finally {
                loading.value = false;
            }
        };

        const pollForNewMessages = async () => {
            if (!props.isOpen || loading.value || sending.value) return;

            try {
                const response = await axios.get(route('messages.history'), {
                    params: {
                        phone: props.phone,
                        encryptedPhone: props.encryptedPhone,
                        type: 'whatsapp'
                    }
                });

                const newMessages = response.data.data || [];

                // Check if there are new messages
                if (newMessages.length > messages.value.length) {
                    messages.value = newMessages;

                    // Scroll to bottom if new message is from visitor
                    const lastMessage = newMessages[newMessages.length - 1];
                    if (lastMessage.source === 'visitor') {
                        scrollToBottom();

                        // Optional: Play notification sound
                        // playNotificationSound();
                    }
                }
            } catch (error) {
                console.error('Error polling for messages:', error);
            }
        };

        const startPolling = () => {
            // Poll every 3 seconds
            pollInterval.value = setInterval(pollForNewMessages, 3000);
        };

        const stopPolling = () => {
            if (pollInterval.value) {
                clearInterval(pollInterval.value);
                pollInterval.value = null;
            }
        };

        const sendMessage = async () => {
            if (!newMessage.value.trim() || sending.value) return;

            sending.value = true;
            try {
                const response = await axios.post(route('messages.send'), {
                    phone: props.phone,
                    encryptedPhone: props.encryptedPhone,
                    text: newMessage.value,
                    type: 'whatsapp'
                });

                if (response.data.success) {
                    // Add the new message to the list
                    messages.value.push(response.data.data);
                    newMessage.value = '';
                    scrollToBottom();
                } else {
                    alert('Ошибка отправки сообщения: ' + response.data.error );
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Ошибка отправки сообщения');
            } finally {
                sending.value = false;
            }
        };

        const close = () => {
            stopPolling();
            emit('close');
        };

        // Load messages when modal opens
        watch(() => props.isOpen, (newVal) => {
            if (newVal) {
                loadMessages();
                startPolling();
            } else {
                stopPolling();
            }
        });

        // Cleanup on unmount
        onUnmounted(() => {
            stopPolling();
        });

        return {
            messages,
            loading,
            sending,
            newMessage,
            chatContainer,
            formatDate,
            sendMessage,
            close
        };
    }
};
</script>
