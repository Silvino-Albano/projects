<template>
  <div class="page">
    <div class="card">

      <div class="brand">
        <span class="brand-icon">⌂</span>
        <span class="brand-name">KosDili</span>
      </div>

      <div class="heading">
        <h1>Buat Akun</h1>
        <p>Daftar untuk mulai menggunakan KosDili</p>
      </div>

      <form @submit.prevent="handleRegister" novalidate>

        <!-- Pilih Role -->
        <div class="role-switch">
          <button
            type="button"
            class="role-btn"
            :class="{ active: form.role === 'user' }"
            @click="form.role = 'user'"
          >
            <span class="role-icon">👤</span>
            <span class="role-label">Pencari Kos</span>
            <span class="role-desc">Saya ingin cari & booking kos</span>
          </button>
          <button
            type="button"
            class="role-btn"
            :class="{ active: form.role === 'owner' }"
            @click="form.role = 'owner'"
          >
            <span class="role-icon">🏡</span>
            <span class="role-label">Pemilik Kos</span>
            <span class="role-desc">Saya ingin daftarkan kos saya</span>
          </button>
        </div>

        <!-- Nama -->
        <div class="field" :class="{ 'field--error': errors.name }">
          <label>Nama Lengkap</label>
          <div class="input-wrap">
            <svg class="iico" viewBox="0 0 20 20" fill="none">
              <circle cx="10" cy="7" r="3.5" stroke="currentColor" stroke-width="1.4"/>
              <path d="M3 17c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <input v-model="form.name" placeholder="Silvino Albano" @blur="validate('name')" />
          </div>
          <span class="ferr" v-if="errors.name">{{ errors.name }}</span>
        </div>

        <!-- Email -->
        <div class="field" :class="{ 'field--error': errors.email }">
          <label>Email</label>
          <div class="input-wrap">
            <svg class="iico" viewBox="0 0 20 20" fill="none">
              <path d="M2.5 6.5l7.5 5 7.5-5M3 5h14a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V6a1 1 0 011-1z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <input v-model="form.email" type="email" placeholder="nama@email.com" @blur="validate('email')" />
          </div>
          <span class="ferr" v-if="errors.email">{{ errors.email }}</span>
        </div>

        <div class="field">
          <label>Nomor WhatsApp <span class="optional">opsional</span></label>
          <div class="input-wrap">
            <svg class="iico" viewBox="0 0 20 20" fill="none">
              <path d="M13.5 12c-.3 0-1 .5-1.3.7a6.8 6.8 0 01-4.9-4.9C7.5 7.5 8 6.8 8 6.5c0-.2-.8-2-.9-2.2C7 4 6.8 4 6.5 4A3.5 3.5 0 004 7.5C4 11.6 8.4 16 12.5 16a3.5 3.5 0 003.5-2.5c0-.3 0-.5-.3-.7L13.5 12z" stroke="currentColor" stroke-width="1.4"/>
            </svg>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="Contoh: 670 76869949"
            />
          </div>
          <span class="field-tip">Format: 628xxx (tanpa + atau 0 di depan)</span>
        </div>

        <!-- Password -->
        <div class="field" :class="{ 'field--error': errors.password }">
          <label>Password</label>
          <div class="input-wrap">
            <svg class="iico" viewBox="0 0 20 20" fill="none">
              <rect x="4" y="9" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
              <path d="M7 9V6.5a3 3 0 016 0V9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <input
              v-model="form.password"
              :type="showPw ? 'text' : 'password'"
              placeholder="Min. 6 karakter"
              @blur="validate('password')"
            />
            <button type="button" class="eye-btn" @click="showPw = !showPw" tabindex="-1">
              <svg viewBox="0 0 20 20" fill="none">
                <path v-if="!showPw" d="M10 4C5.5 4 2 10 2 10s3.5 6 8 6 8-6 8-6-3.5-6-8-6z" stroke="currentColor" stroke-width="1.4"/>
                <circle v-if="!showPw" cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.4"/>
                <path v-if="showPw" d="M3 3l14 14M8.5 8.6A2.5 2.5 0 0012.4 12M6 6.1C4.3 7.2 3 9 3 10s3.5 6 8 6c1.5 0 2.9-.4 4-1.1M10 4c4.5 0 8 6 8 6s-.7 1.3-2 2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <!-- Password strength bar -->
          <div class="strength-bar" v-if="form.password">
            <div class="strength-fill" :class="strengthClass" :style="{ width: strengthWidth }"></div>
          </div>
          <span class="ferr" v-if="errors.password">{{ errors.password }}</span>
        </div>

        <!-- Konfirmasi Password -->
        <div class="field" :class="{ 'field--error': errors.password_confirmation }">
          <label>Konfirmasi Password</label>
          <div class="input-wrap">
            <svg class="iico" viewBox="0 0 20 20" fill="none">
              <path d="M5 10l4 4 6-6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <input
              v-model="form.password_confirmation"
              :type="showPw ? 'text' : 'password'"
              placeholder="Ulangi password"
              @blur="validate('password_confirmation')"
            />
          </div>
          <span class="ferr" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
        </div>

        <!-- Alert error dari server -->
        <div class="alert" v-if="errorMsg" role="alert">
          <svg viewBox="0 0 20 20" fill="none">
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.4"/>
            <path d="M10 6v4M10 13.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
          </svg>
          {{ errorMsg }}
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit" :disabled="loading">
          <span>{{ loading ? 'Mendaftar...' : 'Daftar Sekarang' }}</span>
          <span class="spinner" v-if="loading"></span>
          <svg v-else viewBox="0 0 20 20" fill="none" style="width:16px;height:16px">
            <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

      </form>

      <p class="footer-link">
        Sudah punya akun?
        <router-link to="/login">Masuk di sini</router-link>
      </p>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../store/auth.js'

