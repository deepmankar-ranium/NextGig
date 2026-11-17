import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'
import ChatMessageNotification from '../Components/ChatMessageNotification.vue'
import axios from 'axios';
import { STAGE_LABELS } from '../utils/stages';

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        hasNewMessages: false,
        _inited: false,
        unreadCount: 0,
        unreadMessages: {},
        applicationUpdates: 0,
        lastApplicationUpdate: null,
    }),
    actions: {
        setHasNewMessages(status) {
            this.hasNewMessages = status
        },
        clearNewMessages() {
            this.hasNewMessages = false
        },

        initializeWebSocket(userId) {
            if (this._inited) return
            if (!userId || !window.Echo) return

            const toast = useToast()

            window.Echo.private(`user.${userId}`)
                .listen('ChatMessageSent', (event) => {
                    this.setHasNewMessages(true)
                    this.fetchUnreadCount();

                    // 🎉 Show toast popup
                    toast({
                        component: ChatMessageNotification,
                        props: {
                            user: event.user.full_name,
                            message: event.message.message,
                        }
                    }, {
                        timeout: 4000,
                        position: 'top-right',
                        closeButton: false,
                        hideProgressBar: true,
                        icon: false,
                        toastClassName: '!p-0 !bg-transparent !shadow-none',
                        bodyClassName: 'flex items-start space-x-3 bg-green-100 border border-green-300 px-4 py-3 rounded-lg shadow-md',
                    })
                })
                .listen('.application.status', (event) => {
                    this.applicationUpdates += 1
                    this.lastApplicationUpdate = event
                    toast.info(`${event.job_title}: ${STAGE_LABELS[event.status] ?? event.status}`, {
                        timeout: 5000,
                        position: 'top-right',
                    })
                })
                .error((err) => console.error('Echo error:', err))

            this._inited = true
        },

        clearApplicationUpdates() {
            this.applicationUpdates = 0
        },

        async fetchUnreadCount() {
            try {
                const response = await axios.get('/api/unread-messages-count');
                this.unreadCount = response.data.unread_count;
                this.unreadMessages = response.data.unread_messages;
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        },

        async markMessageAsRead(messageId, refetch = true) {
            try {
                await axios.post(`/api/direct-messages/${messageId}/read`);
                if (refetch) {
                    this.fetchUnreadCount();
                }
            } catch (error) {
                console.error('Error marking message as read:', error);
            }
        },

        async markUserMessagesAsRead(userId) {
            const messagesToMark = this.unreadMessages[userId];
            if (!messagesToMark || messagesToMark.length === 0) return;

            const promises = messagesToMark.map(message => this.markMessageAsRead(message.id, false));
            await Promise.all(promises);

            this.fetchUnreadCount();
        },


    },
})
