import api from './api';

export const blockchainService = {
  async getLatestBlocks(limit = 10) {
    const response = await api.get(`/api/blockchain/blocks?limit=${limit}`);
    return response.data;
  },

  async getBlock(hash) {
    const response = await api.get(`/api/blockchain/block/${hash}`);
    return response.data;
  },

  async getUnconfirmedTransactions() {
    const response = await api.get('/api/blockchain/transactions/unconfirmed');
    return response.data;
  },

  async getStats() {
    const response = await api.get('/api/blockchain/stats');
    return response.data;
  },

  async getLatestBlock() {
    const response = await api.get('/api/blockchain/latest');
    return response.data;
  },

  async getAddress(address) {
    const response = await api.get(`/api/blockchain/address/${address}`);
    return response.data;
  },

  async getTransaction(hash) {
    const response = await api.get(`/api/blockchain/tx/${hash}`);
    return response.data;
  },
};
