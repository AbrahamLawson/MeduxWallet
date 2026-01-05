import { defineStore } from 'pinia';
import { portfolioService } from '../services/portfolio';
import { cryptoService } from '../services/crypto';

export const usePortfolioStore = defineStore('portfolio', {
  state: () => ({
    assets: [],
    prices: {},
    detailedData: {},
    loading: false,
    lastUpdate: null,
  }),

  getters: {
    totalValue: (state) => {
      return state.assets.reduce((total, asset) => {
        const price = state.prices[asset.coinId]?.usd || 0;
        const value = parseFloat(asset.quantity) * price;
        return total + value;
      }, 0);
    },

    totalProfitLoss: (state) => {
      return state.assets.reduce((total, asset) => {
        const coinPrice = state.prices[asset.coinId] || {};
        const currentPrice = coinPrice.usd || 0;
        const currentValue = parseFloat(asset.quantity) * currentPrice;
        const purchaseValue = parseFloat(asset.purchasePrice || 0) * parseFloat(asset.quantity);
        const profitLoss = currentValue - purchaseValue;
        return total + profitLoss;
      }, 0);
    },

    assetsWithPrices: (state) => {
      return state.assets.map(asset => {
        const coinPrice = state.prices[asset.coinId] || {};
        const detailed = state.detailedData[asset.coinId] || {};
        const currentPrice = coinPrice.usd || 0;
        const currentValue = parseFloat(asset.quantity) * currentPrice;
        const purchaseValue = parseFloat(asset.purchasePrice || 0) * parseFloat(asset.quantity);
        const profitLoss = currentValue - purchaseValue;
        const profitLossPercent = purchaseValue > 0 ? (profitLoss / purchaseValue) * 100 : 0;

        return {
          ...asset,
          currentPrice,
          currentValue,
          purchaseValue,
          profitLoss,
          profitLossPercent,
          change24h: coinPrice.usd_24h_change || 0,
          marketCap: coinPrice.usd_market_cap || 0,
          volume24h: coinPrice.usd_24h_vol || 0,
          image: detailed.image || null,
          rank: detailed.market_cap_rank || null,
          high24h: detailed.high_24h || 0,
          low24h: detailed.low_24h || 0,
          ath: detailed.ath || 0,
          athDate: detailed.ath_date || null,
          sparkline: detailed.sparkline_in_7d?.price || [],
        };
      });
    },
  },

  actions: {
    async fetchAssets() {
      this.loading = true;
      try {
        this.assets = await portfolioService.getAssets();
        await this.fetchPrices();
        await this.fetchDetailedData();
      } catch (error) {
        console.error('Error fetching assets:', error);
      } finally {
        this.loading = false;
      }
    },

    async fetchPrices() {
      const coinIds = this.assets.map(a => a.coinId).filter(Boolean);
      if (coinIds.length > 0) {
        try {
          this.prices = await cryptoService.getPrices(coinIds);
          this.lastUpdate = new Date();
        } catch (error) {
          console.error('Error fetching prices:', error);
        }
      }
    },

    async fetchDetailedData() {
      const coinIds = this.assets.map(a => a.coinId).filter(Boolean);
      if (coinIds.length > 0) {
        try {
          const dataArray = await cryptoService.getDetailedData(coinIds);
          // Convertir le tableau en objet avec coinId comme clé
          this.detailedData = dataArray.reduce((acc, coin) => {
            acc[coin.id] = coin;
            return acc;
          }, {});
        } catch (error) {
          console.error('Error fetching detailed data:', error);
        }
      }
    },

    async addAsset(asset) {
      try {
        const newAsset = await portfolioService.addAsset(asset);
        this.assets.push(newAsset);
        await this.fetchPrices();
        return newAsset;
      } catch (error) {
        throw error;
      }
    },

    async updateAsset(id, asset) {
      try {
        const updated = await portfolioService.updateAsset(id, asset);
        const index = this.assets.findIndex(a => a.id === id);
        if (index !== -1) {
          this.assets[index] = { ...this.assets[index], ...updated };
        }
        await this.fetchPrices();
        return updated;
      } catch (error) {
        throw error;
      }
    },

    async deleteAsset(id) {
      try {
        await portfolioService.deleteAsset(id);
        this.assets = this.assets.filter(a => a.id !== id);
      } catch (error) {
        throw error;
      }
    },
  },
});
