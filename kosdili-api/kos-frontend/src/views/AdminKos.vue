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
        <router-link to="/admin/komisi" class="btn-outline">Komisi</router-link>
        <router-link to="/admin/subscriptions" class="btn-outline">Langganan</router-link>
        <button class="btn-logout" @click="handleLogout">Keluar</button>
      </div>
    </nav>

    <!-- HEADER -->
    <section class="page-header">
      <div class="header-inner">
        <p class="eyebrow">Admin Platform</p>
        <h1 class="page-title">Verifikasi <em>Kos</em></h1>
        <p class="page-sub">Tinjau dan setujui kos baru yang didaftarkan oleh pemilik.</p>
      </div>
      <div class="deco"><div class="dc c1"></div><div class="dc c2"></div></div>
    </section>

    <!-- STATS -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-num amber">{{ counts.pending }}</span>
        <span class="stat-label">Menunggu Review</span>
      </div>
      <div class="stat-div"></div>
      <div class="stat-item">
        <span class="stat-num green">{{ counts.aktif }}</span>
        <span class="stat-label">Aktif</span>
      </div>
      <div class="stat-div"></div>
      <div class="stat-item">
        <span class="stat-num red">{{ counts.nonaktif }}</span>
        <span class="stat-label">Ditolak</span>
      </div>
      <div class="stat-div"></div>
      <div class="stat-item">
        <span class="stat-num">{{ counts.total }}</span>
        <span class="stat-label">Total Kos</span>
      </div>
    </div>

    <!-- FILTER TAB -->
    <div class="tab-bar">
      <button
        v-for="tab in tabs" :key="tab.value"
        class="tab-btn"
        :class="{ active: activeTab === tab.value }"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
        <span v-if="tab.value === 'pending' && counts.pending > 0" class="tab-badge">
          {{ counts.pending }}
        </span>
      </button>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="state-wrap">
      <div class="spinner"></div>
      <p>Memuat data kos...</p>
    </div>

    <!-- KOSONG -->
    <div v-else-if="!filteredKos.length" class="state-wrap">
      <div class="empty-icon">
        {{ activeTab === 'pending' ? '✅' : '🏚️' }}
      </div>
      <p class="empty-text">
        {{ activeTab === 'pending' ? 'Tidak ada kos yang menunggu review.' : 'Tidak ada data.' }}
      </p>
    </div>

    <!-- DAFTAR KOS -->
    <div v-else class="kos-wrap">
      <div
        v-for="(kos, i) in filteredKos"
        :key="kos.id"
        class="kos-card"
        :style="{ animationDelay: i * 0.06 + 's' }"
        :class="kos.status"
      >
        <!-- Thumbnail -->
        <div
          class="kos-thumb"
          :style="kos.gambar
            ? { backgroundImage: `url(http://127.0.0.1:8000/storage/${kos.gambar})` }
            : {}"
          :class="{ 'has-image': kos.gambar }"
        >
          <div class="thumb-overlay"></div>
          <div class="thumb-content">
            <span v-if="!kos.gambar" class="thumb-emoji">🏡</span>
            <span class="thumb-zona">{{ kos.zona?.nama_zona ?? '—' }}</span>
          </div>
          <span class="status-pill" :class="kos.status">
            {{ kos.status === 'pending' ? '⏳ Pending'
             : kos.status === 'aktif'   ? '✅ Aktif'
             : '❌ Nonaktif' }}
          </span>
        </div>

        <!-- Info kos -->
        <div class="kos-info">
          <div class="kos-head">
            <div>
              <h3 class="kos-nama">{{ kos.nama_kos }}</h3>
              <p class="kos-alamat">
                <svg viewBox="0 0 16 16" fill="none" class="pin"><path d="M8 2a4 4 0 014 4c0 3-4 8-4 8S4 9 4 6a4 4 0 014-4z" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="6" r="1.5" stroke="currentColor" stroke-width="1.3"/></svg>
                {{ kos.alamat }}
              </p>
            </div>
            <div class="kos-harga">${{ Number(kos.harga_per_bulan ?? 0).toFixed(0) }}<span>/bln</span></div>
          </div>

          <!-- Data pemilik -->
          <div class="owner-info">
            <div class="oi-row">
              <span class="oi-label">👤 Pemilik</span>
              <span class="oi-val">{{ kos.user?.name ?? '-' }}</span>
            </div>
            <div class="oi-row">
              <span class="oi-label">📧 Email</span>
              <span class="oi-val">{{ kos.user?.email ?? '-' }}</span>
            </div>
            <div class="oi-row">
              <span class="oi-label">📅 Daftar</span>
              <span class="oi-val">{{ formatDate(kos.created_at) }}</span>
            </div>
            <div class="oi-row" v-if="kos.deskripsi">
              <span class="oi-label">📝 Deskripsi</span>
              <span class="oi-val desc">{{ kos.deskripsi }}</span>
            </div>
          </div>

          <!-- Tombol aksi -->
          <div class="kos-actions">
            <!-- Pending → tampilkan Setujui & Tolak -->
            <template v-if="kos.status === 'pending'">
              <button
                class="btn-approve"
                :disabled="actionLoading[kos.id]"
                @click="updateStatus(kos.id, 'aktif')"
              >
                <svg viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ actionLoading[kos.id] === 'aktif' ? 'Memproses...' : 'Setujui' }}
              </button>
              <button
                class="btn-reject"
                :disabled="actionLoading[kos.id]"
                @click="updateStatus(kos.id, 'nonaktif')"
              >
                <svg viewBox="0 0 16 16" fill="none"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                {{ actionLoading[kos.id] === 'nonaktif' ? 'Memproses...' : 'Tolak' }}
              </button>
            </template>

            <!-- Aktif → bisa nonaktifkan -->
            <template v-else-if="kos.status === 'aktif'">
              <span class="approved-label">✅ Sudah disetujui</span>
              <button
                class="btn-nonaktif"
                :disabled="actionLoading[kos.id]"
                @click="updateStatus(kos.id, 'nonaktif')"
              >
                Nonaktifkan
              </button>
            </template>

            <!-- Nonaktif → bisa aktifkan ulang -->
            <template v-else>
              <span class="rejected-label">❌ Ditolak</span>
              <button
                class="btn-reaktif"
                :disabled="actionLoading[kos.id]"
                @click="updateStatus(kos.id, 'aktif')"
              >
                Aktifkan Ulang
              </button>
            </template>

            <!-- Lihat detail -->
            <router-link :to="`/kos/${kos.id}`" class="btn-detail" target="_blank">
              Lihat Detail ↗
            </router-link>
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
  name: 'AdminKos',
  setup() {
    const router    = useRouter()
    const authStore = useAuthStore()

    const kosList      = ref([])
    const loading      = ref(true)
    const activeTab    = ref('pending')
    const actionLoading = reactive({})
    const toast        = reactive({ show: false, message: '', type: 'success' })
    const year         = new Date().getFullYear()

    const tabs = [
      { value: 'pending',  label: '⏳ Menunggu Review' },
      { value: 'aktif',    label: '✅ Aktif' },
      { value: 'nonaktif', label: '❌ Ditolak' },
      { value: 'semua',    label: '📋 Semua' },
    ]

    const filteredKos = computed(() => {
      if (activeTab.value === 'semua') return kosList.value
      return kosList.value.filter(k => k.status === activeTab.value)
    })

    const counts = computed(() => ({
      pending  : kosList.value.filter(k => k.status === 'pending').length,
      aktif    : kosList.value.filter(k => k.status === 'aktif').length,
      nonaktif : kosList.value.filter(k => k.status === 'nonaktif').length,
      total    : kosList.value.length,
    }))

    const showToast = (message, type = 'success') => {
      toast.message = message; toast.type = type; toast.show = true
      setTimeout(() => { toast.show = false }, 3500)
    }

    const fetchKos = async () => {
      loading.value = true
      try {
        const res   = await api.get('/admin/kos')
        kosList.value = res.data.data
      } catch {
        showToast('Gagal memuat data kos.', 'error')
      } finally {
        loading.value = false
      }
    }

    const updateStatus = async (kosId, status) => {
      actionLoading[kosId] = status
      try {
        await api.patch(`/admin/kos/${kosId}/status`, { status })

        const label = status === 'aktif' ? 'disetujui' : status === 'nonaktif' ? 'ditolak' : status
        showToast(`Kos berhasil ${label}! ✅`)

        // Update lokal tanpa refetch semua data
        const idx = kosList.value.findIndex(k => k.id === kosId)
        if (idx !== -1) kosList.value[idx].status = status

      } catch (e) {
        showToast(e.response?.data?.message || 'Gagal update status.', 'error')
      } finally {
        delete actionLoading[kosId]
      }
    }

    const formatDate = (d) => {
      if (!d) return '-'
      return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    }

    const handleLogout = async () => { await authStore.logout(); router.push('/login') }

    onMounted(fetchKos)

    return {
      kosList, loading, activeTab, tabs,
      filteredKos, counts, actionLoading, toast, year,
      updateStatus, formatDate, handleLogout,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap');
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.page{min-height:100vh;background:#faf8f4;font-family:'DM Sans',sans-serif;color:#1a1a1a;}

/* NAVBAR */
.navbar{display:flex;justify-content:space-between;align-items:center;padding:1rem 2.5rem;background:#faf8f4;border-bottom:1px solid #e8e2d9;position:sticky;top:0;z-index:100;}
.nav-brand-link{display:flex;align-items:center;gap:8px;text-decoration:none;}
.nav-icon{font-size:20px;}.nav-title{font-family:'Playfair Display',serif;font-size:18px;color:#1a1a1a;}
.nav-actions{display:flex;gap:8px;align-items:center;}
.btn-outline{padding:7px 14px;border:1px solid #d4c5b0;border-radius:8px;font-size:13px;color:#6b5b47;text-decoration:none;}
.btn-logout{background:none;border:1px solid #d4c5b0;color:#6b5b47;padding:7px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;}

/* HEADER */
.page-header{position:relative;overflow:hidden;background:#1e1a14;padding:3rem 2.5rem 2.5rem;color:#faf8f4;}
.header-inner{position:relative;z-index:2;max-width:1100px;margin:0 auto;}
.eyebrow{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#c17f3e;font-weight:500;margin-bottom:.6rem;}
.page-title{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.5rem);line-height:1.15;margin-bottom:.5rem;}
.page-title em{color:#c17f3e;font-style:italic;}
.page-sub{font-size:14px;color:#b5a898;font-weight:300;}
.deco{position:absolute;inset:0;z-index:1;}.dc{position:absolute;border-radius:50%;opacity:.07;background:#c17f3e;}
.c1{width:300px;height:300px;right:-50px;top:-80px;}.c2{width:140px;height:140px;right:200px;bottom:-40px;}

/* STATS */
.stats-bar{display:flex;align-items:center;gap:1.5rem;padding:1rem 2.5rem;background:#fff;border-bottom:1px solid #e8e2d9;flex-wrap:wrap;}
.stat-item{display:flex;flex-direction:column;}
.stat-num{font-family:'Playfair Display',serif;font-size:1.5rem;color:#c17f3e;line-height:1;}
.stat-num.amber{color:#d97706;}.stat-num.green{color:#059669;}.stat-num.red{color:#dc2626;}
.stat-label{font-size:10px;color:#8a7968;text-transform:uppercase;letter-spacing:.07em;margin-top:3px;}
.stat-div{width:1px;height:30px;background:#e8e2d9;flex-shrink:0;}

/* TABS */
.tab-bar{display:flex;gap:6px;padding:1rem 2.5rem;background:#fff;border-bottom:1px solid #e8e2d9;flex-wrap:wrap;}
.tab-btn{display:flex;align-items:center;gap:6px;padding:7px 16px;border-radius:20px;border:1px solid #e8e2d9;background:#fff;font-family:'DM Sans',sans-serif;font-size:13px;color:#6b5b47;cursor:pointer;transition:all .15s;}
.tab-btn.active{background:#1e1a14;color:#faf8f4;border-color:#1e1a14;}
.tab-badge{background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;min-width:20px;text-align:center;}

/* STATE */
.state-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 2rem;gap:.75rem;color:#8a7968;}
.spinner{width:36px;height:36px;border:3px solid #e8e2d9;border-top-color:#c17f3e;border-radius:50%;animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}
.empty-icon{font-size:3rem;}.empty-text{font-size:15px;}

/* KOS LIST */
.kos-wrap{max-width:1100px;margin:0 auto;padding:2rem 2.5rem;display:flex;flex-direction:column;gap:1rem;}

.kos-card{background:#fff;border:1px solid #e8e2d9;border-radius:16px;overflow:hidden;display:flex;animation:fadeUp .4s ease both;transition:box-shadow .2s;}
.kos-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.07);}
.kos-card.pending{border-color:#fde68a;border-left:4px solid #d97706;}
.kos-card.aktif{border-left:4px solid #059669;}
.kos-card.nonaktif{border-left:4px solid #dc2626;opacity:.8;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

/* Thumbnail */
.kos-thumb{width:180px;flex-shrink:0;background:linear-gradient(135deg,#2a2218,#1e1a14);background-size:cover;background-position:center;position:relative;display:flex;flex-direction:column;justify-content:flex-end;}
.kos-thumb.has-image .thumb-overlay{background:linear-gradient(to bottom,rgba(0,0,0,.1),rgba(0,0,0,.6));}
.thumb-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.05),rgba(0,0,0,.4));}
.thumb-content{position:relative;z-index:2;padding:10px 12px;}
.thumb-emoji{font-size:2rem;display:block;margin-bottom:4px;}
.thumb-zona{font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#f0c98a;font-weight:500;}
.status-pill{position:absolute;top:10px;right:10px;font-size:10px;font-weight:600;padding:3px 9px;border-radius:20px;z-index:3;}
.status-pill.pending{background:#fef3c7;color:#92400e;}
.status-pill.aktif{background:#d1fae5;color:#065f46;}
.status-pill.nonaktif{background:#fee2e2;color:#991b1b;}

/* Info */
.kos-info{flex:1;padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:10px;}
.kos-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
.kos-nama{font-family:'Playfair Display',serif;font-size:18px;color:#1a1a1a;}
.kos-alamat{display:flex;align-items:center;gap:5px;font-size:12.5px;color:#8a7968;margin-top:3px;}
.pin{width:12px;height:12px;flex-shrink:0;}
.kos-harga{font-family:'Playfair Display',serif;font-size:1.4rem;color:#c17f3e;white-space:nowrap;}
.kos-harga span{font-family:'DM Sans',sans-serif;font-size:12px;color:#a09080;}

/* Owner info */
.owner-info{background:#faf8f4;border-radius:10px;padding:10px 14px;border:1px solid #f0ebe3;}
.oi-row{display:flex;gap:8px;font-size:12.5px;padding:3px 0;flex-wrap:wrap;}
.oi-label{color:#8a7968;white-space:nowrap;min-width:70px;}
.oi-val{color:#1a1a1a;font-weight:500;}
.oi-val.desc{font-weight:400;color:#6b5b47;font-size:12px;}

/* Aksi */
.kos-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:4px;}
.btn-approve{display:flex;align-items:center;gap:5px;padding:8px 18px;background:#059669;color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:background .15s;}
.btn-approve:hover:not(:disabled){background:#047857;}
.btn-reject{display:flex;align-items:center;gap:5px;padding:8px 18px;background:#fee2e2;color:#991b1b;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;}
.btn-reject:hover:not(:disabled){background:#fecaca;}
.btn-nonaktif{padding:7px 14px;background:none;border:1px solid #e8e2d9;color:#6b5b47;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:12.5px;cursor:pointer;}
.btn-reaktif{padding:7px 14px;background:#d1fae5;color:#065f46;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:12.5px;font-weight:500;cursor:pointer;}
.btn-approve:disabled,.btn-reject:disabled,.btn-nonaktif:disabled,.btn-reaktif:disabled{opacity:.6;cursor:not-allowed;}
.btn-approve svg,.btn-reject svg{width:13px;height:13px;}
.approved-label{font-size:12.5px;color:#059669;font-weight:500;}
.rejected-label{font-size:12.5px;color:#dc2626;font-weight:500;}
.btn-detail{padding:7px 14px;background:#1e1a14;color:#faf8f4;border-radius:8px;font-size:12.5px;text-decoration:none;transition:background .15s;margin-left:auto;}
.btn-detail:hover{background:#c17f3e;}

/* TOAST */
.toast{position:fixed;bottom:1.5rem;right:1.5rem;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;z-index:300;box-shadow:0 4px 20px rgba(0,0,0,.15);}
.toast.success{background:#1a1a1a;color:#faf8f4;}.toast.error{background:#dc2626;color:#fff;}
.toast-enter-active,.toast-leave-active{transition:all .3s ease;}
.toast-enter-from,.toast-leave-to{opacity:0;transform:translateY(12px);}
.footer{text-align:center;padding:2rem;font-size:13px;color:#b5a898;border-top:1px solid #e8e2d9;margin-top:2rem;}

@media(max-width:768px){
  .navbar,.stats-bar,.tab-bar,.kos-wrap{padding-left:1.25rem;padding-right:1.25rem;}
  .page-header{padding:2rem 1.25rem;}
  .kos-card{flex-direction:column;}
  .kos-thumb{width:100%;min-height:120px;}
}
</style>