import api from './api';

export const portfolioService = {
  async getAssets() {
    const response = await api.get('/api/portfolio');
    return response.data;
  },

  async addAsset(asset) {
    const response = await api.post('/api/portfolio', asset);
    return response.data;
  },

  async updateAsset(id, asset) {
    const response = await api.put(`/api/portfolio/${id}`, asset);
    return response.data;
  },

  async deleteAsset(id) {
    const response = await api.delete(`/api/portfolio/${id}`);
    return response.data;
  },
};
