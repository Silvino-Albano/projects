<template>
  <div class="page">

    <nav class="navbar">
      <div class="nav-brand">
        <router-link to="/" class="nav-brand-link">
          <span class="nav-icon">⌂</span>
          <span class="nav-title">KosDili</span>
        </router-link>
      </div>
      <div class="nav-actions">
        <router-link to="/admin/kos" class="btn-outline">Panel Admin</router-link>
        <button class="btn-logout" @click="handleLogout">Keluar</button>
      </div>
    </nav>

    <!-- HEADER -->
    <section class="page-header">
      <div class="header-inner">
        <p class="eyebrow">Admin Platform</p>
        <h1 class="page-title">Pendapatan <em>Komisi</em></h1>
        <p class="page-sub">Pantau semua komisi dari pemilik kos di seluruh Dili.</p>
      </div>
      <div class="deco">
        <div class="dc c1"></div>
        <div class="dc c2"></div>
      </div>
    </section>

    <!-- STATS BESAR -->
    <div class="stats-big" v-if="statistik">
      <div class="sb-card primary">
        <div class="sb-icon">💰</div>
        <div class="sb-val">${{ Number(statistik.total_komisi_semua).toFixed(2) }}</div>
        <div class="sb-label">Total Komisi</div>
      </div>
      <div class="sb-card green">
        <div class="sb-icon">✅</div>
        <div class="sb-val">${{ Number(statistik.total_sudah_bayar).toFixed(2) }}</div>
        <div class="sb-label">Sudah Diterima</div>
      </div>
      <div class="sb-card amber">
        <div class="sb-icon">⏳</div>
        <div class="sb-val">${{ Number(statistik.total_belum_bayar).toFixed(2) }}</div>
        <div class="sb-label">Belum Diterima</div>
      </div>
      <div class="sb-card blue">
        <div class="sb-icon">🏠</div>
        <div class="sb-val">{{ statistik.jumlah_owner_aktif }}</div>
        <div class="sb-label">Owner Aktif</div>
      </div>
      <div class="sb-card gray">
        <div class="sb-icon">📋</div>
        <div class="sb-val">{{ statistik.jumlah_transaksi }}</div>
        <div class="sb-label">Total Transaksi</div>
      </div>
    </div>

    <!-- FILTER -->
    <div class="filter-bar">
      <button
        v-for="f in filters" :key="f.value"
        class="filter-btn"
        :class="{ active: filterStatus === f.value }"
        @click="filterStatus = f.value"
      >
        {{ f.label }}
      </button>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="state-wrap">
      <div class="spinner"></div>
      <p>Memuat data komisi...</p>
    </div>

    <!-- KOSONG -->
    <div v-else-if="!filteredKomisi.length" class="state-wrap">
      <div class="empty-icon">📭</div>
      <p class="empty-text">Tidak ada data komisi.</p>
    </div>

    <!-- TABEL KOMISI -->
    <div v-else class="komisi-wrap">
      <div class="komisi-table">

        <div class="kt-head">
          <span>Owner / Kos</span>
          <span>Booking</span>
          <span>Total Sewa</span>
          <span>Komisi</span>
          <span>Status</span>
          <span>Aksi</span>
        </div>

        <div
          v-for="k in filteredKomisi"
          :key="k.id"
          class="kt-row"
          :class="{ paid: k.status === 'paid' }"
        >
          <!-- Owner & Kos -->
          <div class="kt-cell">
            <div class="owner-name">{{ k.owner?.name ?? '-' }}</div>
            <div class="owner-email">{{ k.owner?.email ?? '-' }}</div>
            <div class="kos-name">{{ k.booking?.kamar?.kos?.nama_kos ?? '-' }}</div>
          </div>

          <!-- Booking -->
          <div class="kt-cell">
            <div class="booking-dates">{{ formatDate(k.booking?.tanggal_masuk) }}</div>
            <div class="booking-dur">{{ k.booking?.durasi_bulan }} bulan</div>
          </div>

          <!-- Total Sewa -->
          <div class="kt-cell">
            <div class="amt">${{ Number(k.total_sewa).toFixed(2) }}</div>
          </div>

          <!-- Komisi -->
          <div class="kt-cell">
            <div class="amt komisi">${{ Number(k.jumlah).toFixed(2) }}</div>
            <div class="persen-label">{{ k.persen }}%</div>
          </div>

          <!-- Status -->
          <div class="kt-cell">
            <span class="status-badge" :class="k.status">
              {{ k.status === 'paid' ? '✅ Lunas' : '⏳ Pending' }}
            </span>
            <div v-if="k.bukti_bayar" class="has-bukti">
              <a :href="'/storage/' + k.bukti_bayar" target="_blank" class="bukti-link">
                Lihat bukti →
              </a>
            </div>
          </div>

          <!-- Aksi -->
          <div class="kt-cell">
            <button
              v-if="k.status === 'unpaid'"
              class="btn-konfirmasi"
              :disabled="konfirmasiLoading[k.id]"
              @click="konfirmasiKomisi(k.id)"
            >
              {{ konfirmasiLoading[k.id] ? 'Memproses...' : 'Konfirmasi' }}
            </button>
            <span v-else class="confirmed-label">
              {{ formatDate(k.dibayar_pada) }}
            </span>
          </div>
        </div>

      </div>
    </div>

    <!-- TOAST -->
    <transition name="toast">
      <div v-if="toast.show" class="toast" :class="toast.type">
        {{ toast.message }}
      </div>
    </transition>

    <footer class="footer">
      <p>© {{ year }} KosDili · Dili, Timor-Leste</p>
    </footer>

  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth.js'
