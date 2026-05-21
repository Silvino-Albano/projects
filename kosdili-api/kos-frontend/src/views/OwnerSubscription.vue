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
        <h1 class="page-title">Paket <em>Langganan</em></h1>
        <p class="page-sub">Pilih paket yang sesuai untuk kelola kos Anda lebih efektif.</p>
      </div>
      <div class="deco"><div class="dc c1"></div><div class="dc c2"></div></div>
    </section>

    <!-- LANGGANAN AKTIF -->
    <div class="aktif-wrap" v-if="aktif">
      <div class="aktif-card">
        <div class="aktif-left">
          <div class="aktif-label">Langganan Aktif</div>
          <div class="aktif-plan">{{ aktif.plan?.nama }}</div>
          <div class="aktif-dates">
            {{ formatDate(aktif.mulai_pada) }} — {{ formatDate(aktif.berakhir_pada) }}
          </div>
        </div>
        <div class="aktif-right">
          <div class="sisa-hari" :class="sisaHari <= 7 ? 'warning' : ''">
            <span class="sisa-num">{{ sisaHari }}</span>
            <span class="sisa-label">hari tersisa</span>
          </div>
          <span class="aktif-status" :class="aktif.payment_status">
            {{ aktif.payment_status === 'paid' ? '✅ Lunas' : '⏳ Belum Bayar' }}
          </span>
        </div>
      </div>

      <!-- Upload bukti kalau belum bayar -->
      <div v-if="aktif.payment_status === 'unpaid'" class="bukti-section">
        <div class="bukti-info">
          <strong>💳 Cara Bayar:</strong> Transfer ke rekening admin KosDili,
          lalu upload bukti di bawah ini.
        </div>
        <div v-if="aktif.bukti_bayar" class="sudah-upload">
          <svg viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          Bukti sudah diupload — menunggu konfirmasi admin
        </div>
        <div v-else>
          <div class="upload-area" @click="$refs.uploadBukti.click()">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 16V8M9 11l3-3 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><rect x="3" y="3" width="18" height="18" rx="4" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 3"/></svg>
            <span>Klik untuk upload bukti transfer</span>
            <small>JPG / PNG · Maks 3MB</small>
          </div>
          <input type="file" ref="uploadBukti" accept="image/*" style="display:none" @change="uploadBukti($event)" />
          <div v-if="uploadLoading" class="uploading">
            <div class="spinner sm"></div> Mengupload...
          </div>
        </div>
      </div>
    </div>

    <!-- PILIH PAKET -->
    <section class="plans-section">
      <div class="plans-inner">
        <h2 class="plans-title">
          {{ aktif ? 'Perpanjang atau Upgrade Paket' : 'Pilih Paket Anda' }}
        </h2>
        <p class="plans-sub">Semua harga dalam USD per bulan. Bisa pilih durasi 1–12 bulan.</p>

        <div v-if="loadingPlans" class="state-wrap">
          <div class="spinner"></div><p>Memuat paket...</p>
        </div>

        <div v-else class="plans-grid">
          <div
            v-for="plan in plans"
            :key="plan.id"
            class="plan-card"
            :class="{
              featured : plan.slug === 'pro',
              bisnis   : plan.slug === 'bisnis',
              selected : selectedPlan?.id === plan.id,
            }"
            @click="pilihPlan(plan)"
          >
            <!-- Badge populer -->
            <div v-if="plan.slug === 'pro'" class="plan-popular">⭐ Paling Populer</div>

            <div class="plan-name">{{ plan.nama }}</div>
            <div class="plan-price">
              <span v-if="plan.harga == 0" class="price-val">Gratis</span>
              <template v-else>
                <span class="price-val">${{ Number(plan.harga).toFixed(0) }}</span>
                <span class="price-unit">/bulan</span>
              </template>
            </div>

            <ul class="plan-features">
              <li v-for="(f, i) in plan.fitur_list" :key="i">
                <svg viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ f }}
              </li>
            </ul>

            <button
              class="plan-btn"
              :class="{ active: selectedPlan?.id === plan.id }"
            >
              {{ selectedPlan?.id === plan.id ? '✓ Dipilih' : 'Pilih Paket' }}
            </button>
          </div>
        </div>

        <!-- Form konfirmasi setelah pilih -->
        <transition name="slide-down">
          <div v-if="selectedPlan" class="order-form">
            <h3 class="order-title">
              Konfirmasi Langganan — Paket {{ selectedPlan.nama }}
            </h3>

            <div class="order-grid">
              <!-- Durasi -->
              <div class="form-group">
                <label class="form-label">Durasi Langganan</label>
                <div class="durasi-opts">
                  <button
                    v-for="d in [1, 3, 6, 12]" :key="d"
                    class="durasi-btn"
                    :class="{ active: durasi === d }"
                    type="button"
                    @click="durasi = d"
                  >
                    {{ d }} bulan
                    <span v-if="d >= 6" class="diskon-label">Hemat!</span>
                  </button>
                </div>
              </div>

              <!-- Metode bayar -->
              <div class="form-group">
                <label class="form-label">Metode Pembayaran</label>
                <div class="pay-opts">
                  <button
                    class="pay-btn"
                    :class="{ active: metodeBayar === 'cash' }"
                    type="button"
                    @click="metodeBayar = 'cash'"
                  >
                    <span class="pay-icon">💵</span>
                    <span class="pay-name">Cash</span>
                    <span class="pay-desc">Bayar langsung ke admin</span>
                  </button>
                  <button
                    class="pay-btn"
                    :class="{ active: metodeBayar === 'transfer' }"
                    type="button"
                    @click="metodeBayar = 'transfer'"
                  >
                    <span class="pay-icon">🏦</span>
                    <span class="pay-name">Transfer</span>
                    <span class="pay-desc">Upload bukti transfer</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Ringkasan -->
            <div class="order-summary">
              <div class="os-row"><span>Paket</span><span>{{ selectedPlan.nama }}</span></div>
              <div class="os-row"><span>Harga</span><span>${{ Number(selectedPlan.harga).toFixed(2) }}/bulan</span></div>
              <div class="os-row"><span>Durasi</span><span>{{ durasi }} bulan</span></div>
              <div class="os-divider"></div>
              <div class="os-row os-total">
                <span>Total</span>
                <span class="os-harga">${{ (Number(selectedPlan.harga) * durasi).toFixed(2) }}</span>
              </div>
              <div v-if="selectedPlan.harga == 0" class="os-note">
                🎉 Paket gratis langsung aktif tanpa pembayaran!
              </div>
              <div v-else class="os-note">
                {{ metodeBayar === 'cash'
                  ? '💵 Bayar cash ke admin KosDili, lalu konfirmasi.'
                  : '🏦 Transfer ke rekening admin, lalu upload bukti.' }}
              </div>
            </div>

            <div v-if="orderError" class="order-error">⚠️ {{ orderError }}</div>

            <div class="order-actions">
              <button class="btn-batal" @click="selectedPlan = null">Batal</button>
              <button class="btn-subscribe" :disabled="orderLoading" @click="subscribe">
                <div v-if="orderLoading" class="spinner sm white"></div>
                <span>{{ orderLoading ? 'Memproses...' : 'Konfirmasi Langganan' }}</span>
              </button>
            </div>
          </div>
        </transition>
      </div>
    </section>

    <!-- RIWAYAT -->
    <section class="riwayat-section" v-if="riwayat.length">
      <div class="plans-inner">
        <h2 class="plans-title">Riwayat Langganan</h2>
        <div class="riwayat-table">
          <div class="rt-head">
            <span>Paket</span><span>Mulai</span><span>Berakhir</span>
            <span>Total</span><span>Status</span>
          </div>
          <div v-for="r in riwayat" :key="r.id" class="rt-row">
            <span class="rt-plan">{{ r.plan?.nama }}</span>
            <span>{{ formatDate(r.mulai_pada) }}</span>
            <span>{{ formatDate(r.berakhir_pada) }}</span>
            <span class="rt-harga">${{ Number(r.plan?.harga).toFixed(2) }}</span>
            <span>
              <span class="badge-status" :class="r.status">
                {{ r.status === 'active' ? 'Aktif' : r.status === 'expired' ? 'Berakhir' : 'Dibatalkan' }}
              </span>
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- TOAST -->
    <transition name="toast">
      <div v-if="toast.show" class="toast" :class="toast.type">{{ toast.message }}</div>
    </transition>

    <footer class="footer"><p>© {{ year }} KosDili · Dili, Timor-Leste</p></footer>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth.js'
