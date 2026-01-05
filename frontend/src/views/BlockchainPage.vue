<template>
  <div class="blockchain-page">
    <nav class="navbar">
      <div class="nav-content">
        <h2><Flame :size="24" /> Medux Wallet</h2>
        <div class="nav-actions">
          <router-link to="/dashboard" class="nav-link">Portfolio</router-link>
          <router-link to="/blockchain" class="nav-link active">Blockchain</router-link>
          <span class="user-info">{{ authStore.user?.email }}</span>
          <button @click="handleLogout" class="btn-logout"><LogOut :size="18" /> Déconnexion</button>
        </div>
      </div>
    </nav>

    <div class="container">
      <div class="header-section">
        <h1><Pickaxe :size="32" /> Bitcoin Blockchain Explorer</h1>
        <p class="subtitle">Explorez la blockchain Bitcoin en temps réel</p>
      </div>

      <!-- Stats du réseau -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><DollarSign :size="32" /></div>
          <div class="stat-content">
            <span class="stat-label">Prix Bitcoin</span>
            <span class="stat-value">${{ formatNumber(stats.market_price_usd) }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><Zap :size="32" /></div>
          <div class="stat-content">
            <span class="stat-label">Hash Rate</span>
            <span class="stat-value">{{ formatHashRate(stats.hash_rate) }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><LinkIcon :size="32" /></div>
          <div class="stat-content">
            <span class="stat-label">Hauteur du bloc</span>
            <span class="stat-value">{{ formatNumber(latestBlock.height) }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><Clock :size="32" /></div>
          <div class="stat-content">
            <span class="stat-label">Mempool</span>
            <span class="stat-value">{{ mempool.length }} tx</span>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button 
          @click="activeTab = 'blocks'" 
          :class="['tab', {active: activeTab === 'blocks'}]"
        >
          <Package :size="20" /> Derniers Blocs
        </button>
        <button 
          @click="activeTab = 'mempool'" 
          :class="['tab', {active: activeTab === 'mempool'}]"
        >
          <RefreshCw :size="20" /> Mempool ({{ mempool.length }})
        </button>
        <button 
          @click="activeTab = 'search'" 
          :class="['tab', {active: activeTab === 'search'}]"
        >
          <Search :size="20" /> Recherche
        </button>
      </div>

      <!-- Tab Blocks -->
      <div v-if="activeTab === 'blocks'" class="tab-content">
        <div v-if="loading" class="loading">Chargement des blocs...</div>
        <div v-else class="blocks-list">
          <div v-for="block in blocks" :key="block.hash" class="block-card">
            <div class="block-header">
              <div class="block-info">
                <h3>Bloc #{{ formatNumber(block.height) }}</h3>
                <p class="block-time">{{ formatTime(block.time) }}</p>
              </div>
              <div class="block-badge">{{ block.tx.length }} transactions</div>
            </div>
            <div class="block-details">
              <div class="detail-row">
                <span>Hash</span>
                <span class="hash">{{ truncateHash(block.hash) }}</span>
              </div>
              <div class="detail-row">
                <span>Mineur</span>
                <span class="value">{{ block.miner || 'Inconnu' }}</span>
              </div>
              <div class="detail-row">
                <span>Taille</span>
                <span class="value">{{ formatBytes(block.size) }}</span>
              </div>
              <div class="detail-row">
                <span>Poids</span>
                <span class="value">{{ formatNumber(block.weight) }} WU</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Mempool -->
      <div v-if="activeTab === 'mempool'" class="tab-content">
        <div v-if="loadingMempool" class="loading">Chargement du mempool...</div>
        <div v-else class="transactions-list">
          <div class="mempool-info">
            <p>Transactions en attente de confirmation</p>
            <button @click="refreshMempool" class="btn-refresh"><RefreshCw :size="18" /> Actualiser</button>
          </div>
          <div v-for="tx in mempool" :key="tx.hash" class="tx-card">
            <div class="tx-header">
              <span class="tx-hash">{{ truncateHash(tx.hash) }}</span>
              <span class="tx-value">{{ satoshiToBTC(tx.out.reduce((sum, o) => sum + o.value, 0)) }} BTC</span>
            </div>
            <div class="tx-details">
              <div class="detail-row">
                <span>{{ tx.inputs?.length || 0 }} entrées</span>
                <span>{{ tx.out?.length || 0 }} sorties</span>
              </div>
              <div class="detail-row">
                <span>Taille: {{ formatBytes(tx.size) }}</span>
                <span>Frais: {{ satoshiToBTC(tx.fee || 0) }} BTC</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Search -->
      <div v-if="activeTab === 'search'" class="tab-content">
        <div class="search-section">
          <h3>Rechercher une transaction, bloc ou adresse</h3>
          <div class="search-form">
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Entrez un hash de transaction, bloc ou adresse Bitcoin..."
              @keyup.enter="handleSearch"
            />
            <button @click="handleSearch" class="btn-search" :disabled="!searchQuery || searching">
              <Search :size="18" /> {{ searching ? 'Recherche...' : 'Rechercher' }}
            </button>
          </div>
          <div v-if="searchError" class="error">{{ searchError }}</div>
          <div v-if="searchResult" class="search-result">
            <h4>Résultat</h4>
            <pre>{{ JSON.stringify(searchResult, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { blockchainService } from '../services/blockchain';
import { Flame, Pickaxe, DollarSign, Zap, Link as LinkIcon, Clock, Package, RefreshCw, Search, X, LogOut } from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();

const activeTab = ref('blocks');
const blocks = ref([]);
const mempool = ref([]);
const stats = ref({});
const latestBlock = ref({});
const loading = ref(true);
const loadingMempool = ref(false);
const searchQuery = ref('');
const searchResult = ref(null);
const searchError = ref('');
const searching = ref(false);

onMounted(async () => {
  await loadData();
  
  // Actualisation automatique toutes les 30 secondes
  setInterval(() => {
    loadData();
  }, 30000);
});

const loadData = async () => {
  try {
    const [blocksData, statsData, latestData] = await Promise.all([
      blockchainService.getLatestBlocks(10),
      blockchainService.getStats(),
      blockchainService.getLatestBlock(),
    ]);
    
    blocks.value = blocksData;
    stats.value = statsData;
    latestBlock.value = latestData;
    loading.value = false;
  } catch (error) {
    console.error('Error loading blockchain data:', error);
    loading.value = false;
  }
};

const refreshMempool = async () => {
  loadingMempool.value = true;
  try {
    mempool.value = await blockchainService.getUnconfirmedTransactions();
  } catch (error) {
    console.error('Error loading mempool:', error);
  } finally {
    loadingMempool.value = false;
  }
};

// Charger le mempool au premier affichage
const handleTabChange = async (tab) => {
  activeTab.value = tab;
  if (tab === 'mempool' && mempool.value.length === 0) {
    await refreshMempool();
  }
};

const handleSearch = async () => {
  if (!searchQuery.value) return;
  
  searching.value = true;
  searchError.value = '';
  searchResult.value = null;
  
  try {
    // Essayer comme transaction d'abord
    try {
      const txResult = await blockchainService.getTransaction(searchQuery.value);
      searchResult.value = txResult;
      return;
    } catch {}
    
    // Essayer comme bloc
    try {
      const blockResult = await blockchainService.getBlock(searchQuery.value);
      searchResult.value = blockResult;
      return;
    } catch {}
    
    // Essayer comme adresse
    try {
      const addressResult = await blockchainService.getAddress(searchQuery.value);
      searchResult.value = addressResult;
      return;
    } catch {}
    
    searchError.value = 'Aucun résultat trouvé';
  } catch (error) {
    searchError.value = 'Erreur lors de la recherche';
  } finally {
    searching.value = false;
  }
};

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};

// Formatters
const formatNumber = (num) => {
  return num ? num.toLocaleString() : '0';
};

const formatHashRate = (rate) => {
  if (!rate) return '0 EH/s';
  const eh = (rate / 1e18).toFixed(2);
  return `${eh} EH/s`;
};

const formatBytes = (bytes) => {
  if (!bytes) return '0 B';
  const kb = bytes / 1024;
  if (kb < 1024) return `${kb.toFixed(2)} KB`;
  return `${(kb / 1024).toFixed(2)} MB`;
};

const formatTime = (timestamp) => {
  if (!timestamp) return '';
  const date = new Date(timestamp * 1000);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);
  
  if (diff < 60) return `Il y a ${diff}s`;
  if (diff < 3600) return `Il y a ${Math.floor(diff / 60)}min`;
  if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)}h`;
  return date.toLocaleString('fr-FR');
};

const truncateHash = (hash) => {
  if (!hash) return '';
  return `${hash.substring(0, 10)}...${hash.substring(hash.length - 10)}`;
};

const satoshiToBTC = (satoshi) => {
  return (satoshi / 100000000).toFixed(8);
};

// Auto-load mempool on tab change
const observer = {
  blocks: () => {},
  mempool: async () => {
    if (mempool.value.length === 0) {
      await refreshMempool();
    }
  },
  search: () => {},
};

// Watch for tab changes
const previousTab = ref(activeTab.value);
setInterval(() => {
  if (activeTab.value !== previousTab.value) {
    previousTab.value = activeTab.value;
    if (observer[activeTab.value]) {
      observer[activeTab.value]();
    }
  }
}, 100);
</script>

<style scoped>
.blockchain-page { min-height: 100vh; background: #f5f7fa; }
.navbar { background: white; border-bottom: 1px solid #e1e8ed; padding: 16px 0; }
.nav-content { max-width: 1400px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
.nav-content h2 { margin: 0; color: var(--primary); }
.nav-actions { display: flex; align-items: center; gap: 16px; }
.nav-link { padding: 8px 16px; color: #666; text-decoration: none; border-radius: 6px; transition: all 0.2s; }
.nav-link:hover { background: #f5f7fa; color: var(--primary); }
.nav-link.active { background: var(--primary); color: white; }
.user-info { color: #666; font-size: 14px; }
.btn-logout { padding: 8px 16px; background: #f5f7fa; border: 1px solid #e1e8ed; border-radius: 6px; color: #333; cursor: pointer; font-size: 14px; }
.container { max-width: 1400px; margin: 0 auto; padding: 32px 20px; }
.header-section { text-align: center; margin-bottom: 32px; }
.header-section h1 { margin: 0 0 8px; color: #333; }
.subtitle { color: #666; font-size: 16px; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 32px; }
.stat-card { background: white; border-radius: 12px; padding: 24px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.stat-icon { font-size: 32px; }
.stat-content { display: flex; flex-direction: column; }
.stat-label { font-size: 13px; color: #666; }
.stat-value { font-size: 20px; font-weight: 700; color: #333; }
.tabs { display: flex; gap: 8px; margin-bottom: 24px; background: white; padding: 8px; border-radius: 12px; }
.tab { flex: 1; padding: 12px 24px; background: transparent; border: none; border-radius: 8px; font-size: 15px; font-weight: 500; cursor: pointer; transition: all 0.2s; color: #666; }
.tab:hover { background: #f5f7fa; }
.tab.active { background: var(--primary); color: white; }
.tab-content { background: white; border-radius: 12px; padding: 24px; }
.loading { text-align: center; padding: 48px; color: #666; }
.blocks-list, .transactions-list { display: flex; flex-direction: column; gap: 16px; }
.block-card, .tx-card { background: #f5f7fa; border-radius: 8px; padding: 20px; transition: transform 0.2s; }
.block-card:hover, .tx-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.block-header, .tx-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.block-info h3 { margin: 0 0 4px; color: #333; font-size: 18px; }
.block-time { margin: 0; color: #666; font-size: 13px; }
.block-badge { background: var(--primary); color: white; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; }
.block-details, .tx-details { display: flex; flex-direction: column; gap: 10px; }
.detail-row { display: flex; justify-content: space-between; font-size: 14px; }
.detail-row span:first-child { color: #666; }
.detail-row .value, .detail-row .hash { color: #333; font-weight: 500; font-family: monospace; }
.tx-hash { font-family: monospace; font-size: 14px; color: var(--primary); }
.tx-value { font-weight: 700; color: #22c55e; }
.mempool-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #e1e8ed; }
.btn-refresh { padding: 8px 16px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
.search-section h3 { margin: 0 0 16px; color: #333; }
.search-form { display: flex; gap: 12px; margin-bottom: 20px; }
.search-form input { flex: 1; padding: 12px 16px; border: 2px solid #e1e8ed; border-radius: 8px; font-size: 14px; }
.btn-search { padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; white-space: nowrap; }
.btn-search:disabled { opacity: 0.6; cursor: not-allowed; }
.error { background: #fee; color: #c33; padding: 12px; border-radius: 6px; margin-bottom: 16px; }
.search-result { background: #f5f7fa; border-radius: 8px; padding: 20px; }
.search-result h4 { margin: 0 0 12px; color: #333; }
.search-result pre { background: #2d3748; color: #e2e8f0; padding: 16px; border-radius: 6px; overflow-x: auto; font-size: 12px; margin: 0; }
</style>
