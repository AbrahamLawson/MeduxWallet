<template>
  <div class="dashboard">
    <nav class="navbar">
      <div class="nav-content">
        <h2>💎 Medux Wallet</h2>
        <div class="nav-actions">
          <router-link to="/dashboard" class="nav-link active">Portfolio</router-link>
          <router-link to="/blockchain" class="nav-link">Blockchain</router-link>
          <span class="user-info">{{ authStore.user?.email }}</span>
          <button @click="handleLogout" class="btn-logout">Déconnexion</button>
        </div>
      </div>
    </nav>

    <div class="container">
      <div class="header-section">
        <div class="portfolio-summary">
          <h1>Mon Portefeuille</h1>
          <div class="total-value">
            <span class="label">Valeur totale</span>
            <span class="amount">${{ totalValue.toFixed(2) }}</span>
          </div>
        </div>
        <button @click="showAddModal = true" class="btn-add">+ Ajouter un actif</button>
      </div>

      <div v-if="portfolioStore.loading" class="loading">Chargement...</div>

      <div v-else-if="assets.length === 0" class="empty-state">
        <p>Aucun actif dans votre portefeuille</p>
        <button @click="showAddModal = true" class="btn-primary">Ajouter votre premier actif</button>
      </div>

      <div v-else class="assets-grid">
        <div v-for="asset in assets" :key="asset.id" class="asset-card">
          <div class="asset-header">
            <div>
              <h3>{{ asset.symbol }}</h3>
              <p class="asset-name">{{ asset.name }}</p>
            </div>
            <button @click="deleteAsset(asset.id)" class="btn-delete">×</button>
          </div>

          <div class="asset-details">
            <div class="detail-row">
              <span>Quantité</span>
              <span class="value">{{ parseFloat(asset.quantity).toFixed(8) }}</span>
            </div>
            <div class="detail-row">
              <span>Prix actuel</span>
              <span class="value">${{ asset.currentPrice.toFixed(2) }}</span>
            </div>
            <div class="detail-row">
              <span>Valeur</span>
              <span class="value">${{ asset.currentValue.toFixed(2) }}</span>
            </div>
            <div v-if="asset.purchasePrice" class="detail-row">
              <span>P&L</span>
              <span :class="['value', asset.profitLoss >= 0 ? 'positive' : 'negative']">
                {{ asset.profitLoss >= 0 ? '+' : '' }}${{ asset.profitLoss.toFixed(2) }}
                ({{ asset.profitLossPercent.toFixed(2) }}%)
              </span>
            </div>
            <div class="detail-row">
              <span>24h</span>
              <span :class="['value', asset.change24h >= 0 ? 'positive' : 'negative']">
                {{ asset.change24h >= 0 ? '+' : '' }}{{ asset.change24h.toFixed(2) }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAddModal" class="modal" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Ajouter un actif</h2>
          <button @click="closeModal" class="btn-close">×</button>
        </div>

        <form @submit.prevent="handleAddAsset">
          <div class="form-group">
            <label>Rechercher une crypto</label>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Bitcoin, Ethereum..."
              @input="searchCoins"
            />
            <div v-if="searchResults.length > 0" class="search-results">
              <div 
                v-for="coin in searchResults" 
                :key="coin.id" 
                @click="selectCoin(coin)"
                class="search-result-item"
              >
                <img :src="coin.thumb" :alt="coin.name" />
                <span>{{ coin.name }} ({{ coin.symbol }})</span>
              </div>
            </div>
          </div>

          <div v-if="selectedCoin" class="selected-coin">
            <p>✅ {{ selectedCoin.name }} ({{ selectedCoin.symbol }})</p>
          </div>

          <div class="form-group">
            <label>Quantité</label>
            <input 
              v-model="newAsset.quantity" 
              type="number" 
              step="0.00000001"
              placeholder="0.00"
              required
            />
          </div>

          <div class="form-group">
            <label>Prix d'achat (optionnel)</label>
            <input 
              v-model="newAsset.purchasePrice" 
              type="number" 
              step="0.01"
              placeholder="0.00"
            />
          </div>

          <div v-if="addError" class="error">{{ addError }}</div>

          <button type="submit" class="btn-primary" :disabled="!selectedCoin || adding">
            {{ adding ? 'Ajout...' : 'Ajouter' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { usePortfolioStore } from '../stores/portfolio';
import { cryptoService } from '../services/crypto';

const router = useRouter();
const authStore = useAuthStore();
const portfolioStore = usePortfolioStore();

const showAddModal = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const selectedCoin = ref(null);
const addError = ref('');
const adding = ref(false);

const newAsset = ref({
  quantity: '',
  purchasePrice: '',
});

const assets = computed(() => portfolioStore.assetsWithPrices);
const totalValue = computed(() => portfolioStore.totalValue);

onMounted(async () => {
  await authStore.fetchUser();
  await portfolioStore.fetchAssets();
  
  setInterval(() => {
    portfolioStore.fetchPrices();
  }, 30000);
});

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};

let searchTimeout;
const searchCoins = () => {
  clearTimeout(searchTimeout);
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }
  
  searchTimeout = setTimeout(async () => {
    try {
      const results = await cryptoService.searchCoins(searchQuery.value);
      searchResults.value = results;
    } catch (error) {
      console.error('Search error:', error);
    }
  }, 300);
};

const selectCoin = (coin) => {
  selectedCoin.value = coin;
  searchQuery.value = '';
  searchResults.value = [];
};

const handleAddAsset = async () => {
  addError.value = '';
  adding.value = true;

  try {
    await portfolioStore.addAsset({
      symbol: selectedCoin.value.symbol.toUpperCase(),
      name: selectedCoin.value.name,
      coinId: selectedCoin.value.id,
      quantity: newAsset.value.quantity,
      purchasePrice: newAsset.value.purchasePrice || null,
    });
    closeModal();
  } catch (error) {
    addError.value = error.response?.data?.error || 'Erreur lors de l\'ajout';
  } finally {
    adding.value = false;
  }
};

const deleteAsset = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet actif ?')) {
    try {
      await portfolioStore.deleteAsset(id);
    } catch (error) {
      alert('Erreur lors de la suppression');
    }
  }
};

