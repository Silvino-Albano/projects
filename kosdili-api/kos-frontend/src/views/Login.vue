<template>
  <div class="page">
    <div class="card">

      <div class="brand">
        <span class="brand-icon">⌂</span>
        <span class="brand-name">KosDili</span>
      </div>

      <div class="heading">
        <h1>Selamat datang</h1>
        <p>Masuk ke akun Anda untuk melanjutkan</p>
      </div>

      <form @submit.prevent="handleLogin" class="form" novalidate>
        <div class="field" :class="{ 'field--error': errors.email }">
          <label for="email">Email</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 20 20" fill="none">
              <path d="M2.5 6.5l7.5 5 7.5-5M3 5h14a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V6a1 1 0 011-1z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="nama@email.com"
              autocomplete="email"
              @blur="validateEmail"
            />
          </div>
          <span class="field-error" v-if="errors.email">{{ errors.email }}</span>
        </div>

        <div class="field" :class="{ 'field--error': errors.password }">
          <label for="password">Password</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 20 20" fill="none">
              <rect x="4" y="9" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
              <path d="M7 9V6.5a3 3 0 016 0V9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="current-password"
            />
            <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
              <svg v-if="!showPassword" viewBox="0 0 20 20" fill="none">
                <path d="M10 4C5.5 4 2 10 2 10s3.5 6 8 6 8-6 8-6-3.5-6-8-6z" stroke="currentColor" stroke-width="1.4"/>
                <circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.4"/>
              </svg>
              <svg v-else viewBox="0 0 20 20" fill="none">
                <path d="M3 3l14 14M8.5 8.6A2.5 2.5 0 0012.4 12M6 6.1C4.3 7.2 3 9 3 10s3.5 6 8 6c1.5 0 2.9-.4 4-1.1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                <path d="M10 4c4.5 0 8 6 8 6s-.7 1.3-2 2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <span class="field-error" v-if="errors.password">{{ errors.password }}</span>
        </div>

        <div class="alert" v-if="errorMsg" role="alert">
          <svg viewBox="0 0 20 20" fill="none">
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.4"/>
            <path d="M10 6v4M10 13.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
          </svg>
          {{ errorMsg }}
        </div>

        <button type="submit" class="btn-submit" :class="{ 'btn-submit--loading': loading }" :disabled="loading">
          <span class="btn-text">{{ loading ? 'Memproses…' : 'Masuk' }}</span>
          <span class="btn-spinner" v-if="loading"></span>
          <svg v-else class="btn-arrow" viewBox="0 0 20 20" fill="none">
            <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </form>

      <p class="footer">
        Belum punya akun?
        <router-link to="/register">Daftar sekarang</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'

const email        = ref('')
const password     = ref('')
const loading      = ref(false)
const errorMsg     = ref('')
const showPassword = ref(false)
const auth         = useAuthStore()
const router       = useRouter()

const errors = reactive({ email: '', password: '' })

const validateEmail = () => {
  errors.email = email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)
    ? 'Format email tidak valid'
    : ''
}

const handleLogin = async () => {
  loading.value  = true
  errorMsg.value = ''
  try {
    const res = await auth.login({ email: email.value, password: password.value })
    router.push('/')
  } catch (err) {
    // ✅ Jika belum verifikasi email
    if (err.response?.data?.action === 'verify_email') {
      localStorage.setItem('pending_email', email.value)
      router.push('/verify-email')
      return
    }
    errorMsg.value = err.response?.data?.message || 'Login gagal.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0e0f11;
  background-image:
    radial-gradient(ellipse 60% 50% at 20% 10%, #1a1f2e 0%, transparent 60%),
    radial-gradient(ellipse 50% 40% at 80% 90%, #1e1610 0%, transparent 60%);
  font-family: 'Sora', sans-serif;
  padding: 1.5rem;
}

.card {
  width: 100%;
  max-width: 420px;
  background: #16181d;
  border: 1px solid #2a2d35;
  border-radius: 20px;
  padding: 2.5rem 2.25rem;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 2rem;
}
.brand-icon {
  font-size: 20px;
  color: #d4a055;
}
.brand-name {
  font-size: 15px;
  font-weight: 600;
  color: #f0ebe3;
  letter-spacing: 0.02em;
}

.heading {
  margin-bottom: 2rem;
}
.heading h1 {
  font-size: 22px;
  font-weight: 600;
  color: #f0ebe3;
  letter-spacing: -0.02em;
  margin-bottom: 6px;
}
.heading p {
  font-size: 13.5px;
  color: #6b7280;
  font-weight: 300;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.field label {
  font-size: 12.5px;
  font-weight: 500;
  color: #9ca3af;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.input-icon {
  position: absolute;
  left: 13px;
  width: 16px;
  height: 16px;
  color: #4b5563;
  pointer-events: none;
  flex-shrink: 0;
}

.input-wrap input {
  width: 100%;
  height: 46px;
  background: #1e2028;
  border: 1px solid #2a2d35;
  border-radius: 10px;
  color: #f0ebe3;
  font-family: 'Sora', sans-serif;
  font-size: 14px;
  font-weight: 300;
  padding: 0 42px 0 40px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input-wrap input::placeholder { color: #3d4148; }
.input-wrap input:hover { border-color: #3d4148; }
.input-wrap input:focus {
  border-color: #d4a055;
  box-shadow: 0 0 0 3px rgba(212, 160, 85, 0.12);
}

.field--error .input-wrap input {
  border-color: #7f1d1d;
  box-shadow: 0 0 0 3px rgba(127, 29, 29, 0.15);
}

.field-error {
  font-size: 12px;
  color: #f87171;
  font-weight: 300;
}

.eye-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: #4b5563;
  padding: 4px;
  display: flex;
  align-items: center;
  transition: color 0.15s;
}
.eye-btn:hover { color: #9ca3af; }
.eye-btn svg { width: 16px; height: 16px; }

.alert {
  display: flex;
  align-items: center;
  gap: 9px;
  background: rgba(127, 29, 29, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 10px;
  padding: 11px 14px;
  font-size: 13px;
  color: #fca5a5;
  font-weight: 300;
}
.alert svg { width: 16px; height: 16px; flex-shrink: 0; }

.btn-submit {
  height: 48px;
  background: #d4a055;
  border: none;
  border-radius: 10px;
  color: #0e0f11;
  font-family: 'Sora', sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 0.4rem;
  transition: background 0.2s, transform 0.1s;
  letter-spacing: 0.01em;
}
.btn-submit:hover:not(:disabled) { background: #c49040; }
.btn-submit:active:not(:disabled) { transform: scale(0.98); }
.btn-submit:disabled { opacity: 0.55; cursor: not-allowed; }

.btn-arrow { width: 17px; height: 17px; }

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(14,15,17,0.3);
  border-top-color: #0e0f11;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.footer {
  margin-top: 1.75rem;
  text-align: center;
  font-size: 13px;
  color: #4b5563;
  font-weight: 300;
}
.footer a {
  color: #d4a055;
  text-decoration: none;
  font-weight: 500;
  margin-left: 4px;
  transition: color 0.15s;
}
.footer a:hover { color: #e8b870; }
</style>