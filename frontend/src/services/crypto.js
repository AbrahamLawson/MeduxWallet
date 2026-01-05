import api from './api';

export const cryptoService = {
  async getPrices(coinIds) {
    const ids = Array.isArray(coinIds) ? coinIds.join(',') : coinIds;
    const response = await api.get(`/api/crypto/prices?ids=${ids}`);
    return response.data;
  },

  async getDetailedData(coinIds) {
    const ids = Array.isArray(coinIds) ? coinIds.join(',') : coinIds;
    const response = await api.get(`/api/crypto/detailed?ids=${ids}`);
    return response.data;
  },

  async getCoinInfo(coinId) {
    const response = await api.get(`/api/crypto/info/${coinId}`);
    return response.data;
  },

  async searchCoins(query) {
    const response = await api.get(`/api/crypto/search?q=${query}`);
    return response.data;
  },

  async getTopCoins(limit = 20) {
    const response = await api.get(`/api/crypto/top?limit=${limit}`);
    return response.data;
  },
};