const closeModal = () => {
  showAddModal.value = false;
  selectedCoin.value = null;
  searchQuery.value = '';
  searchResults.value = [];
  newAsset.value = { quantity: '', purchasePrice: '' };
  addError.value = '';
};
</script>

<style scoped>
.dashboard { min-height: 100vh; background: #f5f7fa; }
.navbar { background: white; border-bottom: 1px solid #e1e8ed; padding: 16px 0; }
.nav-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
.nav-content h2 { margin: 0; color: #667eea; }
.nav-actions { display: flex; align-items: center; gap: 16px; }
.nav-link { padding: 8px 16px; color: #666; text-decoration: none; border-radius: 6px; transition: all 0.2s; }
.nav-link:hover { background: #f5f7fa; color: #667eea; }
.nav-link.active { background: #667eea; color: white; }
.user-info { color: #666; font-size: 14px; }
.btn-logout { padding: 8px 16px; background: #f5f7fa; border: 1px solid #e1e8ed; border-radius: 6px; color: #333; cursor: pointer; font-size: 14px; transition: background 0.2s; }
.btn-logout:hover { background: #e1e8ed; }
.container { max-width: 1200px; margin: 0 auto; padding: 32px 20px; }
.header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.portfolio-summary h1 { margin: 0 0 8px; color: #333; }
.total-value { display: flex; flex-direction: column; }
.total-value .label { font-size: 14px; color: #666; }
.total-value .amount { font-size: 32px; font-weight: 700; color: #667eea; }
.btn-add { padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s; }
.btn-add:hover { transform: translateY(-2px); }
.loading { text-align: center; padding: 48px; color: #666; }
.empty-state { text-align: center; padding: 64px 20px; background: white; border-radius: 12px; }
.empty-state p { color: #666; margin-bottom: 24px; }
.btn-primary { padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
.assets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
.asset-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s; }
.asset-card:hover { transform: translateY(-4px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
.asset-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; }
.asset-header h3 { margin: 0 0 4px; color: #333; font-size: 20px; }
.asset-name { margin: 0; color: #666; font-size: 13px; }
.btn-delete { background: #fee; border: none; width: 32px; height: 32px; border-radius: 6px; font-size: 24px; color: #c33; cursor: pointer; transition: background 0.2s; }
.btn-delete:hover { background: #fdd; }
.asset-details { display: flex; flex-direction: column; gap: 12px; }
.detail-row { display: flex; justify-content: space-between; font-size: 14px; }
.detail-row span:first-child { color: #666; }
.detail-row .value { font-weight: 600; color: #333; }
.detail-row .positive { color: #22c55e; }
.detail-row .negative { color: #ef4444; }
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px; border-bottom: 1px solid #e1e8ed; }
.modal-header h2 { margin: 0; color: #333; }
.btn-close { background: none; border: none; font-size: 32px; color: #666; cursor: pointer; padding: 0; width: 32px; height: 32px; }
.modal-content form { padding: 24px; }
.form-group { margin-bottom: 20px; position: relative; }
.form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
.form-group input { width: 100%; padding: 12px 16px; border: 2px solid #e1e8ed; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
.form-group input:focus { outline: none; border-color: #667eea; }
.search-results { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #e1e8ed; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10; margin-top: 4px; }
.search-result-item { padding: 12px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: background 0.2s; }
.search-result-item:hover { background: #f5f7fa; }
.search-result-item img { width: 24px; height: 24px; }
.selected-coin { background: #efe; padding: 12px; border-radius: 6px; margin-bottom: 16px; }
.selected-coin p { margin: 0; color: #3c3; }
.error { background: #fee; color: #c33; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
</style>
