import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    hasNewMessages: false,
    _inited: false,
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
            console.log('New chat message event received:', event)
          this.setHasNewMessages(true)

          // 🎉 Show toast popup
          toast.info(`${event.user.full_name}: ${event.message.message}`, {
            timeout: 4000,
            position: 'top-right',
          })
        })
        .error((err) => console.error('Echo error:', err))

      this._inited = true
    },
  },
})