const router  = useRouter()
const auth    = useAuthStore()
const loading = ref(false)
const errorMsg = ref('')
const showPw  = ref(false)

const form = reactive({
  name:                  '',
  email:                 '',
  phone:                '',
  password:              '',
  password_confirmation: '',
  role:                  'user', // default pencari kos
})

const errors = reactive({
  name: '', email: '', password: '', password_confirmation: '',
})

// Password strength
const strengthLevel = computed(() => {
  const p = form.password
  if (!p) return 0
  let score = 0
  if (p.length >= 6)  score++
  if (p.length >= 10) score++
  if (/[A-Z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  return score
})
const strengthWidth = computed(() => {
  return ['0%', '20%', '40%', '60%', '80%', '100%'][strengthLevel.value]
})
const strengthClass = computed(() => {
  return ['', 'weak', 'weak', 'medium', 'strong', 'strong'][strengthLevel.value]
})

// Validasi per field
const validate = (field) => {
  if (field === 'name') {
    errors.name = !form.name ? 'Nama tidak boleh kosong' : ''
  }
  if (field === 'email') {
    if (!form.email) errors.email = 'Email tidak boleh kosong'
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errors.email = 'Format email tidak valid'
    else errors.email = ''
  }
  if (field === 'password') {
    errors.password = form.password.length < 6 ? 'Password minimal 6 karakter' : ''
  }
  if (field === 'password_confirmation') {
    errors.password_confirmation = form.password_confirmation !== form.password
      ? 'Password tidak cocok' : ''
  }
}

const handleRegister = async () => {
  // Validasi semua field
  validate('name')
  validate('email')
  validate('password')
  validate('password_confirmation')
  if (Object.values(errors).some(e => e)) return

  loading.value  = true
  errorMsg.value = ''
    try {
    const res = await api.post('/register', form)

    // ✅ Simpan email untuk halaman resend
    localStorage.setItem('pending_email', form.email)

    // ✅ Redirect ke halaman cek email, BUKAN auto login
    router.push('/verify-email')

  } catch (err) {
    const serverErrors = err.response?.data?.errors
    if (serverErrors) {
      // Tampilkan error validasi dari Laravel per field
      errors.name     = serverErrors.name?.[0]     || ''
      errors.email    = serverErrors.email?.[0]    || ''
      errors.password = serverErrors.password?.[0] || ''
    } else {
      errorMsg.value = err.response?.data?.message || 'Registrasi gagal. Coba lagi.'
    }
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
  padding: 2rem 1.5rem;
}

.card {
  width: 100%;
  max-width: 440px;
  background: #16181d;
  border: 1px solid #2a2d35;
  border-radius: 20px;
  padding: 2.25rem 2rem;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 1.75rem;
}
.brand-icon { font-size: 20px; color: #d4a055; }
.brand-name { font-size: 15px; font-weight: 600; color: #f0ebe3; letter-spacing: .02em; }

.heading { margin-bottom: 1.75rem; }
.heading h1 { font-size: 21px; font-weight: 600; color: #f0ebe3; letter-spacing: -.02em; margin-bottom: 5px; }
.heading p  { font-size: 13px; color: #6b7280; font-weight: 300; }

/* Role switch */
.role-switch {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 1.25rem;
}
.role-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 14px 10px;
  background: #1e2028;
  border: 1.5px solid #2a2d35;
  border-radius: 12px;
  cursor: pointer;
  transition: border-color .2s, background .2s;
  font-family: 'Sora', sans-serif;
  text-align: center;
}
.role-btn:hover { border-color: #d4a055; }
.role-btn.active { border-color: #d4a055; background: rgba(212,160,85,.08); }
.role-icon  { font-size: 1.6rem; }
.role-label { font-size: 13px; font-weight: 500; color: #f0ebe3; }
.role-desc  { font-size: 11px; color: #6b7280; font-weight: 300; line-height: 1.4; }

/* Form fields */
form { display: flex; flex-direction: column; gap: 1rem; }

.field { display: flex; flex-direction: column; gap: 5px; }
.field label { font-size: 11.5px; font-weight: 500; color: #9ca3af; letter-spacing: .04em; text-transform: uppercase; }
.field-tip { font-size: 11px; color: #c4b8a8; font-weight: 300; }
.input-wrap { position: relative; display: flex; align-items: center; }
.iico { position: absolute; left: 13px; width: 15px; height: 15px; color: #4b5563; pointer-events: none; }

.input-wrap input {
  width: 100%;
  height: 44px;
  background: #1e2028;
  border: 1px solid #2a2d35;
  border-radius: 10px;
  color: #f0ebe3;
  font-family: 'Sora', sans-serif;
  font-size: 13.5px;
  font-weight: 300;
  padding: 0 40px;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.input-wrap input::placeholder { color: #3d4148; }
.input-wrap input:focus { border-color: #d4a055; box-shadow: 0 0 0 3px rgba(212,160,85,.12); }
.field--error .input-wrap input { border-color: #7f1d1d; }

.eye-btn {
  position: absolute; right: 11px;
  background: none; border: none; cursor: pointer;
  color: #4b5563; padding: 4px;
  display: flex; align-items: center;
  transition: color .15s;
}
.eye-btn:hover { color: #9ca3af; }
.eye-btn svg { width: 15px; height: 15px; }

/* Strength bar */
.strength-bar {
  height: 3px;
  background: #2a2d35;
  border-radius: 2px;
  overflow: hidden;
  margin-top: 2px;
}
.strength-fill { height: 100%; border-radius: 2px; transition: width .3s, background .3s; }
.strength-fill.weak   { background: #ef4444; }
.strength-fill.medium { background: #f59e0b; }
.strength-fill.strong { background: #10b981; }

.ferr { font-size: 11.5px; color: #f87171; font-weight: 300; }

/* Alert */
.alert {
  display: flex; align-items: center; gap: 9px;
  background: rgba(127,29,29,.2);
  border: 1px solid rgba(239,68,68,.25);
  border-radius: 10px; padding: 10px 13px;
  font-size: 13px; color: #fca5a5; font-weight: 300;
}
.alert svg { width: 15px; height: 15px; flex-shrink: 0; }

/* Submit */
.btn-submit {
  height: 46px;
  background: #d4a055;
  border: none; border-radius: 10px;
  color: #0e0f11;
  font-family: 'Sora', sans-serif;
  font-size: 14px; font-weight: 600;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  margin-top: .25rem;
  transition: background .2s, transform .1s;
}
.btn-submit:hover:not(:disabled) { background: #c49040; }
.btn-submit:active:not(:disabled) { transform: scale(.98); }
.btn-submit:disabled { opacity: .55; cursor: not-allowed; }

.spinner {
  width: 15px; height: 15px;
  border: 2px solid rgba(14,15,17,.3);
  border-top-color: #0e0f11;
  border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.footer-link {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 13px;
  color: #4b5563;
  font-weight: 300;
}
.footer-link a { color: #d4a055; text-decoration: none; font-weight: 500; margin-left: 4px; }
.footer-link a:hover { color: #e8b870; }
</style>