import api from './api';

export const authService = {
  async register(email, password, name) {
    const response = await api.post('/api/register', { email, password, name });
    return response.data;
  },

  async login(email, password) {
    const response = await api.post('/api/login', { email, password });
    if (response.data.token) {
      localStorage.setItem('token', response.data.token);
    }
    return response.data;
  },

  async getMe() {
    const response = await api.get('/api/me');
    return response.data;
  },

  logout() {
    localStorage.removeItem('token');
  },

  isAuthenticated() {
    return !!localStorage.getItem('token');
  },
};
