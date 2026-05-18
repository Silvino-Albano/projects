<template>
  <div class="page">
    <div class="card">
      <div class="brand">
        <span class="brand-icon">⌂</span>
        <span class="brand-name">KosDili</span>
      </div>

      <!-- Sukses dari link email -->
      <template v-if="status === 'success'">
        <div class="icon-wrap success">✅</div>
        <h1>Email Terverifikasi!</h1>
        <p>Akun Anda sudah aktif. Silakan login untuk mulai menggunakan KosDili.</p>
        <router-link to="/login" class="btn-primary">Masuk Sekarang</router-link>
      </template>

      <!-- Sudah pernah verifikasi -->
      <template v-else-if="status === 'already'">
        <div class="icon-wrap info">ℹ️</div>
        <h1>Sudah Terverifikasi</h1>
        <p>Email Anda sudah terverifikasi sebelumnya. Silakan login.</p>
        <router-link to="/login" class="btn-primary">Masuk</router-link>
      </template>

      <!-- Belum verifikasi — tampil setelah register -->
      <template v-else>
        <div class="icon-wrap pending">📧</div>
        <h1>Cek Email Anda</h1>
        <p>Kami telah mengirim link verifikasi ke <strong>{{ email }}</strong>.</p>
        <p class="sub">Klik link di email tersebut untuk mengaktifkan akun Anda.</p>

        <div class="resend-wrap">
          <p>Tidak menerima email?</p>
          <button
            class="btn-resend"
            :disabled="resendLoading || countdown > 0"
            @click="resendEmail"
          >
            {{ countdown > 0 ? `Kirim ulang (${countdown}s)` : 'Kirim Ulang Email' }}
          </button>
        </div>

        <p class="tip">💡 Cek folder spam/junk jika tidak ada di inbox.</p>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

const route        = useRoute()
const status       = ref(route.query.verified || '')
const email        = ref(localStorage.getItem('pending_email') || '')
const resendLoading = ref(false)
const countdown    = ref(0)
let timer = null

const startCountdown = () => {
  countdown.value = 60
  timer = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) clearInterval(timer)
  }, 1000)
}

const resendEmail = async () => {
  if (!email.value) return
  resendLoading.value = true
  try {
    await api.post('/resend-verification', { email: email.value })
    startCountdown()
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal kirim ulang.')
  } finally {
    resendLoading.value = false
  }
}

onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.page {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  background: #0e0f11;
  background-image: radial-gradient(ellipse 60% 50% at 20% 10%, #1a1f2e 0%, transparent 60%);
  font-family: 'Sora', sans-serif; padding: 2rem;
}
.card {
  width: 100%; max-width: 420px; background: #16181d;
  border: 1px solid #2a2d35; border-radius: 20px;
  padding: 2.5rem 2rem; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.brand { display: flex; align-items: center; gap: 8px; margin-bottom: .5rem; }
.brand-icon { font-size: 20px; color: #d4a055; }
.brand-name { font-size: 15px; font-weight: 600; color: #f0ebe3; }

.icon-wrap { font-size: 3rem; margin: .5rem 0; }
h1 { font-size: 20px; font-weight: 600; color: #f0ebe3; }
p  { font-size: 13.5px; color: #6b7280; font-weight: 300; line-height: 1.6; }
p strong { color: #f0ebe3; }
.sub { font-size: 13px; }

.btn-primary {
  display: inline-block; margin-top: .5rem;
  padding: 10px 28px; background: #d4a055; color: #0e0f11;
  border-radius: 10px; font-size: 14px; font-weight: 600;
  text-decoration: none; transition: background .2s;
}
.btn-primary:hover { background: #c49040; }

.resend-wrap { display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: .5rem; }
.resend-wrap p { font-size: 12.5px; color: #4b5563; }
.btn-resend {
  padding: 8px 20px; background: none; border: 1px solid #2a2d35;
  border-radius: 8px; color: #d4a055; font-family: 'Sora', sans-serif;
  font-size: 13px; cursor: pointer; transition: border-color .2s;
}
.btn-resend:hover:not(:disabled) { border-color: #d4a055; }
.btn-resend:disabled { opacity: .4; cursor: not-allowed; }

.tip { font-size: 12px; color: #3d4148; margin-top: .25rem; }
</style>