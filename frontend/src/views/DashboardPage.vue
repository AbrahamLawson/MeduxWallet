<template>
  <div class="dashboard">
    <nav class="navbar">
      <div class="nav-content">
        <h2 class="logo-animated"><Flame :size="24" /> Medux Wallet</h2>
        <div class="nav-actions">
          <router-link to="/dashboard" class="nav-link active">Portfolio</router-link>
          <router-link to="/blockchain" class="nav-link">Blockchain</router-link>
          <span class="user-info">{{ authStore.user?.email }}</span>
          <button @click="handleLogout" class="btn-logout"><LogOut :size="18" /> Déconnexion</button>
        </div>
      </div>
    </nav>

    <div class="container">
      <!-- Stats Header -->
      <div class="stats-header fade-in">
        <div class="stat-card-big pulse">
          <div class="stat-icon"><Wallet :size="48" /></div>
          <div class="stat-content">
            <span class="stat-label">Valeur totale du portefeuille</span>
            <span class="stat-value animated-number">${{ totalValue.toFixed(2) }}</span>
            <span v-if="totalProfitLoss !== 0" :class="['stat-change', totalProfitLoss >= 0 ? 'positive' : 'negative']">
              <TrendingUp v-if="totalProfitLoss >= 0" :size="16" />
              <TrendingDown v-else :size="16" />
              ${{ Math.abs(totalProfitLoss).toFixed(2) }}
              ({{ ((totalProfitLoss / (totalValue - totalProfitLoss)) * 100).toFixed(2) }}%)
            </span>
          </div>
        </div>

        <div class="stat-card pulse" style="animation-delay: 0.1s">
          <div class="stat-icon"><BarChart3 :size="48" /></div>
          <div class="stat-content">
            <span class="stat-label">Nombre d'actifs</span>
            <span class="stat-value">{{ assets.length }}</span>
          </div>
        </div>

        <div class="stat-card pulse" style="animation-delay: 0.2s">
          <div class="stat-icon"><RefreshCw :size="48" /></div>
          <div class="stat-content">
            <span class="stat-label">Dernière mise à jour</span>
            <span class="stat-value-small">{{ lastUpdateFormatted }}</span>
          </div>
        </div>
      </div>

      <!-- Add Asset Button -->
      <div class="header-section slide-in">
        <h2>Mes Actifs</h2>
        <button @click="showAddModal = true" class="btn-add glow">
          <Plus :size="20" /> Ajouter un actif
        </button>
      </div>

      <div v-if="portfolioStore.loading" class="loading">
        <div class="spinner"></div>
        <p>Chargement de vos actifs...</p>
      </div>

      <div v-else-if="assets.length === 0" class="empty-state bounce-in">
        <div class="empty-icon"><Inbox :size="80" /></div>
        <p>Votre portefeuille est vide</p>
        <button @click="showAddModal = true" class="btn-primary">
          Ajouter votre premier actif
        </button>
      </div>

      <!-- Assets Grid avec animations -->
      <div v-else class="assets-grid">
        <div 
          v-for="(asset, index) in assets" 
          :key="asset.id" 
          class="asset-card slide-up"
          :style="{ animationDelay: `${index * 0.1}s` }"
        >
          <div class="asset-header">
            <div class="asset-title-group">
              <div v-if="asset.image" class="coin-image">
                <img :src="asset.image" :alt="asset.name" />
              </div>
              <div>
                <div class="asset-title-row">
                  <h3>{{ asset.symbol }}</h3>
                  <span v-if="asset.rank" class="rank-badge">#{{ asset.rank }}</span>
                </div>
                <p class="asset-name">{{ asset.name }}</p>
              </div>
            </div>
            <button @click="deleteAsset(asset.id)" class="btn-delete hover-scale"><X :size="24" /></button>
          </div>

          <!-- Price with sparkline -->
          <div class="price-section">
            <div class="current-price">
              <span class="price-label">Prix actuel</span>
              <span class="price-value">${{ asset.currentPrice.toFixed(2) }}</span>
            </div>
            <div :class="['price-change', asset.change24h >= 0 ? 'positive' : 'negative']">
              <TrendingUp v-if="asset.change24h >= 0" :size="16" />
              <TrendingDown v-else :size="16" />
              {{ Math.abs(asset.change24h).toFixed(2) }}%
            </div>
          </div>

          <!-- Mini sparkline si disponible -->
          <div v-if="asset.sparkline && asset.sparkline.length > 0" class="sparkline">
            <svg :viewBox="`0 0 100 30`" class="sparkline-svg">
              <polyline
                :points="getSparklinePoints(asset.sparkline)"
                :class="asset.change24h >= 0 ? 'positive' : 'negative'"
              />
            </svg>
          </div>

          <!-- Asset Details -->
          <div class="asset-details">
            <div class="detail-row">
              <span>Quantité</span>
              <span class="value">{{ parseFloat(asset.quantity).toFixed(8) }}</span>
            </div>
            <div class="detail-row">
              <span>Valeur</span>
              <span class="value highlight">${{ asset.currentValue.toFixed(2) }}</span>
            </div>

            <div v-if="asset.purchasePrice" class="detail-section">
              <div class="detail-row">
                <span>Prix d'achat</span>
                <span class="value">${{ parseFloat(asset.purchasePrice).toFixed(2) }}</span>
              </div>
              <div class="detail-row">
                <span>P&L</span>
                <span :class="['value', 'bold', asset.profitLoss >= 0 ? 'positive' : 'negative']">
                  {{ asset.profitLoss >= 0 ? '+' : '' }}${{ asset.profitLoss.toFixed(2) }}
                  <small>({{ asset.profitLossPercent.toFixed(2) }}%)</small>
                </span>
              </div>
            </div>

            <!-- Extra Data -->
            <div class="extra-data">
              <div v-if="asset.marketCap" class="extra-item">
                <span>Market Cap</span>
                <span>${{ formatLargeNumber(asset.marketCap) }}</span>
              </div>
              <div v-if="asset.volume24h" class="extra-item">
                <span>Volume 24h</span>
                <span>${{ formatLargeNumber(asset.volume24h) }}</span>
              </div>
              <div v-if="asset.high24h && asset.low24h" class="extra-item">
                <span>24h Range</span>
                <span>${{ asset.low24h.toFixed(2) }} - ${{ asset.high24h.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal (identique à avant) -->
    <div v-if="showAddModal" class="modal fade-in" @click.self="closeModal">
      <div class="modal-content scale-in">
        <div class="modal-header">
          <h2>Ajouter un actif</h2>
          <button @click="closeModal" class="btn-close"><X :size="28" /></button>
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
import { Flame, Wallet, BarChart3, RefreshCw, Inbox, Plus, X, TrendingUp, TrendingDown, LogOut } from 'lucide-vue-next';

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
const totalProfitLoss = computed(() => portfolioStore.totalProfitLoss);

const lastUpdateFormatted = computed(() => {
  if (!portfolioStore.lastUpdate) return 'Jamais';
  const now = new Date();
  const diff = Math.floor((now - portfolioStore.lastUpdate) / 1000);
  if (diff < 60) return `Il y a ${diff}s`;
  if (diff < 3600) return `Il y a ${Math.floor(diff / 60)}min`;
  return portfolioStore.lastUpdate.toLocaleTimeString('fr-FR');
});

onMounted(async () => {
  await authStore.fetchUser();
  await portfolioStore.fetchAssets();
  
  setInterval(() => {
    portfolioStore.fetchPrices();
    portfolioStore.fetchDetailedData();
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
    await portfolioStore.fetchDetailedData();
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

const formatLargeNumber = (num) => {
  if (num >= 1e9) return `${(num / 1e9).toFixed(2)}B`;
  if (num >= 1e6) return `${(num / 1e6).toFixed(2)}M`;
  if (num >= 1e3) return `${(num / 1e3).toFixed(2)}K`;
  return num.toFixed(2);
};

const getSparklinePoints = (data) => {
  if (!data || data.length === 0) return '';
  const max = Math.max(...data);
  const min = Math.min(...data);
  const range = max - min;
  
  return data.map((value, index) => {
    const x = (index / (data.length - 1)) * 100;
    const y = range > 0 ? 30 - ((value - min) / range) * 30 : 15;
    return `${x},${y}`;
  }).join(' ');
};
</script>

<style scoped>
/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { transform: translateX(-20px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.02); }
}

@keyframes bounceIn {
  0% { transform: scale(0.3); opacity: 0; }
  50% { transform: scale(1.05); }
  70% { transform: scale(0.9); }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes scaleIn {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

@keyframes glow {
  0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.5); }
  50% { box-shadow: 0 0 30px rgba(102, 126, 234, 0.8); }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Utility Classes */
.fade-in { animation: fadeIn 0.5s ease-out; }
.slide-in { animation: slideIn 0.6s ease-out; }
.slide-up { animation: slideUp 0.6s ease-out; }
.pulse { animation: pulse 2s ease-in-out infinite; }
.bounce-in { animation: bounceIn 0.8s ease-out; }
.scale-in { animation: scaleIn 0.3s ease-out; }
.glow { animation: glow 2s ease-in-out infinite; }
.hover-scale { transition: transform 0.2s; }
.hover-scale:hover { transform: scale(1.1); }

/* Base */
.dashboard { min-height: 100vh; background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%); }
.navbar { background: white; border-bottom: 1px solid #e1e8ed; padding: 16px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.nav-content { max-width: 1400px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
.logo-animated { margin: 0; color: var(--primary); font-size: 24px; transition: transform 0.3s; }
.logo-animated:hover { transform: scale(1.1) rotate(5deg); }
.nav-actions { display: flex; align-items: center; gap: 16px; }
.nav-link { padding: 8px 16px; color: #666; text-decoration: none; border-radius: 8px; transition: all 0.3s; font-weight: 500; }
.nav-link:hover { background: #f5f7fa; color: var(--primary); transform: translateY(-2px); }
.nav-link.active { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
.user-info { color: #666; font-size: 14px; }
.btn-logout { padding: 8px 16px; background: #f5f7fa; border: 1px solid #e1e8ed; border-radius: 8px; color: #333; cursor: pointer; font-size: 14px; transition: all 0.3s; }
.btn-logout:hover { background: #e1e8ed; transform: translateY(-2px); }

.container { max-width: 1400px; margin: 0 auto; padding: 32px 20px; }

/* Stats Header */
.stats-header { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-bottom: 40px; }
.stat-card, .stat-card-big { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s; }
.stat-card:hover, .stat-card-big:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
.stat-card-big { display: flex; align-items: center; gap: 20px; }
.stat-icon { font-size: 48px; }
.stat-content { display: flex; flex-direction: column; gap: 4px; }
.stat-label { font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 32px; font-weight: 700; color: #333; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.stat-value-small { font-size: 16px; font-weight: 600; color: var(--primary); }
.animated-number { transition: all 0.5s ease; }
.stat-change { font-size: 14px; font-weight: 600; }
.stat-change.positive { color: #22c55e; }
.stat-change.negative { color: #ef4444; }

/* Header Section */
.header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-section h2 { margin: 0; color: #333; font-size: 24px; }
.btn-add { padding: 14px 28px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
.btn-add:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); }
.btn-add span { font-size: 20px; }

/* Loading */
.loading { text-align: center; padding: 80px 20px; }
.spinner { width: 60px; height: 60px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary); border-radius: 50%; margin: 0 auto 20px; animation: spin 1s linear infinite; }
.loading p { color: #666; font-size: 16px; }

/* Empty State */
.empty-state { text-align: center; padding: 80px 20px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.empty-icon { font-size: 80px; margin-bottom: 20px; }
.empty-state p { color: #666; margin-bottom: 24px; font-size: 18px; }
.btn-primary { padding: 14px 28px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); }

/* Assets Grid */
.assets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 24px; }
.asset-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s; border: 2px solid transparent; }
.asset-card:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(0,0,0,0.15); border-color: var(--primary); }

.asset-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; }
.asset-title-group { display: flex; align-items: center; gap: 12px; }
.coin-image { width: 48px; height: 48px; border-radius: 50%; overflow: hidden; background: #f5f7fa; }
.coin-image img { width: 100%; height: 100%; object-fit: cover; }
.asset-title-row { display: flex; align-items: center; gap: 8px; }
.asset-card h3 { margin: 0 0 4px; color: #333; font-size: 22px; }
.rank-badge { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
.asset-name { margin: 0; color: #666; font-size: 13px; }
.btn-delete { background: #fee; border: none; width: 36px; height: 36px; border-radius: 8px; font-size: 24px; color: #c33; cursor: pointer; transition: all 0.3s; }
.btn-delete:hover { background: #fdd; transform: rotate(90deg); }

/* Price Section */
.price-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 16px; background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%); border-radius: 12px; }
.current-price { display: flex; flex-direction: column; }
.price-label { font-size: 12px; color: #666; text-transform: uppercase; }
.price-value { font-size: 24px; font-weight: 700; color: #333; }
.price-change { font-size: 16px; font-weight: 600; padding: 6px 12px; border-radius: 8px; }
.price-change.positive { background: #dcfce7; color: #22c55e; }
.price-change.negative { background: #fee2e2; color: #ef4444; }

/* Sparkline */
.sparkline { height: 50px; margin: 16px 0; }
.sparkline-svg { width: 100%; height: 100%; }
.sparkline-svg polyline { fill: none; stroke-width: 2; transition: stroke 0.3s; }
.sparkline-svg polyline.positive { stroke: #22c55e; }
.sparkline-svg polyline.negative { stroke: #ef4444; }

/* Details */
.asset-details { display: flex; flex-direction: column; gap: 12px; }
.detail-row { display: flex; justify-content: space-between; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #f5f7fa; }
.detail-row:last-child { border-bottom: none; }
.detail-row span:first-child { color: #666; }
.detail-row .value { font-weight: 600; color: #333; }
.detail-row .value.highlight { color: var(--primary); font-size: 16px; }
.detail-row .value.bold { font-weight: 700; }
.detail-row .positive { color: #22c55e; }
.detail-row .negative { color: #ef4444; }
.detail-row small { font-size: 11px; opacity: 0.8; }

.detail-section { margin-top: 12px; padding-top: 12px; border-top: 2px solid #e8ecf1; }

.extra-data { margin-top: 16px; padding: 12px; background: #f5f7fa; border-radius: 8px; display: flex; flex-direction: column; gap: 8px; }
.extra-item { display: flex; justify-content: space-between; font-size: 12px; }
.extra-item span:first-child { color: #666; }
.extra-item span:last-child { font-weight: 600; color: #333; }

/* Modal */
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px; backdrop-filter: blur(5px); }
.modal-content { background: white; border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px; border-bottom: 2px solid #e8ecf1; }
.modal-header h2 { margin: 0; color: #333; }
.btn-close { background: none; border: none; font-size: 32px; color: #666; cursor: pointer; padding: 0; width: 36px; height: 36px; border-radius: 8px; transition: all 0.3s; }
.btn-close:hover { background: #f5f7fa; transform: rotate(90deg); }

.modal-content form { padding: 24px; }
.form-group { margin-bottom: 20px; position: relative; }
.form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; }
.form-group input { width: 100%; padding: 12px 16px; border: 2px solid #e1e8ed; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: all 0.3s; }
.form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }

.search-results { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #e1e8ed; border-radius: 8px; max-height: 220px; overflow-y: auto; z-index: 10; margin-top: 4px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
.search-result-item { padding: 12px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.2s; }
.search-result-item:hover { background: #f5f7fa; transform: translateX(4px); }
.search-result-item img { width: 28px; height: 28px; border-radius: 50%; }

.selected-coin { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); padding: 14px; border-radius: 8px; margin-bottom: 16px; border: 2px solid #22c55e; }
.selected-coin p { margin: 0; color: #16a34a; font-weight: 600; }

.error { background: #fee2e2; color: #c33; padding: 14px; border-radius: 8px; margin-bottom: 16px; border: 2px solid #ef4444; font-size: 14px; }

.modal-content .btn-primary { width: 100%; }
.modal-content .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

/* Responsive */
@media (max-width: 1024px) {
  .stats-header { grid-template-columns: 1fr; }
  .assets-grid { grid-template-columns: 1fr; }
}
</style>
