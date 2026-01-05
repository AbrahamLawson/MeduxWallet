<template>
  <div class="auth-container">
    <div class="auth-box">
      <h1><Sparkles :size="32" /> Inscription</h1>
      <p class="subtitle">Créez votre compte Medux Wallet</p>
      
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label><User :size="18" /> Nom (optionnel)</label>
          <input 
            v-model="name" 
            type="text" 
            placeholder="Votre nom"
          />
        </div>

        <div class="form-group">
          <label><Mail :size="18" /> Email</label>
          <input 
            v-model="email" 
            type="email" 
            placeholder="votre@email.com"
            required
          />
        </div>

        <div class="form-group">
          <label><Lock :size="18" /> Mot de passe</label>
          <input 
            v-model="password" 
            type="password" 
            placeholder="••••••••"
            required
          />
        </div>

        <div class="form-group">
          <label><Lock :size="18" /> Confirmer le mot de passe</label>
          <input 
            v-model="confirmPassword" 
            type="password" 
            placeholder="••••••••"
            required
          />
        </div>

        <div v-if="error" class="error">{{ error }}</div>
        <div v-if="success" class="success">{{ success }}</div>

        <button type="submit" class="btn-primary" :disabled="loading">
          <UserPlus :size="20" /> {{ loading ? 'Inscription...' : 'S\'inscrire' }}
        </button>
      </form>

      <p class="link-text">
        Déjà un compte ? 
        <router-link to="/login">Se connecter</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { Sparkles, User, Mail, Lock, UserPlus } from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const error = ref('');
const success = ref('');
const loading = ref(false);

const handleRegister = async () => {
  error.value = '';
  success.value = '';

  if (password.value !== confirmPassword.value) {
    error.value = 'Les mots de passe ne correspondent pas';
    return;
  }

  if (password.value.length < 6) {
    error.value = 'Le mot de passe doit contenir au moins 6 caractères';
    return;
  }

  loading.value = true;

  try {
    await authStore.register(email.value, password.value, name.value);
    success.value = 'Compte créé avec succès ! Redirection...';
    setTimeout(() => {
      router.push('/login');
    }, 2000);
  } catch (err) {
    error.value = err.response?.data?.error || 'Erreur lors de l\'inscription';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  padding: 20px;
}

.auth-box {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  width: 100%;
  max-width: 420px;
}

h1 {
  margin: 0 0 10px;
  color: #333;
  font-size: 28px;
  text-align: center;
}

.subtitle {
  text-align: center;
  color: #666;
  margin-bottom: 30px;
  font-size: 14px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e1e8ed;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: var(--primary);
}

.btn-primary {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  background: #fee;
  color: #c33;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 16px;
  font-size: 14px;
}

.success {
  background: #efe;
  color: #3c3;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 16px;
  font-size: 14px;
}

.link-text {
  text-align: center;
  margin-top: 20px;
  color: #666;
  font-size: 14px;
}

.link-text a {
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
}

.link-text a:hover {
  text-decoration: underline;
}
</style>
