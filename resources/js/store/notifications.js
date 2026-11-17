import { defineStore } from 'pinia'
import { notify } from '../utils/toast'
import axios from 'axios';
import { STAGE_LABELS } from '../utils/stages';

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        hasNewMessages: false,
        _userId: null,
        unreadCount: 0,
        unreadMessages: {},
        applicationUpdates: 0,
        lastApplicationUpdate: null,
        lastIncomingMessage: null,
    }),
    actions: {
        setHasNewMessages(status) {
            this.hasNewMessages = status
        },
        clearNewMessages() {
            this.hasNewMessages = false
        },

        /**
         * Listen on the signed-in user's private channel. Called whenever the user changes;
         * switching users leaves the previous channel first.
         */
        connect(userId) {
            if (!userId || !window.Echo || this._userId === userId) return
            this.disconnect()
            this._userId = userId

            window.Echo.private(`user.${userId}`)
                .listen('ChatMessageSent', (event) => {
                    this.lastIncomingMessage = event
                    // Already looking at this conversation: no toast, the thread updates itself.
                    if (window.location.pathname === `/messages/${event.message.sender_id}`) return

                    this.setHasNewMessages(true)
                    this.fetchUnreadCount()

                    notify({
                        variant: 'message',
                        title: event.user.full_name,
                        body: event.message.message,
                        href: `/messages/${event.message.sender_id}`,
                        avatar: { id: event.user.id, name: event.user.full_name, src: event.user.photo_url ?? null },
                        id: `dm-${event.message.id}`,
                        timeout: 6000,
                    })
                })
                .listen('.application.status', (event) => {
                    this.applicationUpdates += 1
                    this.lastApplicationUpdate = event
                    notify({
                        variant: event.status === 'rejected' ? 'info' : 'success',
                        title: event.job_title,
                        body: `Your application moved to ${STAGE_LABELS[event.status] ?? event.status}.`,
                        href: '/applied-jobs',
                        id: `app-${event.application_id ?? ''}-${event.status}`,
                    })
                })
                .error((err) => console.error('Echo error:', err))
        },

        disconnect() {
            if (this._userId && window.Echo) window.Echo.leave(`user.${this._userId}`)
            this._userId = null
            this.unreadCount = 0
            this.unreadMessages = {}
            this.applicationUpdates = 0
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
