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
        <h1 class="page-title">Manajemen <em>Langganan</em></h1>
        <p class="page-sub">Pantau subscriber aktif dan konfirmasi pembayaran paket.</p>
      </div>
      <div class="deco"><div class="dc c1"></div><div class="dc c2"></div></div>
    </section>

    <!-- STATS -->
    <div class="stats-wrap" v-if="statistik">
      <div class="stat-card primary">
        <div class="sc-icon">👥</div>
        <div class="sc-val">{{ statistik.total_subscriber }}</div>
        <div class="sc-label">Subscriber Aktif</div>
      </div>
      <div class="stat-card green">
        <div class="sc-icon">💰</div>
        <div class="sc-val">${{ Number(statistik.total_pendapatan).toFixed(2) }}</div>
        <div class="sc-label">Total Pendapatan</div>
      </div>
      <div class="stat-card amber">
        <div class="sc-icon">⏳</div>
        <div class="sc-val">{{ statistik.total_belum_bayar }}</div>
        <div class="sc-label">Belum Dikonfirmasi</div>
      </div>
      <template v-if="statistik.per_plan">
        <div
          v-for="p in statistik.per_plan"
          :key="p.nama"
          class="stat-card gray"
        >
          <div class="sc-icon">📦</div>
          <div class="sc-val">{{ p.count }}</div>
          <div class="sc-label">Paket {{ p.nama }}</div>
        </div>
      </template>
    </div>

    <!-- FILTER -->
    <div class="filter-bar">
      <div class="filter-group">
        <button
          v-for="f in statusFilters" :key="f.value"
          class="filter-btn"
          :class="{ active: filterStatus === f.value }"
          @click="filterStatus = f.value"
        >{{ f.label }}</button>
      </div>
      <div class="filter-group">
        <button
          v-for="f in paymentFilters" :key="f.value"
          class="filter-btn"
          :class="{ active: filterPayment === f.value }"
          @click="filterPayment = f.value"
        >{{ f.label }}</button>
      </div>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="state-wrap">
      <div class="spinner"></div><p>Memuat data langganan...</p>
    </div>

    <!-- TABEL -->
    <div v-else class="table-wrap">

      <div v-if="!filteredSubs.length" class="state-wrap">
        <div class="empty-icon">📭</div>
        <p class="empty-text">Tidak ada data langganan.</p>
      </div>

      <div v-else class="sub-table">
        <div class="st-head">
          <span>Owner</span>
          <span>Paket</span>
          <span>Periode</span>
          <span>Bayar</span>
          <span>Status</span>
          <span>Aksi</span>
        </div>

        <div
          v-for="s in filteredSubs"
          :key="s.id"
          class="st-row"
          :class="{ paid: s.payment_status === 'paid' }"
        >
          <!-- Owner -->
          <div class="st-cell">
            <div class="owner-name">{{ s.user?.name ?? '-' }}</div>
            <div class="owner-email">{{ s.user?.email ?? '-' }}</div>
          </div>

          <!-- Paket -->
          <div class="st-cell">
            <span class="plan-badge" :class="s.plan?.slug">{{ s.plan?.nama }}</span>
            <div class="plan-harga">${{ Number(s.plan?.harga ?? 0).toFixed(2) }}/bln</div>
          </div>

          <!-- Periode -->
          <div class="st-cell">
            <div class="period-date">{{ formatDate(s.mulai_pada) }}</div>
            <div class="period-sep">→</div>
            <div class="period-date">{{ formatDate(s.berakhir_pada) }}</div>
          </div>

          <!-- Status bayar -->
          <div class="st-cell">
            <span class="pay-badge" :class="s.payment_status">
              {{ s.payment_status === 'paid' ? '✅ Lunas' : '⏳ Belum' }}
            </span>
            <div v-if="s.metode_bayar" class="metode-label">{{ s.metode_bayar }}</div>
            <a
              v-if="s.bukti_bayar"
              :href="'/storage/' + s.bukti_bayar"
              target="_blank"
              class="bukti-link"
            >Lihat bukti →</a>
          </div>

          <!-- Status langganan -->
          <div class="st-cell">
            <span class="sub-status" :class="s.status">
              {{ s.status === 'active' ? '🟢 Aktif'
               : s.status === 'expired' ? '⚫ Berakhir'
               : '🔴 Dibatalkan' }}
            </span>
            <div v-if="s.dikonfirmasi_pada" class="konfirmasi-date">
              Dikonfirmasi: {{ formatDate(s.dikonfirmasi_pada) }}
            </div>
          </div>

          <!-- Aksi -->
          <div class="st-cell">
            <button
              v-if="s.payment_status === 'unpaid' && s.status === 'active'"
              class="btn-konfirmasi"
              :disabled="konfLoading[s.id]"
              @click="konfirmasi(s.id)"
            >
              {{ konfLoading[s.id] ? '...' : 'Konfirmasi' }}
            </button>
            <span v-else-if="s.payment_status === 'paid'" class="sudah-konfirmasi">
              ✓ Dikonfirmasi
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- TOAST -->
    <transition name="toast">
      <div v-if="toast.show" class="toast" :class="toast.type">{{ toast.message }}</div>
    </transition>

    <footer class="footer"><p>© {{ year }} KosDili · Dili, Timor-Leste</p></footer>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth.js'
