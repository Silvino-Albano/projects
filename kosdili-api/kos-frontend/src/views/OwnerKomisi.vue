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
        <router-link to="/owner/dashboard" class="btn-outline">Dashboard</router-link>
        <button class="btn-logout" @click="handleLogout">Keluar</button>
      </div>
    </nav>

    <!-- HEADER -->
    <section class="page-header">
      <div class="header-inner">
        <p class="eyebrow">Pemilik Kos</p>
        <h1 class="page-title">Tagihan <em>Komisi</em></h1>
        <p class="page-sub">Komisi platform 7% dari setiap booking yang berhasil.</p>
      </div>
      <div class="deco"><div class="dc c1"></div><div class="dc c2"></div></div>
    </section>

    <!-- RINGKASAN STATS -->
    <div class="stats-bar" v-if="ringkasan">
      <div class="stat-item">
        <span class="stat-num">${{ Number(ringkasan.total_semua).toFixed(2) }}</span>
        <span class="stat-label">Total Komisi</span>
      </div>
      <div class="stat-div"></div>
      <div class="stat-item">
        <span class="stat-num text-red">${{ Number(ringkasan.total_unpaid).toFixed(2) }}</span>
        <span class="stat-label">Belum Dibayar</span>
      </div>
      <div class="stat-div"></div>
      <div class="stat-item">
        <span class="stat-num text-green">${{ Number(ringkasan.total_paid).toFixed(2) }}</span>
        <span class="stat-label">Sudah Dibayar</span>
      </div>
      <div class="stat-div"></div>
      <div class="stat-item">
        <span class="stat-num">{{ ringkasan.total_tagihan }}</span>
        <span class="stat-label">Total Tagihan</span>
      </div>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="state-wrap">
      <div class="spinner"></div>
      <p>Memuat tagihan komisi...</p>
    </div>

    <!-- KOSONG -->
    <div v-else-if="!komisiList.length" class="state-wrap">
      <div class="empty-icon">🎉</div>
      <p class="empty-text">Belum ada tagihan komisi.</p>
      <p style="font-size:13px;color:#8a7968;margin-top:4px;">Tagihan muncul otomatis saat ada booking yang Anda setujui.</p>
    </div>

    <!-- DAFTAR KOMISI -->
    <div v-else class="komisi-list">

      <!-- Info cara bayar -->
      <div class="info-box" v-if="komisiList.some(k => k.status === 'unpaid')">
        <div class="info-icon">💡</div>
        <div>
          <strong>Cara bayar komisi:</strong> Transfer ke rekening admin KosDili,
          lalu upload bukti transfer di bawah. Admin akan konfirmasi dalam 1×24 jam.
        </div>
      </div>

      <div
        v-for="(k, i) in komisiList"
        :key="k.id"
        class="komisi-card"
        :class="{ paid: k.status === 'paid' }"
        :style="{ animationDelay: i * 0.06 + 's' }"
      >
        <!-- Header kartu -->
        <div class="kk-head">
          <div>
            <div class="kk-title">{{ k.booking?.kamar?.kos?.nama_kos ?? '-' }} — Kamar {{ k.booking?.kamar?.nomor_kamar }}</div>
            <div class="kk-date">
              Booking: {{ formatDate(k.booking?.tanggal_masuk) }} →
              {{ formatDate(k.booking?.tanggal_keluar) }}
              ({{ k.booking?.durasi_bulan }} bulan)
            </div>
          </div>
          <span class="status-badge" :class="k.status">
            {{ k.status === 'paid' ? '✅ Lunas' : '⏳ Belum Bayar' }}
          </span>
        </div>

        <!-- Detail perhitungan -->
        <div class="kk-calc">
          <div class="calc-row">
            <span>Total Sewa</span>
            <span>${{ Number(k.total_sewa).toFixed(2) }}</span>
          </div>
          <div class="calc-row">
            <span>Komisi Platform ({{ k.persen }}%)</span>
            <span class="calc-komisi">${{ Number(k.jumlah).toFixed(2) }}</span>
          </div>
        </div>

        <!-- Status bayar -->
        <div v-if="k.status === 'paid'" class="paid-info">
          <svg viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Dikonfirmasi pada {{ formatDate(k.dibayar_pada) }}
          <span v-if="k.catatan_admin"> · {{ k.catatan_admin }}</span>
        </div>

        <!-- Tombol upload bukti (jika belum bayar) -->
        <div v-else class="unpaid-action">
          <div v-if="k.bukti_bayar" class="sudah-upload">
            <svg viewBox="0 0 16 16" fill="none"><path d="M2 8l4 4 8-8" stroke="#0F6E56" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Bukti sudah diupload — menunggu konfirmasi admin
          </div>
          <div v-else>
            <div class="upload-area" @click="$refs['upload_' + k.id].click()">
              <svg viewBox="0 0 20 20" fill="none"><path d="M10 14V6M7 9l3-3 3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="3" width="14" height="14" rx="3" stroke="currentColor" stroke-width="1.4" stroke-dasharray="3 2"/></svg>
              <span>Klik untuk upload bukti transfer</span>
              <small>JPG / PNG · Maks 3MB</small>
            </div>
            <input
              type="file"
              accept="image/*"
              :ref="'upload_' + k.id"
              style="display:none"
              @change="uploadBukti(k.id, $event)"
            />
          </div>

          <div v-if="uploadLoading[k.id]" class="upload-loading">
            <div class="spinner sm"></div> Mengupload...
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
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth.js'
import api from '../services/api'

