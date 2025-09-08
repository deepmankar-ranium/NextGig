
import { defineStore } from 'pinia'

export const useMainStore = defineStore('main', {
  state: () => ({
    message: 'Hello from Pinia!',
  }),
  getters: {
    getMessage: (state) => state.message,
  },
  actions: {
    setMessage(message) {
      this.message = message
    },
  },
})
