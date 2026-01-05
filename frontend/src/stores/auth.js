import { defineStore } from 'pinia';
import { authService } from '../services/auth';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: authService.isAuthenticated(),
  }),

  actions: {
    async login(email, password) {
      try {
        const data = await authService.login(email, password);
        this.isAuthenticated = true;
        await this.fetchUser();
        return data;
      } catch (error) {
        throw error;
      }
    },

    async register(email, password, name) {
      try {
        const data = await authService.register(email, password, name);
        return data;
      } catch (error) {
        throw error;
      }
    },

    async fetchUser() {
      try {
        this.user = await authService.getMe();
      } catch (error) {
        this.logout();
        throw error;
      }
    },

    logout() {
      authService.logout();
      this.user = null;
      this.isAuthenticated = false;
    },
  },
});