import api from '../services/api'

export default {
  name: 'AdminKomisi',

  setup() {
    const router    = useRouter()
    const authStore = useAuthStore()

    const komisiList        = ref([])
    const statistik         = ref(null)
    const loading           = ref(true)
    const filterStatus      = ref('all')
    const konfirmasiLoading = reactive({})
    const toast             = reactive({ show: false, message: '', type: 'success' })
    const year              = new Date().getFullYear()

    const filters = [
      { value: 'all',    label: '📋 Semua' },
      { value: 'unpaid', label: '⏳ Belum Bayar' },
      { value: 'paid',   label: '✅ Lunas' },
    ]

    const filteredKomisi = computed(() => {
      if (filterStatus.value === 'all') return komisiList.value
      return komisiList.value.filter(k => k.status === filterStatus.value)
    })

    const showToast = (message, type = 'success') => {
      toast.message = message
      toast.type    = type
      toast.show    = true
      setTimeout(() => { toast.show = false }, 3500)
    }

    const fetchKomisi = async () => {
      loading.value = true
      try {
        const res        = await api.get('/admin/komisi')
        komisiList.value = res.data.data
        statistik.value  = res.data.statistik
      } catch {
        showToast('Gagal memuat data komisi.', 'error')
      } finally {
        loading.value = false
      }
    }

    const konfirmasiKomisi = async (id) => {
      konfirmasiLoading[id] = true
      try {
        await api.patch(`/admin/komisi/${id}/konfirmasi`, {
          catatan_admin: 'Pembayaran dikonfirmasi oleh admin.',
        })
        showToast('Komisi berhasil dikonfirmasi! ✅')
        await fetchKomisi()
      } catch (e) {
        showToast(e.response?.data?.message || 'Gagal konfirmasi.', 'error')
      } finally {
        konfirmasiLoading[id] = false
      }
    }

    const formatDate = (d) => {
      if (!d) return '-'
      return new Date(d).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
      })
    }

    const handleLogout = async () => {
      await authStore.logout()
      router.push('/login')
    }

    onMounted(fetchKomisi)

    return {
      komisiList, statistik, loading,
      filterStatus, filteredKomisi, filters,
      konfirmasiLoading, toast, year,
      konfirmasiKomisi, formatDate, handleLogout,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
.page { min-height: 100vh; background: #faf8f4; font-family: 'DM Sans', sans-serif; color: #1a1a1a; }

/* NAVBAR */
.navbar {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 2.5rem; background: #faf8f4;
  border-bottom: 1px solid #e8e2d9; position: sticky; top: 0; z-index: 100;
}
.nav-brand-link { display: flex; align-items: center; gap: 8px; text-decoration: none; }
.nav-icon  { font-size: 20px; }
.nav-title { font-family: 'Playfair Display', serif; font-size: 18px; color: #1a1a1a; }
.nav-actions { display: flex; gap: 10px; align-items: center; }
.btn-outline {
  padding: 7px 16px; border: 1px solid #d4c5b0; border-radius: 8px;
  font-size: 13px; color: #6b5b47; text-decoration: none;
}
.btn-logout {
  background: none; border: 1px solid #d4c5b0; color: #6b5b47;
  padding: 7px 14px; border-radius: 8px; font-size: 13px; cursor: pointer;
  font-family: 'DM Sans', sans-serif;
}

/* HEADER */
.page-header {
  position: relative; overflow: hidden;
  background: #1e1a14; padding: 3rem 2.5rem 2.5rem; color: #faf8f4;
}
.header-inner { position: relative; z-index: 2; max-width: 1100px; margin: 0 auto; }
.eyebrow {
  font-size: 11px; letter-spacing: .14em; text-transform: uppercase;
  color: #c17f3e; font-weight: 500; margin-bottom: .6rem;
}
.page-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 4vw, 2.5rem); line-height: 1.15; margin-bottom: .5rem;
}
.page-title em { color: #c17f3e; font-style: italic; }
.page-sub { font-size: 14px; color: #b5a898; font-weight: 300; }
.deco { position: absolute; inset: 0; z-index: 1; }
.dc   { position: absolute; border-radius: 50%; opacity: .07; background: #c17f3e; }
.c1   { width: 300px; height: 300px; right: -50px; top: -80px; }
.c2   { width: 140px; height: 140px; right: 200px; bottom: -40px; }

/* STATS */
.stats-big {
  display: flex; gap: 12px; padding: 1.5rem 2.5rem;
  flex-wrap: wrap; background: #fff; border-bottom: 1px solid #e8e2d9;
}
.sb-card {
  flex: 1; min-width: 130px; border-radius: 12px;
  padding: 14px 16px; border: 1px solid #e8e2d9; text-align: center;
}
.sb-card.primary { background: #fdf5ec; border-color: #f0d9bb; }
.sb-card.green   { background: #f0fdf4; border-color: #a7f3d0; }
.sb-card.amber   { background: #fffbeb; border-color: #fde68a; }
.sb-card.blue    { background: #eff6ff; border-color: #bfdbfe; }
.sb-card.gray    { background: #f9fafb; border-color: #e5e7eb; }
.sb-icon  { font-size: 1.5rem; margin-bottom: 5px; }
.sb-val   { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #1a1a1a; font-weight: 600; }
.sb-label { font-size: 10px; color: #8a7968; text-transform: uppercase; letter-spacing: .06em; margin-top: 3px; }

/* FILTER */
.filter-bar {
  display: flex; gap: 8px; flex-wrap: wrap;
  padding: 1rem 2.5rem; border-bottom: 1px solid #e8e2d9; background: #fff;
}
.filter-btn {
  padding: 6px 14px; border-radius: 20px; border: 1px solid #e8e2d9;
  background: #fff; font-family: 'DM Sans', sans-serif; font-size: 12.5px;
  color: #6b5b47; cursor: pointer; transition: all .15s;
}
.filter-btn.active { background: #1e1a14; color: #faf8f4; border-color: #1e1a14; }

/* STATE */
.state-wrap {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 4rem 2rem; gap: .75rem; color: #8a7968;
}
.spinner {
  width: 36px; height: 36px; border: 3px solid #e8e2d9;
  border-top-color: #c17f3e; border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.empty-icon { font-size: 3rem; }
.empty-text { font-size: 15px; }

/* TABLE */
.komisi-wrap { max-width: 1100px; margin: 0 auto; padding: 2rem 2.5rem; }
.komisi-table {
  background: #fff; border-radius: 14px;
  border: 1px solid #e8e2d9; overflow: hidden;
}
.kt-head {
  display: grid;
  grid-template-columns: 2fr 1.2fr 1fr 1fr 1.2fr 1.2fr;
  gap: 12px; padding: 12px 16px;
  background: #f7f5f2;
  font-size: 11px; font-weight: 600; color: #8a7968;
  text-transform: uppercase; letter-spacing: .06em;
  border-bottom: 1px solid #e8e2d9;
}
.kt-row {
  display: grid;
  grid-template-columns: 2fr 1.2fr 1fr 1fr 1.2fr 1.2fr;
  gap: 12px; padding: 14px 16px;
  border-bottom: 1px solid #f5f0eb;
  align-items: center; transition: background .12s;
}
.kt-row:last-child { border: none; }
.kt-row:hover { background: #faf8f4; }
.kt-row.paid  { background: #f9fef9; }

.kt-cell       { display: flex; flex-direction: column; gap: 2px; }
.owner-name    { font-size: 13px; font-weight: 500; color: #1a1a1a; }
.owner-email   { font-size: 11px; color: #8a7968; }
.kos-name      { font-size: 11px; color: #c17f3e; margin-top: 2px; }
.booking-dates { font-size: 12.5px; color: #1a1a1a; }
.booking-dur   { font-size: 11px; color: #8a7968; }
.amt           { font-size: 13.5px; font-weight: 500; color: #1a1a1a; }
.amt.komisi    { color: #c17f3e; font-family: 'Playfair Display', serif; font-size: 15px; font-weight: 700; }
.persen-label  { font-size: 10px; color: #8a7968; }

.status-badge  { font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; align-self: flex-start; }
.status-badge.paid   { background: #d1fae5; color: #065f46; }
.status-badge.unpaid { background: #fef3c7; color: #92400e; }
.has-bukti     { margin-top: 4px; }
.bukti-link    { font-size: 11px; color: #c17f3e; text-decoration: none; }

.btn-konfirmasi {
  background: #059669; color: #fff; border: none;
  padding: 7px 14px; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 500;
  cursor: pointer; transition: background .15s;
}
.btn-konfirmasi:hover:not(:disabled) { background: #047857; }
.btn-konfirmasi:disabled { opacity: .6; cursor: not-allowed; }
.confirmed-label { font-size: 11.5px; color: #059669; }

/* TOAST */
.toast {
  position: fixed; bottom: 1.5rem; right: 1.5rem;
  padding: 12px 20px; border-radius: 10px;
  font-size: 14px; font-weight: 500; z-index: 300;
  box-shadow: 0 4px 20px rgba(0,0,0,.15);
}
.toast.success { background: #1a1a1a; color: #faf8f4; }
.toast.error   { background: #dc2626; color: #fff; }
.toast-enter-active, .toast-leave-active { transition: all .3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(12px); }

/* FOOTER */
.footer {
  text-align: center; padding: 2rem; font-size: 13px;
  color: #b5a898; border-top: 1px solid #e8e2d9; margin-top: 2rem;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .navbar, .stats-big, .filter-bar, .komisi-wrap { padding-left: 1.25rem; padding-right: 1.25rem; }
  .page-header { padding: 2rem 1.25rem; }
  .kt-head, .kt-row { grid-template-columns: 1.5fr 1fr 1fr; }
  .kt-head span:nth-child(n+4),
  .kt-row .kt-cell:nth-child(n+4) { display: none; }
}
</style>