import api from '../services/api'

export default {
  name: 'OwnerSubscription',
  setup() {
    const router    = useRouter()
    const authStore = useAuthStore()

    const plans        = ref([])
    const aktif        = ref(null)
    const riwayat      = ref([])
    const sisaHari     = ref(0)
    const loadingPlans = ref(true)
    const selectedPlan = ref(null)
    const durasi       = ref(1)
    const metodeBayar  = ref('transfer')
    const orderLoading = ref(false)
    const orderError   = ref('')
    const uploadLoading= ref(false)
    const toast        = reactive({ show: false, message: '', type: 'success' })
    const year         = new Date().getFullYear()

    const showToast = (message, type = 'success') => {
      toast.message = message; toast.type = type; toast.show = true
      setTimeout(() => { toast.show = false }, 3500)
    }

    const fetchPlans = async () => {
      try {
        const res  = await api.get('/subscription/plans')
        plans.value = res.data.data
      } catch { showToast('Gagal memuat paket.', 'error') }
      finally { loadingPlans.value = false }
    }

    const fetchSaya = async () => {
      try {
        const res    = await api.get('/subscription/saya')
        aktif.value  = res.data.aktif
        sisaHari.value = res.data.sisa_hari
        riwayat.value = res.data.riwayat
      } catch {}
    }

    const pilihPlan = (plan) => {
      if (selectedPlan.value?.id === plan.id) {
        selectedPlan.value = null
      } else {
        selectedPlan.value = plan
        durasi.value = 1
        metodeBayar.value = 'transfer'
        orderError.value = ''
        setTimeout(() => {
          document.querySelector('.order-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }, 100)
      }
    }

    const subscribe = async () => {
      orderError.value  = ''
      orderLoading.value = true
      try {
        const res = await api.post('/subscription/berlangganan', {
          plan_id      : selectedPlan.value.id,
          metode_bayar : metodeBayar.value,
          durasi_bulan : durasi.value,
        })
        showToast(res.data.message)
        selectedPlan.value = null
        await fetchSaya()
      } catch (e) {
        orderError.value = e.response?.data?.message || 'Gagal berlangganan.'
      } finally {
        orderLoading.value = false
      }
    }

    const uploadBukti = async (event) => {
      const file = event.target.files[0]
      if (!file || !aktif.value) return
      uploadLoading.value = true
      try {
        const fd = new FormData()
        fd.append('bukti_bayar', file)
        await api.post(`/subscription/${aktif.value.id}/upload-bukti`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        showToast('Bukti berhasil diupload! Admin akan konfirmasi segera.')
        await fetchSaya()
      } catch (e) {
        showToast(e.response?.data?.message || 'Gagal upload bukti.', 'error')
      } finally {
        uploadLoading.value = false
        event.target.value = ''
      }
    }

    const formatDate = (d) => {
      if (!d) return '-'
      return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    }

    const handleLogout = async () => { await authStore.logout(); router.push('/login') }

    onMounted(async () => { await Promise.all([fetchPlans(), fetchSaya()]) })

    return {
      plans, aktif, riwayat, sisaHari, loadingPlans,
      selectedPlan, durasi, metodeBayar,
      orderLoading, orderError, uploadLoading, toast, year,
      pilihPlan, subscribe, uploadBukti, formatDate, handleLogout,
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
.deco{position:absolute;inset:0;z-index:1;}.dc{position:absolute;border-radius:50%;opacity:.07;background:#c17f3e;}
.c1{width:300px;height:300px;right:-50px;top:-80px;}.c2{width:140px;height:140px;right:200px;bottom:-40px;}

/* AKTIF */
.aktif-wrap{max-width:1100px;margin:0 auto;padding:1.5rem 2.5rem 0;}
.aktif-card{background:#fff;border:1.5px solid #c17f3e;border-radius:14px;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
.aktif-label{font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#8a7968;margin-bottom:3px;}
.aktif-plan{font-family:'Playfair Display',serif;font-size:1.4rem;color:#c17f3e;}
.aktif-dates{font-size:12px;color:#8a7968;margin-top:3px;}
.aktif-right{display:flex;flex-direction:column;align-items:flex-end;gap:6px;}
.sisa-hari{display:flex;align-items:baseline;gap:4px;}
.sisa-num{font-family:'Playfair Display',serif;font-size:2rem;color:#1a1a1a;}
.sisa-label{font-size:12px;color:#8a7968;}
.sisa-hari.warning .sisa-num{color:#dc2626;}
.aktif-status{font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;}
.aktif-status.paid{background:#d1fae5;color:#065f46;}
.aktif-status.unpaid{background:#fef3c7;color:#92400e;}

/* BUKTI */
.bukti-section{background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:1.25rem 1.5rem;margin-top:10px;}
.bukti-info{font-size:13px;color:#78350f;margin-bottom:12px;line-height:1.6;}
.upload-area{border:1.5px dashed #d4c5b0;border-radius:10px;padding:18px;text-align:center;cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:5px;}
.upload-area:hover{border-color:#c17f3e;background:#fdf5ec;}
.upload-area svg{width:28px;height:28px;color:#c17f3e;}
.upload-area span{font-size:13px;color:#6b5b47;font-weight:500;}
.upload-area small{font-size:11px;color:#a09080;}
.sudah-upload{display:flex;align-items:center;gap:8px;font-size:13px;color:#059669;padding:10px 14px;background:#E1F5EE;border-radius:8px;}
.sudah-upload svg{width:16px;height:16px;flex-shrink:0;}
.uploading{display:flex;align-items:center;gap:8px;margin-top:8px;font-size:12px;color:#8a7968;}

/* PLANS */
.plans-section{padding:2.5rem 0;}
.plans-inner{max-width:1100px;margin:0 auto;padding:0 2.5rem;}
.plans-title{font-family:'Playfair Display',serif;font-size:1.6rem;color:#1a1a1a;margin-bottom:.4rem;}
.plans-sub{font-size:13px;color:#8a7968;margin-bottom:2rem;}
.plans-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;margin-bottom:2rem;}

/* Plan card */
.plan-card{background:#fff;border:1.5px solid #e8e2d9;border-radius:16px;padding:1.75rem;cursor:pointer;transition:all .2s;position:relative;display:flex;flex-direction:column;gap:14px;}
.plan-card:hover{border-color:#c17f3e;box-shadow:0 8px 24px rgba(193,127,62,.12);}
.plan-card.featured{background:#1e1a14;border-color:#c17f3e;}
.plan-card.bisnis{background:#f7f5f2;border-color:#d4c5b0;}
.plan-card.selected{border-color:#c17f3e;box-shadow:0 0 0 3px rgba(193,127,62,.2);}
.plan-popular{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#c17f3e;color:#fff;font-size:11px;font-weight:600;padding:4px 14px;border-radius:20px;white-space:nowrap;}
.plan-name{font-size:16px;font-weight:600;color:#1a1a1a;}
.plan-card.featured .plan-name{color:#faf8f4;}
.plan-price{display:flex;align-items:baseline;gap:3px;}
.price-val{font-family:'Playfair Display',serif;font-size:2rem;color:#c17f3e;}
.price-unit{font-size:12px;color:#8a7968;}
.plan-card.featured .price-unit{color:#7a6e65;}
.plan-features{list-style:none;display:flex;flex-direction:column;gap:7px;flex:1;}
.plan-features li{display:flex;align-items:flex-start;gap:7px;font-size:13px;color:#4a3f35;}
.plan-card.featured .plan-features li{color:#b5a898;}
.plan-features li svg{width:14px;height:14px;color:#c17f3e;flex-shrink:0;margin-top:2px;}
.plan-btn{width:100%;padding:10px;border-radius:10px;border:1.5px solid #c17f3e;background:transparent;color:#c17f3e;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;margin-top:auto;}
.plan-btn:hover,.plan-btn.active{background:#c17f3e;color:#fff;}
.plan-card.featured .plan-btn{background:#c17f3e;color:#fff;border-color:#c17f3e;}
.plan-card.featured .plan-btn:hover{background:#a96c30;}

/* ORDER FORM */
.order-form{background:#fff;border:1.5px solid #c17f3e;border-radius:16px;padding:2rem;margin-top:1.5rem;}
.order-title{font-family:'Playfair Display',serif;font-size:1.3rem;color:#1a1a1a;margin-bottom:1.25rem;}
.order-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;}
.form-group{display:flex;flex-direction:column;gap:8px;}
.form-label{font-size:12px;font-weight:600;color:#6b5b47;text-transform:uppercase;letter-spacing:.05em;}
.durasi-opts{display:flex;gap:6px;flex-wrap:wrap;}
.durasi-btn{padding:7px 14px;border-radius:8px;border:1px solid #e8e2d9;background:#fff;font-family:'DM Sans',sans-serif;font-size:13px;color:#6b5b47;cursor:pointer;transition:all .15s;position:relative;}
.durasi-btn.active{background:#1e1a14;color:#faf8f4;border-color:#1e1a14;}
.diskon-label{position:absolute;top:-8px;right:-4px;background:#059669;color:#fff;font-size:8px;padding:1px 5px;border-radius:6px;font-weight:600;}
.pay-opts{display:flex;gap:8px;}
.pay-btn{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:12px 8px;border-radius:10px;border:1.5px solid #e8e2d9;background:#fff;cursor:pointer;transition:all .15s;}
.pay-btn.active{border-color:#c17f3e;background:#fdf5ec;}
.pay-icon{font-size:1.4rem;}.pay-name{font-size:12px;font-weight:600;color:#1a1a1a;}.pay-desc{font-size:10px;color:#8a7968;}
.order-summary{background:#faf8f4;border-radius:10px;padding:14px 16px;border:1px solid #e8e2d9;margin-bottom:1rem;}
.os-row{display:flex;justify-content:space-between;font-size:13px;padding:4px 0;color:#6b5b47;}
.os-divider{height:1px;background:#e8e2d9;margin:6px 0;}
.os-total{font-weight:600;font-size:14px;color:#1a1a1a;}
.os-harga{color:#c17f3e;font-family:'Playfair Display',serif;font-size:17px;}
.os-note{font-size:11.5px;color:#8a7968;margin-top:8px;padding-top:6px;border-top:1px dashed #e8e2d9;}
.order-error{background:#fee2e2;border-radius:8px;padding:10px 14px;font-size:13px;color:#991b1b;margin-bottom:10px;}
.order-actions{display:flex;gap:10px;}
.btn-batal{flex:1;height:44px;background:#f0ebe3;color:#6b5b47;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;cursor:pointer;}
.btn-subscribe{flex:2;height:44px;background:#c17f3e;color:#fff;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s;}
.btn-subscribe:hover:not(:disabled){background:#a96c30;}
.btn-subscribe:disabled{opacity:.6;cursor:not-allowed;}

/* RIWAYAT */
.riwayat-section{padding:0 0 3rem;}
.riwayat-table{background:#fff;border-radius:12px;border:1px solid #e8e2d9;overflow:hidden;}
.rt-head{display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr;gap:12px;padding:10px 16px;background:#f7f5f2;font-size:11px;font-weight:600;color:#8a7968;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e8e2d9;}
.rt-row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr;gap:12px;padding:12px 16px;border-bottom:1px solid #f5f0eb;font-size:13px;align-items:center;}
.rt-row:last-child{border:none;}
.rt-plan{font-weight:500;color:#1a1a1a;}.rt-harga{color:#c17f3e;font-weight:500;}
.badge-status{font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:20px;}
.badge-status.active{background:#d1fae5;color:#065f46;}
.badge-status.expired{background:#f3f4f6;color:#6b7280;}
.badge-status.cancelled{background:#fee2e2;color:#991b1b;}

/* STATE */
.state-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem;gap:.75rem;color:#8a7968;}
.spinner{width:36px;height:36px;border:3px solid #e8e2d9;border-top-color:#c17f3e;border-radius:50%;animation:spin .7s linear infinite;}
.spinner.sm{width:18px;height:18px;border-width:2px;}
.spinner.white{border-color:rgba(255,255,255,.3);border-top-color:#fff;}
@keyframes spin{to{transform:rotate(360deg);}}

/* TOAST */
.toast{position:fixed;bottom:1.5rem;right:1.5rem;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;z-index:300;box-shadow:0 4px 20px rgba(0,0,0,.15);}
.toast.success{background:#1a1a1a;color:#faf8f4;}.toast.error{background:#dc2626;color:#fff;}
.toast-enter-active,.toast-leave-active{transition:all .3s ease;}
.toast-enter-from,.toast-leave-to{opacity:0;transform:translateY(12px);}
.slide-down-enter-active,.slide-down-leave-active{transition:all .3s ease;}
.slide-down-enter-from,.slide-down-leave-to{opacity:0;transform:translateY(-12px);}

/* FOOTER */
.footer{text-align:center;padding:2rem;font-size:13px;color:#b5a898;border-top:1px solid #e8e2d9;}

@media(max-width:768px){
  .navbar,.plans-inner,.aktif-wrap{padding-left:1.25rem;padding-right:1.25rem;}
  .page-header{padding:2rem 1.25rem;}
  .plans-grid{grid-template-columns:1fr;}
  .order-grid{grid-template-columns:1fr;}
  .aktif-card{flex-direction:column;}
  .aktif-right{align-items:flex-start;}
  .pay-opts{flex-direction:column;}
}
</style>