import api from '../services/api'

export default {
  name: 'AdminSubscription',
  setup() {
    const router    = useRouter()
    const authStore = useAuthStore()

    const subscriptions = ref([])
    const statistik     = ref(null)
    const loading       = ref(true)
    const filterStatus  = ref('all')
    const filterPayment = ref('all')
    const konfLoading   = reactive({})
    const toast         = reactive({ show: false, message: '', type: 'success' })
    const year          = new Date().getFullYear()

    const statusFilters  = [
      { value: 'all',       label: '📋 Semua' },
      { value: 'active',    label: '🟢 Aktif' },
      { value: 'expired',   label: '⚫ Berakhir' },
      { value: 'cancelled', label: '🔴 Dibatalkan' },
    ]

    const paymentFilters = [
      { value: 'all',    label: '💳 Semua Bayar' },
      { value: 'unpaid', label: '⏳ Belum Bayar' },
      { value: 'paid',   label: '✅ Sudah Bayar' },
    ]

    const filteredSubs = computed(() => {
      return subscriptions.value.filter(s => {
        const matchStatus  = filterStatus.value  === 'all' || s.status          === filterStatus.value
        const matchPayment = filterPayment.value === 'all' || s.payment_status  === filterPayment.value
        return matchStatus && matchPayment
      })
    })

    const showToast = (message, type = 'success') => {
      toast.message = message; toast.type = type; toast.show = true
      setTimeout(() => { toast.show = false }, 3500)
    }

    const fetchData = async () => {
      loading.value = true
      try {
        const res           = await api.get('/admin/subscriptions')
        subscriptions.value = res.data.data
        statistik.value     = res.data.statistik
      } catch {
        showToast('Gagal memuat data.', 'error')
      } finally {
        loading.value = false
      }
    }

    const konfirmasi = async (id) => {
      konfLoading[id] = true
      try {
        await api.patch(`/admin/subscriptions/${id}/konfirmasi`)
        showToast('Pembayaran berhasil dikonfirmasi! ✅')
        await fetchData()
      } catch (e) {
        showToast(e.response?.data?.message || 'Gagal konfirmasi.', 'error')
      } finally {
        konfLoading[id] = false
      }
    }

    const formatDate = (d) => {
      if (!d) return '-'
      return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    }

    const handleLogout = async () => { await authStore.logout(); router.push('/login') }

    onMounted(fetchData)

    return {
      subscriptions, statistik, loading,
      filterStatus, filterPayment, filteredSubs,
      statusFilters, paymentFilters,
      konfLoading, toast, year,
      konfirmasi, formatDate, handleLogout,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap');
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.page{min-height:100vh;background:#faf8f4;font-family:'DM Sans',sans-serif;color:#1a1a1a;}
.navbar{display:flex;justify-content:space-between;align-items:center;padding:1rem 2.5rem;background:#faf8f4;border-bottom:1px solid #e8e2d9;position:sticky;top:0;z-index:100;}
.nav-brand-link{display:flex;align-items:center;gap:8px;text-decoration:none;}
.nav-icon{font-size:20px;}.nav-title{font-family:'Playfair Display',serif;font-size:18px;color:#1a1a1a;}
.nav-actions{display:flex;gap:10px;align-items:center;}
.btn-outline{padding:7px 16px;border:1px solid #d4c5b0;border-radius:8px;font-size:13px;color:#6b5b47;text-decoration:none;}
.btn-logout{background:none;border:1px solid #d4c5b0;color:#6b5b47;padding:7px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;}
.page-header{position:relative;overflow:hidden;background:#1e1a14;padding:3rem 2.5rem 2.5rem;color:#faf8f4;}
.header-inner{position:relative;z-index:2;max-width:1100px;margin:0 auto;}
.eyebrow{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#c17f3e;font-weight:500;margin-bottom:.6rem;}
.page-title{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.5rem);line-height:1.15;margin-bottom:.5rem;}
.page-title em{color:#c17f3e;font-style:italic;}
.page-sub{font-size:14px;color:#b5a898;font-weight:300;}
.deco{position:absolute;inset:0;z-index:1;}.dc{position:absolute;border-radius:50%;opacity:.07;background:#c17f3e;}
.c1{width:300px;height:300px;right:-50px;top:-80px;}.c2{width:140px;height:140px;right:200px;bottom:-40px;}

/* STATS */
.stats-wrap{display:flex;gap:12px;padding:1.5rem 2.5rem;background:#fff;border-bottom:1px solid #e8e2d9;flex-wrap:wrap;}
.stat-card{flex:1;min-width:130px;border-radius:12px;padding:14px 16px;border:1px solid #e8e2d9;text-align:center;}
.stat-card.primary{background:#fdf5ec;border-color:#f0d9bb;}
.stat-card.green{background:#f0fdf4;border-color:#a7f3d0;}
.stat-card.amber{background:#fffbeb;border-color:#fde68a;}
.stat-card.gray{background:#f9fafb;border-color:#e5e7eb;}
.sc-icon{font-size:1.5rem;margin-bottom:5px;}
.sc-val{font-family:'Playfair Display',serif;font-size:1.5rem;color:#1a1a1a;font-weight:600;}
.sc-label{font-size:10px;color:#8a7968;text-transform:uppercase;letter-spacing:.06em;margin-top:3px;}

/* FILTER */
.filter-bar{display:flex;gap:12px;padding:1rem 2.5rem;border-bottom:1px solid #e8e2d9;background:#fff;flex-wrap:wrap;}
.filter-group{display:flex;gap:6px;flex-wrap:wrap;}
.filter-btn{padding:6px 13px;border-radius:20px;border:1px solid #e8e2d9;background:#fff;font-family:'DM Sans',sans-serif;font-size:12.5px;color:#6b5b47;cursor:pointer;transition:all .15s;}
.filter-btn.active{background:#1e1a14;color:#faf8f4;border-color:#1e1a14;}

/* STATE */
.state-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 2rem;gap:.75rem;color:#8a7968;}
.spinner{width:36px;height:36px;border:3px solid #e8e2d9;border-top-color:#c17f3e;border-radius:50%;animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}
.empty-icon{font-size:3rem;}.empty-text{font-size:15px;}

/* TABLE */
.table-wrap{max-width:1100px;margin:0 auto;padding:2rem 2.5rem;}
.sub-table{background:#fff;border-radius:14px;border:1px solid #e8e2d9;overflow:hidden;}
.st-head{display:grid;grid-template-columns:1.8fr 1fr 1.3fr 1fr 1.2fr 1fr;gap:12px;padding:12px 16px;background:#f7f5f2;font-size:11px;font-weight:600;color:#8a7968;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e8e2d9;}
.st-row{display:grid;grid-template-columns:1.8fr 1fr 1.3fr 1fr 1.2fr 1fr;gap:12px;padding:14px 16px;border-bottom:1px solid #f5f0eb;align-items:center;transition:background .12s;}
.st-row:last-child{border:none;}
.st-row:hover{background:#faf8f4;}
.st-row.paid{background:#f9fef9;}
.st-cell{display:flex;flex-direction:column;gap:3px;}
.owner-name{font-size:13px;font-weight:500;color:#1a1a1a;}
.owner-email{font-size:11px;color:#8a7968;}
.plan-badge{font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;align-self:flex-start;}
.plan-badge.gratis{background:#f3f4f6;color:#374151;}
.plan-badge.pro{background:#EEEDFE;color:#3C3489;}
.plan-badge.bisnis{background:#FAEEDA;color:#854F0B;}
.plan-harga{font-size:11px;color:#8a7968;}
.period-date{font-size:12px;color:#1a1a1a;}
.period-sep{font-size:10px;color:#8a7968;}
.pay-badge{font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:20px;align-self:flex-start;}
.pay-badge.paid{background:#d1fae5;color:#065f46;}
.pay-badge.unpaid{background:#fef3c7;color:#92400e;}
.metode-label{font-size:10.5px;color:#8a7968;text-transform:capitalize;}
.bukti-link{font-size:11px;color:#c17f3e;text-decoration:none;}
.sub-status{font-size:11.5px;font-weight:500;color:#1a1a1a;}
.konfirmasi-date{font-size:10px;color:#8a7968;}
.btn-konfirmasi{background:#059669;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:12px;font-weight:500;cursor:pointer;transition:background .15s;}
.btn-konfirmasi:hover:not(:disabled){background:#047857;}
.btn-konfirmasi:disabled{opacity:.6;cursor:not-allowed;}
.sudah-konfirmasi{font-size:12px;color:#059669;font-weight:500;}

/* TOAST */
.toast{position:fixed;bottom:1.5rem;right:1.5rem;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;z-index:300;box-shadow:0 4px 20px rgba(0,0,0,.15);}
.toast.success{background:#1a1a1a;color:#faf8f4;}.toast.error{background:#dc2626;color:#fff;}
.toast-enter-active,.toast-leave-active{transition:all .3s ease;}
.toast-enter-from,.toast-leave-to{opacity:0;transform:translateY(12px);}
.footer{text-align:center;padding:2rem;font-size:13px;color:#b5a898;border-top:1px solid #e8e2d9;}

@media(max-width:768px){
  .navbar,.stats-wrap,.filter-bar,.table-wrap{padding-left:1.25rem;padding-right:1.25rem;}
  .page-header{padding:2rem 1.25rem;}
  .st-head,.st-row{grid-template-columns:1.5fr 1fr 1fr;}
  .st-head span:nth-child(n+4),.st-row .st-cell:nth-child(n+4){display:none;}
}
</style>
