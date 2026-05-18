<template>
  <div class="page">

    <nav class="navbar">
      <div class="nav-brand">
        <router-link to="/" class="nav-brand-link">
          <span>⌂</span>
          <span class="nav-title">KosDili</span>
        </router-link>
      </div>
      <button class="btn-logout" @click="handleLogout">Keluar</button>
    </nav>

    <div class="inner">
      <h1 class="page-title">Booking Masuk</h1>
      <p class="page-sub">Daftar penyewa yang booking kos Anda</p>

      <div v-if="loading" class="state-wrap">
        <div class="spinner"></div>
      </div>

      <div v-else-if="bookings.length === 0" class="state-wrap">
        <p>Belum ada booking masuk.</p>
      </div>

      <div v-else class="booking-list">
        <div v-for="b in bookings" :key="b.id" class="booking-card">

          <!-- Info penyewa -->
          <div class="card-left">
            <div class="avatar">{{ b.user?.name?.charAt(0).toUpperCase() }}</div>
            <div>
              <p class="penyewa-name">{{ b.user?.name }}</p>
              <p class="penyewa-email">{{ b.user?.email }}</p>
            </div>
          </div>

          <!-- Info kamar & tanggal -->
          <div class="card-mid">
            <p class="kamar-info">
              🏠 {{ b.kamar?.kos?.nama_kos }} — Kamar {{ b.kamar?.nomor_kamar }}
            </p>
            <p class="tanggal-info">
              📅 {{ b.tanggal_masuk }} → {{ b.tanggal_keluar }}
            </p>
          </div>

          <!-- Status & aksi -->
          <div class="card-right">
            <span class="status-badge" :class="b.status">{{ statusLabel(b.status) }}</span>

            <div v-if="b.status === 'pending'" class="action-btns">
              <button
                class="btn-approve"
                :disabled="actionLoading === b.id"
                @click="updateStatus(b.id, 'confirmed')"
              >✓ Terima</button>
              <button
                class="btn-reject"
                :disabled="actionLoading === b.id"
                @click="updateStatus(b.id, 'cancelled')"
              >✗ Tolak</button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <footer class="footer">© 2025 KosDili · Dili, Timor-Leste</footer>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../store/auth.js'

export default {
  setup() {
    const router        = useRouter()
    const authStore     = useAuthStore()
    const bookings      = ref([])
    const loading       = ref(true)
    const actionLoading = ref(null)

    const fetchBookings = async () => {
      loading.value = true
      try {
        const res = await api.get('/owner/bookings')
        bookings.value = res.data.data
      } catch (err) {
        console.error('Gagal load bookings:', err.response?.data)
      } finally {
        loading.value = false
      }
    }

    const updateStatus = async (id, status) => {
      actionLoading.value = id
      try {
        await api.put(`/owner/bookings/${id}/status`, { status })
        await fetchBookings()
      } catch (err) {
        alert(err.response?.data?.message || 'Gagal update status.')
      } finally {
        actionLoading.value = null
      }
    }

    const statusLabel = (status) => {
      const map = {
        pending:   'Menunggu',
        confirmed: 'Dikonfirmasi',
        cancelled: 'Dibatalkan',
      }
      return map[status] || status
    }

    const handleLogout = async () => {
      await authStore.logout()
      router.push('/login')
    }

    onMounted(fetchBookings)

    return {
      bookings,
      loading,
      actionLoading,
      updateStatus,
      handleLogout,
      statusLabel,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap');
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
.page { min-height: 100vh; background: #faf8f4; font-family: 'DM Sans', sans-serif; }

.navbar { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2.5rem; background: #faf8f4; border-bottom: 1px solid #e8e2d9; position: sticky; top: 0; z-index: 10; }
.nav-brand-link { display: flex; align-items: center; gap: 8px; text-decoration: none; color: #1a1a1a; }
.nav-title { font-family: 'Playfair Display', serif; font-size: 18px; }
.btn-logout { background: none; border: 1px solid #d4c5b0; color: #6b5b47; padding: 8px 16px; border-radius: 8px; font-size: 13.5px; cursor: pointer; font-family: 'DM Sans', sans-serif; }

.inner { max-width: 900px; margin: 0 auto; padding: 2.5rem 2rem; }
.page-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #1a1a1a; margin-bottom: 4px; }
.page-sub { font-size: 14px; color: #8a7968; margin-bottom: 2rem; font-weight: 300; }

.state-wrap { display: flex; justify-content: center; padding: 3rem; color: #8a7968; }
.spinner { width: 32px; height: 32px; border: 3px solid #e8e2d9; border-top-color: #c17f3e; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.booking-list { display: flex; flex-direction: column; gap: 1rem; }
.booking-card {
  background: #fff; border: 1px solid #e8e2d9; border-radius: 14px;
  padding: 1.25rem 1.5rem;
  display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;
}

.card-left { display: flex; align-items: center; gap: 12px; min-width: 180px; }
.avatar { width: 42px; height: 42px; border-radius: 50%; background: #1e1a14; color: #c17f3e; display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-size: 18px; flex-shrink: 0; }
.penyewa-name  { font-size: 14.5px; font-weight: 500; color: #1a1a1a; }
.penyewa-email { font-size: 12.5px; color: #8a7968; font-weight: 300; }

.card-mid { flex: 1; min-width: 200px; }
.kamar-info   { font-size: 13.5px; color: #1a1a1a; margin-bottom: 4px; }
.tanggal-info { font-size: 13px; color: #8a7968; font-weight: 300; }

.card-right { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.status-badge { font-size: 11.5px; font-weight: 500; padding: 4px 12px; border-radius: 20px; }
.status-badge.pending   { background: #fef3c7; color: #92400e; }
.status-badge.confirmed { background: #d1fae5; color: #065f46; }
.status-badge.cancelled { background: #fee2e2; color: #991b1b; }

.action-btns { display: flex; gap: 8px; }
.btn-approve { padding: 7px 16px; background: #1e1a14; color: #faf8f4; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background .2s; }
.btn-approve:hover { background: #c17f3e; }
.btn-reject  { padding: 7px 16px; background: none; border: 1px solid #fca5a5; color: #991b1b; border-radius: 8px; font-size: 13px; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background .2s; }
.btn-reject:hover { background: #fee2e2; }
.btn-approve:disabled, .btn-reject:disabled { opacity: .5; cursor: not-allowed; }

.footer { text-align: center; padding: 2rem; font-size: 13px; color: #b5a898; border-top: 1px solid #e8e2d9; margin-top: 2rem; }
</style>