export default {
  name: 'OwnerKomisi',
  setup() {
    const router    = useRouter()
    const authStore = useAuthStore()

    const komisiList    = ref([])
    const ringkasan     = ref(null)
    const loading       = ref(true)
    const uploadLoading = reactive({})
    const toast         = reactive({ show: false, message: '', type: 'success' })
    const year          = new Date().getFullYear()

    const showToast = (message, type = 'success') => {
      toast.message = message; toast.type = type; toast.show = true
      setTimeout(() => { toast.show = false }, 3500)
    }

    const fetchKomisi = async () => {
      loading.value = true
      try {
        const { data } = await api.get('/owner/komisi')
        komisiList.value = data.data
        ringkasan.value  = data.ringkasan
      } catch {
        showToast('Gagal memuat data komisi.', 'error')
      } finally {
        loading.value = false
      }
    }

    const uploadBukti = async (komisiId, event) => {
      const file = event.target.files[0]
      if (!file) return

      uploadLoading[komisiId] = true
      try {
        const fd = new FormData()
        fd.append('bukti_bayar', file)
        await api.post(`/owner/komisi/${komisiId}/bayar`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        showToast('Bukti berhasil diupload! Admin akan konfirmasi segera.')
        await fetchKomisi()
      } catch (e) {
        showToast(e.response?.data?.message || 'Gagal upload bukti.', 'error')
      } finally {
        uploadLoading[komisiId] = false
        event.target.value = ''
      }
    }

    const formatDate = (d) => {
      if (!d) return '-'
      return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    }

    const handleLogout = async () => { await authStore.logout(); router.push('/login') }

    onMounted(fetchKomisi)

    return { komisiList, ringkasan, loading, uploadLoading, toast, year, uploadBukti, formatDate, handleLogout }
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
.nav-actions{display:flex;gap:10px;align-items:center;}
.btn-outline{padding:7px 16px;border:1px solid #d4c5b0;border-radius:8px;font-size:13px;color:#6b5b47;text-decoration:none;}
.btn-logout{background:none;border:1px solid #d4c5b0;color:#6b5b47;padding:7px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;}

/* HEADER */
.page-header{position:relative;overflow:hidden;background:#1e1a14;padding:3rem 2.5rem 2.5rem;color:#faf8f4;}
.header-inner{position:relative;z-index:2;max-width:1100px;margin:0 auto;}
.eyebrow{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#c17f3e;font-weight:500;margin-bottom:.6rem;}
.page-title{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.5rem);line-height:1.15;margin-bottom:.5rem;}
.page-title em{color:#c17f3e;font-style:italic;}
.page-sub{font-size:14px;color:#b5a898;font-weight:300;}
.deco{position:absolute;inset:0;z-index:1;}
.dc{position:absolute;border-radius:50%;opacity:.07;background:#c17f3e;}
.c1{width:300px;height:300px;right:-50px;top:-80px;}.c2{width:140px;height:140px;right:200px;bottom:-40px;}

/* STATS */
.stats-bar{display:flex;align-items:center;gap:1.5rem;padding:1rem 2.5rem;background:#fff;border-bottom:1px solid #e8e2d9;flex-wrap:wrap;max-width:100%;}
.stat-item{display:flex;flex-direction:column;}
.stat-num{font-family:'Playfair Display',serif;font-size:1.4rem;color:#c17f3e;line-height:1;}
.stat-num.text-red{color:#dc2626;}.stat-num.text-green{color:#059669;}
.stat-label{font-size:10px;color:#8a7968;text-transform:uppercase;letter-spacing:.07em;margin-top:3px;}
.stat-div{width:1px;height:30px;background:#e8e2d9;flex-shrink:0;}

/* STATE */
.state-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 2rem;gap:.75rem;color:#8a7968;}
.spinner{width:36px;height:36px;border:3px solid #e8e2d9;border-top-color:#c17f3e;border-radius:50%;animation:spin .7s linear infinite;}
.spinner.sm{width:18px;height:18px;border-width:2px;}
@keyframes spin{to{transform:rotate(360deg);}}
.empty-icon{font-size:3rem;}.empty-text{font-size:15px;}

/* KOMISI LIST */
.komisi-list{max-width:800px;margin:0 auto;padding:2rem 2.5rem;display:flex;flex-direction:column;gap:1rem;}

.info-box{background:#fdf5ec;border:1px solid #f0d9bb;border-radius:10px;padding:12px 16px;display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#6b5b47;line-height:1.6;}
.info-icon{font-size:1.2rem;flex-shrink:0;}

.komisi-card{background:#fff;border:1px solid #e8e2d9;border-radius:14px;padding:1.25rem 1.5rem;animation:fadeUp .4s ease both;transition:box-shadow .2s;}
.komisi-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.06);}
.komisi-card.paid{background:#f9fef9;border-color:#a7f3d0;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

.kk-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;}
.kk-title{font-size:14.5px;font-weight:500;color:#1a1a1a;margin-bottom:3px;}
.kk-date{font-size:12px;color:#8a7968;}

.status-badge{font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;white-space:nowrap;flex-shrink:0;}
.status-badge.paid{background:#d1fae5;color:#065f46;}
.status-badge.unpaid{background:#fef3c7;color:#92400e;}

.kk-calc{background:#faf8f4;border-radius:8px;padding:10px 14px;margin-bottom:12px;border:1px solid #f0ebe3;}
.calc-row{display:flex;justify-content:space-between;font-size:13px;padding:3px 0;}
.calc-row:first-child{color:#8a7968;}
.calc-komisi{font-weight:700;color:#c17f3e;font-size:15px;font-family:'Playfair Display',serif;}

.paid-info{display:flex;align-items:center;gap:6px;font-size:12.5px;color:#059669;padding:8px 12px;background:#f0fdf4;border-radius:8px;}
.paid-info svg{width:14px;height:14px;flex-shrink:0;}

.upload-area{border:1.5px dashed #d4c5b0;border-radius:10px;padding:16px;text-align:center;cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:4px;}
.upload-area:hover{border-color:#c17f3e;background:#fdf5ec;}
.upload-area svg{width:24px;height:24px;color:#c17f3e;}
.upload-area span{font-size:13px;color:#6b5b47;font-weight:500;}
.upload-area small{font-size:11px;color:#a09080;}
.sudah-upload{display:flex;align-items:center;gap:6px;font-size:13px;color:#0F6E56;padding:10px 14px;background:#E1F5EE;border-radius:8px;}
.sudah-upload svg{width:14px;height:14px;flex-shrink:0;}
.upload-loading{display:flex;align-items:center;gap:8px;margin-top:8px;font-size:12px;color:#8a7968;}

/* TOAST */
.toast{position:fixed;bottom:1.5rem;right:1.5rem;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;z-index:300;box-shadow:0 4px 20px rgba(0,0,0,.15);}
.toast.success{background:#1a1a1a;color:#faf8f4;}.toast.error{background:#dc2626;color:#fff;}
.toast-enter-active,.toast-leave-active{transition:all .3s ease;}
.toast-enter-from,.toast-leave-to{opacity:0;transform:translateY(12px);}

/* FOOTER */
.footer{text-align:center;padding:2rem;font-size:13px;color:#b5a898;border-top:1px solid #e8e2d9;margin-top:2rem;}

@media(max-width:640px){
  .navbar,.stats-bar,.komisi-list{padding-left:1.25rem;padding-right:1.25rem;}
  .page-header{padding:2rem 1.25rem;}
  .kk-head{flex-direction:column;gap:6px;}
}
</style>