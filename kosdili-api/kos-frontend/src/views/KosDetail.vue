<template>
  <div class="page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-brand">
        <router-link to="/" class="nav-brand-link">
          <span class="nav-icon">⌂</span>
          <span class="nav-title">KosDili</span>
        </router-link>
      </div>
      <div class="nav-actions">
        <template v-if="isLoggedIn">
          <router-link
            v-if="userRole === 'owner' || userRole === 'admin'"
            to="/add-kos"
            class="btn-add"
          >+ Tambah Kos</router-link>
          <button class="btn-logout" @click="handleLogout">Keluar</button>
        </template>
        <router-link v-else to="/login" class="btn-login">Masuk</router-link>
      </div>
    </nav>

    <!-- LOADING -->
    <div v-if="loading" class="state-wrap">
      <div class="spinner"></div>
      <p>Memuat detail kos...</p>
    </div>

    <!-- ERROR -->
    <div v-else-if="error" class="state-wrap">
      <div class="empty-icon">⚠️</div>
      <p class="empty-text">{{ error }}</p>
      <router-link to="/" class="btn-back">Kembali ke Beranda</router-link>
    </div>

    <template v-else>

      <!-- HERO -->
      <section class="detail-hero">
        <div class="hero-deco">
          <div class="deco-circle c1"></div>
          <div class="deco-circle c2"></div>
        </div>
        <div class="hero-inner">
          <div class="breadcrumb">
            <router-link to="/">Beranda</router-link>
            <span>/</span>
            <span>{{ kos.nama_kos }}</span>
          </div>
          <div class="hero-body">
            <div class="hero-left">
              <span class="zona-tag">{{ kos.zona?.nama_zona ?? '—' }}</span>
              <h1 class="kos-title">{{ kos.nama_kos }}</h1>
              <div class="kos-meta">
                <span class="meta-item">
                  <svg viewBox="0 0 20 20" fill="none"><path d="M10 2a6 6 0 016 6c0 4-6 10-6 10S4 12 4 8a6 6 0 016-6z" stroke="currentColor" stroke-width="1.4"/><circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.4"/></svg>
                  {{ kos.alamat }}
                </span>
                <span class="meta-item">
                  <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.4"/><path d="M10 6v4l3 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                  Pemilik: {{ kos.user?.name ?? '—' }}
                </span>
              </div>
              <p class="kos-desc">{{ kos.deskripsi ?? 'Tidak ada deskripsi.' }}</p>
            </div>
            <div class="hero-price-card">
              <p class="price-label">Harga per bulan</p>
              <p class="price-val">${{ Number(kos.harga_per_bulan ?? 0).toFixed(0) }}</p>
              <span class="badge" :class="kos.status">{{ kos.status }}</span>
              <div class="price-info">
                <svg viewBox="0 0 20 20" fill="none"><path d="M10 10m-7 0a7 7 0 1014 0 7 7 0 00-14 0" stroke="currentColor" stroke-width="1.3"/><path d="M10 7v3l2 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                Zona {{ kos.zona?.nama_zona }}
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- MAP SECTION -->
      <section class="map-section">
        <div class="section-inner">
          <div class="map-header">
            <div class="map-header-left">
              <h2 class="section-title">
                <svg viewBox="0 0 20 20" fill="none" class="title-icon">
                  <path d="M10 2a6 6 0 016 6c0 4-6 10-6 10S4 12 4 8a6 6 0 016-6z" stroke="currentColor" stroke-width="1.4"/>
                  <circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.4"/>
                </svg>
                Lokasi Kos
              </h2>
              <p class="map-address">{{ kos.alamat ?? 'Alamat belum tersedia' }}</p>
            </div>
            <div class="landmark-chips" v-if="kos.landmark && kos.landmark.length">
              <span v-for="lm in kos.landmark.slice(0, 4)" :key="lm.nama" class="lm-chip" :class="lm.kategori">
                <span class="lm-icon">{{ landmarkIcon(lm.kategori) }}</span>
                {{ lm.nama }}
                <span class="lm-dist">{{ lm.jarak_km }} km</span>
              </span>
            </div>
          </div>
          <div class="map-wrap" v-if="kos.latitude && kos.longitude">
            <div id="kos-map" ref="mapRef"></div>
            <div class="map-legend">
              <div class="legend-item"><span class="legend-dot kos-dot"></span><span>Lokasi kos</span></div>
              <div class="legend-item"><span class="legend-dot landmark-dot"></span><span>Landmark</span></div>
            </div>
            <div class="map-coords">
              <svg viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.2"/><path d="M8 5v3l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
              {{ Number(kos.latitude).toFixed(5) }}, {{ Number(kos.longitude).toFixed(5) }}
            </div>
          </div>
          <div v-else class="map-no-coords"><span>📍</span><p>Koordinat lokasi belum tersedia.</p></div>
        </div>
      </section>

      <!-- KAMAR SECTION -->
      <section class="kamar-section">
        <div class="section-inner">
          <div class="section-header">
            <h2 class="section-title">Daftar Kamar</h2>
            <span class="kamar-count">{{ availableCount }} kamar tersedia dari {{ kos.kamar?.length ?? 0 }}</span>
          </div>

          <div v-if="!kos.kamar || kos.kamar.length === 0" class="state-wrap" style="padding:3rem">
            <div class="empty-icon">🚪</div>
            <p class="empty-text">Belum ada kamar terdaftar.</p>
          </div>

          <div v-else class="kamar-grid">
            <div
              v-for="(k, i) in kos.kamar"
              :key="k.id"
              class="kamar-card"
              :class="{ 'kamar-card--unavail': k.status !== 'available' }"
              :style="{ animationDelay: i * 0.07 + 's' }"
            >
              <div class="kamar-header">
                <span class="kamar-num">Kamar {{ k.nomor_kamar ?? '#' + k.id }}</span>
                <span class="kamar-badge" :class="k.status === 'available' ? 'avail' : 'booked'">
                  {{ k.status === 'available' ? 'Tersedia' : 'Terisi' }}
                </span>
              </div>

              <div class="kamar-icon">{{ k.status === 'available' ? '🚪' : '🔒' }}</div>

              <div class="kamar-harga">
                <span class="kharga-val">${{ Number(k.harga ?? kos.harga_per_bulan ?? 0).toFixed(0) }}</span>
                <span class="kharga-unit">/bulan</span>
              </div>

              <div class="kamar-fasilitas">
                <span class="kharga-unit">Fasilitas: {{ k.fasilitas ?? 'Tidak tersedia' }}</span>
              </div>

              <!-- Tombol booking — buka MODAL -->
              <button
                v-if="k.status === 'available'"
                class="btn-book"
                @click="bukaModalBooking(k)"
              >
                <span>Booking Sekarang</span>
                <svg viewBox="0 0 20 20" fill="none">
                  <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

              <div v-else class="kamar-full">
                <svg viewBox="0 0 20 20" fill="none"><path d="M5 10l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Sudah dibooking
              </div>
            </div>
          </div>
        </div>
      </section>

    </template>

    <!-- ═══════════════════════════════════════════════════
         MODAL BOOKING — muncul saat tombol diklik
    ════════════════════════════════════════════════════ -->
    <div v-if="showBookingModal" class="modal-overlay" @click.self="tutupModalBooking">
      <div class="modal-booking">

        <!-- Header modal -->
        <div class="modal-booking-head">
          <div>
            <div class="modal-eyebrow">Form Booking</div>
            <h3 class="modal-booking-title">{{ kamarDipilih?.nomor_kamar ? 'Kamar ' + kamarDipilih.nomor_kamar : 'Detail Kamar' }}</h3>
          </div>
          <button class="modal-close-btn" @click="tutupModalBooking">✕</button>
        </div>

        <!-- Info kamar -->
        <div class="modal-kamar-info">
          <div class="mki-row">
            <span class="mki-label">🏠 Kos</span>
            <span class="mki-val">{{ kos.nama_kos }}</span>
          </div>
          <div class="mki-row">
            <span class="mki-label">📍 Alamat</span>
            <span class="mki-val">{{ kos.alamat }}</span>
          </div>
          <div class="mki-row">
            <span class="mki-label">💰 Harga</span>
            <span class="mki-val mki-harga">${{ Number(kamarDipilih?.harga ?? kos.harga_per_bulan ?? 0).toFixed(0) }}/bulan</span>
          </div>
        </div>

        <!-- Form isian -->
        <div class="modal-form">

          <!-- Tanggal masuk -->
          <div class="mf-group">
            <label class="mf-label">📅 Tanggal Masuk</label>
            <input
              type="date"
              class="mf-input"
              v-model="formBooking.tanggal_masuk"
              :min="hariIni"
            />
          </div>

          <!-- Durasi sewa -->
          <div class="mf-group">
            <label class="mf-label">⏱ Durasi Sewa</label>
            <div class="durasi-options">
              <button
                v-for="opt in [1, 3, 6, 12]"
                :key="opt"
                class="durasi-btn"
                :class="{ active: formBooking.durasi_bulan === opt }"
                type="button"
                @click="formBooking.durasi_bulan = opt"
              >
                {{ opt }} bln
              </button>
            </div>
          </div>

          <!-- Metode bayar -->
          <div class="mf-group">
            <label class="mf-label">💳 Metode Pembayaran</label>
            <div class="pay-options">
              <button
                class="pay-btn"
                :class="{ active: formBooking.payment_method === 'cash' }"
                type="button"
                @click="formBooking.payment_method = 'cash'"
              >
                <span class="pay-icon">💵</span>
                <span class="pay-name">Cash</span>
                <span class="pay-desc">Bayar langsung ke pemilik</span>
              </button>
              <button
                class="pay-btn"
                :class="{ active: formBooking.payment_method === 'transfer' }"
                type="button"
                @click="formBooking.payment_method = 'transfer'"
              >
                <span class="pay-icon">🏦</span>
                <span class="pay-name">Transfer</span>
                <span class="pay-desc">Upload bukti transfer</span>
              </button>
            </div>
          </div>

          <!-- Catatan -->
          <div class="mf-group">
            <label class="mf-label">📝 Catatan (opsional)</label>
            <textarea
              class="mf-input mf-textarea"
              v-model="formBooking.catatan"
              placeholder="Contoh: saya mahasiswa UNTL, rencana masuk awal bulan..."
              rows="2"
            ></textarea>
          </div>

          <!-- Ringkasan total -->
          <div class="booking-summary">
            <div class="summary-row">
              <span>Harga</span>
              <span>${{ Number(kamarDipilih?.harga ?? kos.harga_per_bulan ?? 0).toFixed(0) }}/bulan</span>
            </div>
            <div class="summary-row">
              <span>Durasi</span>
              <span>{{ formBooking.durasi_bulan }} bulan</span>
            </div>
            <div class="summary-row">
              <span>Masuk</span>
              <span>{{ formBooking.tanggal_masuk }}</span>
            </div>
            <div class="summary-row">
              <span>Perkiraan keluar</span>
              <span>{{ perkiraanKeluar }}</span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-row summary-total">
              <span>Total</span>
              <span class="total-harga">${{ totalHarga }}</span>
            </div>
            <div class="summary-note">
              {{ formBooking.payment_method === 'cash'
                ? '💵 Bayar cash langsung ke pemilik setelah booking disetujui'
                : '🏦 Upload bukti transfer setelah booking disetujui' }}
            </div>
          </div>

        </div>

        <!-- Error message -->
        <div v-if="bookingError" class="booking-error">
          ⚠️ {{ bookingError }}
        </div>

        <!-- Footer modal -->
        <div class="modal-booking-foot">
          <button class="btn-batal" @click="tutupModalBooking" :disabled="bookingLoading">
            Batal
          </button>
          <button class="btn-konfirmasi" @click="submitBooking" :disabled="bookingLoading">
            <div v-if="bookingLoading" class="spinner-sm"></div>
            <span>{{ bookingLoading ? 'Memproses...' : 'Konfirmasi Booking' }}</span>
          </button>
        </div>

      </div>
    </div>

    <!-- MODAL SUKSES -->
    <div v-if="showSuksesModal" class="modal-overlay" @click.self="showSuksesModal = false">
      <div class="modal-sukses">
        <div class="sukses-icon">🎉</div>
        <h3>Booking Berhasil!</h3>
        <p>Permintaan booking Anda telah dikirim. Tunggu konfirmasi dari pemilik kos.</p>
        <div class="sukses-detail">
          <div class="sd-row"><span>Kos</span><span>{{ infoBooking?.nama_kos }}</span></div>
          <div class="sd-row"><span>Masuk</span><span>{{ infoBooking?.tanggal_masuk }}</span></div>
          <div class="sd-row"><span>Keluar</span><span>{{ infoBooking?.tanggal_keluar }}</span></div>
          <div class="sd-row"><span>Total</span><span class="sd-harga">{{ infoBooking?.total_harga }}</span></div>
          <div class="sd-row"><span>Bayar via</span><span>{{ infoBooking?.payment_method === 'cash' ? '💵 Cash' : '🏦 Transfer' }}</span></div>
        </div>
        <button class="btn-oke" @click="showSuksesModal = false">Oke, Mengerti</button>
      </div>
    </div>

    <!-- MODAL LOGIN -->
    <div v-if="showLoginPrompt" class="modal-overlay" @click.self="showLoginPrompt = false">
      <div class="modal-login-prompt">
        <div class="modal-icon">🔐</div>
        <h3>Login Diperlukan</h3>
        <p>Silakan login terlebih dahulu untuk melakukan booking kos.</p>
        <div class="modal-actions">
          <button class="modal-cancel" @click="showLoginPrompt = false">Batal</button>
          <router-link to="/login" class="modal-login">Masuk Sekarang</router-link>
        </div>
      </div>
    </div>

    <footer class="footer">
      <p>© 2025 KosDili · Dili, Timor-Leste</p>
    </footer>
  </div>
</template>

<script>
import { onMounted, onUnmounted, ref, computed, reactive, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../store/auth.js'

export default {
  setup() {
    const route     = useRoute()
    const router    = useRouter()
    const authStore = useAuthStore()

    // ── State utama ─────────────────────────────────────────────
    const kos             = ref({})
    const loading         = ref(true)
    const error           = ref('')
    const mapRef          = ref(null)
    let   mapInstance     = null

    // ── State modal ──────────────────────────────────────────────
    const showLoginPrompt  = ref(false)
    const showBookingModal = ref(false)
    const showSuksesModal  = ref(false)
    const kamarDipilih     = ref(null)
    const bookingLoading   = ref(false)
    const bookingError     = ref('')
    const infoBooking      = ref(null)

    // ── Form booking ─────────────────────────────────────────────
    const hariIni = new Date().toISOString().split('T')[0]
    const formBooking = reactive({
      tanggal_masuk  : hariIni,
      durasi_bulan   : 1,
      payment_method : 'cash',
      catatan        : '',
    })

    // ── Computed ─────────────────────────────────────────────────
    const isLoggedIn = computed(() => !!localStorage.getItem('token'))

    const userRole = computed(() => {
      const user = JSON.parse(localStorage.getItem('user') || '{}')
      return user.role || user.peran || ''
    })

    const availableCount = computed(() =>
      kos.value.kamar?.filter(k => k.status === 'available').length ?? 0
    )

    const totalHarga = computed(() => {
      const harga = Number(kamarDipilih.value?.harga ?? kos.value.harga_per_bulan ?? 0)
      return (harga * formBooking.durasi_bulan).toFixed(0)
    })

    const perkiraanKeluar = computed(() => {
      if (!formBooking.tanggal_masuk) return '-'
      const d = new Date(formBooking.tanggal_masuk)
      d.setMonth(d.getMonth() + formBooking.durasi_bulan)
      return d.toISOString().split('T')[0]
    })

    // ── Modal booking ─────────────────────────────────────────────
    const bukaModalBooking = (kamar) => {
      // Belum login → modal login
      if (!isLoggedIn.value) {
        showLoginPrompt.value = true
        return
      }
      // Reset form
      kamarDipilih.value         = kamar
      formBooking.tanggal_masuk  = hariIni
      formBooking.durasi_bulan   = 1
      formBooking.payment_method = 'cash'
      formBooking.catatan        = ''
      bookingError.value         = ''
      showBookingModal.value     = true
    }

    const tutupModalBooking = () => {
      if (bookingLoading.value) return
      showBookingModal.value = false
      kamarDipilih.value     = null
      bookingError.value     = ''
    }

    // ── Submit booking ────────────────────────────────────────────
    const submitBooking = async () => {
      bookingError.value  = ''
      bookingLoading.value = true

      try {
        const payload = {
          kamar_id       : kamarDipilih.value.id,
          tanggal_masuk  : formBooking.tanggal_masuk,
          durasi_bulan   : parseInt(formBooking.durasi_bulan),
          payment_method : String(formBooking.payment_method),
          catatan        : formBooking.catatan || '',
        }

        const res = await api.post('/booking', payload)

        // Simpan info untuk modal sukses
        infoBooking.value = res.data.info ?? {
          nama_kos       : kos.value.nama_kos,
          tanggal_masuk  : formBooking.tanggal_masuk,
          tanggal_keluar : perkiraanKeluar.value,
          total_harga    : `$${totalHarga.value}`,
          payment_method : formBooking.payment_method,
        }

        // Tutup modal booking, buka modal sukses
        showBookingModal.value = false
        showSuksesModal.value  = true

        // Refresh data kamar
        fetchDetail()

      } catch (err) {
        // Tangkap error dari Laravel — bisa berupa object errors
        const data = err.response?.data
        if (data?.errors) {
          // Laravel validation errors — ambil pesan pertama dari setiap field
          const pesanErrors = Object.values(data.errors).flat().join(' | ')
          bookingError.value = pesanErrors
        } else {
          bookingError.value = data?.message || 'Gagal melakukan booking. Coba lagi.'
        }
      } finally {
        bookingLoading.value = false
      }
    }

    // ── Landmark icon ─────────────────────────────────────────────
    const landmarkIcon = (k) => ({ kampus:'🎓', pasar:'🏪', rumah_sakit:'🏥', transportasi:'✈️' })[k] ?? '📍'

    // ── Leaflet map ───────────────────────────────────────────────
    const initMap = async () => {
      const lat = parseFloat(kos.value.latitude)
      const lng = parseFloat(kos.value.longitude)
      if (!lat || !lng) return
      await nextTick()
      if (!document.getElementById('leaflet-css')) {
        const l = document.createElement('link')
        l.id = 'leaflet-css'; l.rel = 'stylesheet'
        l.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
        document.head.appendChild(l)
      }
      const L = await new Promise((res, rej) => {
        if (window.L) return res(window.L)
        const s = document.createElement('script')
        s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
        s.onload = () => res(window.L); s.onerror = rej
        document.head.appendChild(s)
      })
      if (mapInstance) { mapInstance.remove(); mapInstance = null }
      mapInstance = L.map('kos-map', { center:[lat,lng], zoom:15, scrollWheelZoom:false })
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution:'© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom:19
      }).addTo(mapInstance)
      const kosIcon = L.divIcon({
        className:'',
        html:`<div style="width:38px;height:38px;background:#c17f3e;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 14px rgba(193,127,62,.5);"></div>
              <div style="position:absolute;top:-2px;left:50%;transform:translateX(-50%);font-size:9px;font-weight:600;color:#1e1a14;background:#faf8f4;padding:2px 6px;border-radius:10px;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,.15);">${kos.value.nama_kos??'Kos'}</div>`,
        iconSize:[38,38], iconAnchor:[19,38], popupAnchor:[0,-42],
      })
      L.marker([lat,lng],{icon:kosIcon}).addTo(mapInstance)
        .bindPopup(`<div style="font-family:'DM Sans',sans-serif;min-width:140px;"><b style="font-size:13px;">${kos.value.nama_kos??''}</b><br><span style="font-size:11px;color:#8a7968;">${kos.value.alamat??''}</span><br><b style="color:#c17f3e;">$${Number(kos.value.harga_per_bulan??0).toFixed(0)}/bulan</b></div>`,{maxWidth:220})
      const landmarks = kos.value.landmark || []
      landmarks.forEach(lm => {
        const la=parseFloat(lm.latitude), lo=parseFloat(lm.longitude)
        if (!la||!lo) return
        L.marker([la,lo],{icon:L.divIcon({className:'',html:`<div style="width:28px;height:28px;background:#1e1a14;border:2.5px solid #fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;box-shadow:0 3px 8px rgba(0,0,0,.3);">${landmarkIcon(lm.kategori)}</div>`,iconSize:[28,28],iconAnchor:[14,28],popupAnchor:[0,-32]})})
          .addTo(mapInstance)
          .bindPopup(`<div style="font-family:'DM Sans',sans-serif;"><b style="font-size:12px;">${lm.nama}</b><br><span style="font-size:11px;color:#c17f3e;">${lm.jarak_km} km dari sini</span></div>`,{maxWidth:180})
        L.polyline([[lat,lng],[la,lo]],{color:'#c17f3e',weight:1.5,opacity:.35,dashArray:'5,8'}).addTo(mapInstance)
      })
      if (landmarks.length) {
        mapInstance.fitBounds([[lat,lng],...landmarks.filter(l=>l.latitude&&l.longitude).map(l=>[parseFloat(l.latitude),parseFloat(l.longitude)])],{padding:[40,40],maxZoom:15})
      }
    }

    // ── Fetch data ────────────────────────────────────────────────
    const fetchDetail = async () => {
      loading.value = true; error.value = ''
      try {
        const res = await api.get(`/kos/${route.params.id}`)
        kos.value = res.data.data
        await nextTick()
        initMap()
      } catch {
        error.value = 'Gagal memuat detail kos. Coba refresh halaman.'
      } finally {
        loading.value = false
      }
    }

    const handleLogout = async () => { await authStore.logout(); router.push('/login') }

    onMounted(fetchDetail)
    onUnmounted(() => { if (mapInstance) { mapInstance.remove(); mapInstance = null } })

    return {
      kos, loading, error, mapRef,
      isLoggedIn, userRole, availableCount,
      showLoginPrompt, showBookingModal, showSuksesModal,
      kamarDipilih, bookingLoading, bookingError, infoBooking,
      formBooking, hariIni, totalHarga, perkiraanKeluar,
      bukaModalBooking, tutupModalBooking, submitBooking,
      handleLogout, landmarkIcon,
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
.nav-actions{display:flex;align-items:center;gap:10px;}
.btn-add{background:#c17f3e;color:#fff;padding:8px 18px;border-radius:8px;font-size:13.5px;font-weight:500;text-decoration:none;}
.btn-logout{background:none;border:1px solid #d4c5b0;color:#6b5b47;padding:8px 16px;border-radius:8px;font-size:13.5px;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-logout:hover{background:#f0ebe3;}
.btn-login{background:#1a1a1a;color:#faf8f4;padding:8px 20px;border-radius:8px;font-size:13.5px;font-weight:500;text-decoration:none;}

/* STATE */
.state-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 2rem;gap:1rem;color:#8a7968;}
.spinner{width:36px;height:36px;border:3px solid #e8e2d9;border-top-color:#c17f3e;border-radius:50%;animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}
.empty-icon{font-size:3rem;}.empty-text{font-size:15px;}
.btn-back{margin-top:.5rem;padding:8px 20px;background:#1a1a1a;color:#faf8f4;border-radius:8px;font-size:13px;text-decoration:none;}

/* HERO */
.detail-hero{position:relative;overflow:hidden;background:#1e1a14;padding:3rem 0 3.5rem;color:#faf8f4;}
.hero-deco{position:absolute;inset:0;}
.deco-circle{position:absolute;border-radius:50%;opacity:.07;background:#c17f3e;}
.c1{width:380px;height:380px;right:-60px;top:-100px;}.c2{width:180px;height:180px;right:240px;bottom:-50px;}
.hero-inner{position:relative;z-index:2;max-width:1100px;margin:0 auto;padding:0 2.5rem;}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:12.5px;color:#7a6e65;margin-bottom:1.5rem;}
.breadcrumb a{color:#c17f3e;text-decoration:none;}
.hero-body{display:flex;gap:2.5rem;align-items:flex-start;flex-wrap:wrap;}
.hero-left{flex:1;min-width:280px;}
.zona-tag{display:inline-block;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#c17f3e;font-weight:500;border:1px solid rgba(193,127,62,.4);padding:3px 10px;border-radius:20px;margin-bottom:.9rem;}
.kos-title{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,4vw,2.5rem);line-height:1.2;color:#faf8f4;margin-bottom:1rem;}
.kos-meta{display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;}
.meta-item{display:flex;align-items:center;gap:6px;font-size:13px;color:#b5a898;font-weight:300;}
.meta-item svg{width:14px;height:14px;flex-shrink:0;}
.kos-desc{font-size:14px;color:#9a8e84;font-weight:300;line-height:1.7;}
.hero-price-card{background:#16130e;border:1px solid #3a3228;border-radius:16px;padding:1.75rem;min-width:220px;display:flex;flex-direction:column;gap:10px;}
.price-label{font-size:11.5px;color:#7a6e65;text-transform:uppercase;letter-spacing:.08em;}
.price-val{font-family:'Playfair Display',serif;font-size:2.2rem;color:#c17f3e;}
.badge{display:inline-block;font-size:11px;font-weight:500;padding:4px 12px;border-radius:20px;text-transform:capitalize;align-self:flex-start;}
.badge.aktif{background:#d1fae5;color:#065f46;}.badge.pending{background:#fef3c7;color:#92400e;}.badge.nonaktif{background:#fee2e2;color:#991b1b;}
.price-info{font-size:12.5px;color:#6b5b47;display:flex;align-items:center;gap:6px;}
.price-info svg{width:13px;height:13px;}

/* MAP */
.map-section{padding:2.5rem 0;background:#f4efe8;border-top:1px solid #e8e2d9;border-bottom:1px solid #e8e2d9;}
.section-inner{max-width:1100px;margin:0 auto;padding:0 2.5rem;}
.map-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;}
.map-header-left{display:flex;flex-direction:column;gap:4px;}
.section-title{font-family:'Playfair Display',serif;font-size:1.5rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;}
.title-icon{width:18px;height:18px;color:#c17f3e;}
.map-address{font-size:13px;color:#8a7968;margin-top:2px;}
.landmark-chips{display:flex;flex-wrap:wrap;gap:6px;align-items:center;}
.lm-chip{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;padding:4px 10px;border-radius:20px;border:1px solid #e8e2d9;background:#fff;color:#4a3f35;}
.lm-chip.kampus{border-color:#c17f3e;background:#fdf5ec;}.lm-chip.pasar{border-color:#7ab87a;background:#f0faf0;}.lm-chip.rumah_sakit{border-color:#7aabd4;background:#eef5fb;}.lm-chip.transportasi{border-color:#a47ab8;background:#f5eefb;}
.lm-icon{font-size:12px;}.lm-dist{font-size:10px;color:#c17f3e;font-weight:600;margin-left:2px;}
.map-wrap{position:relative;border-radius:16px;overflow:hidden;border:1px solid #ddd5c8;box-shadow:0 6px 24px rgba(0,0,0,.08);}
#kos-map{width:100%;height:420px;background:#e8e2d9;}
.map-legend{position:absolute;bottom:48px;left:12px;z-index:999;background:rgba(250,248,244,.92);backdrop-filter:blur(6px);border:1px solid #e8e2d9;border-radius:10px;padding:8px 12px;display:flex;flex-direction:column;gap:5px;}
.legend-item{display:flex;align-items:center;gap:7px;font-size:11.5px;color:#4a3f35;}
.legend-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.kos-dot{background:#c17f3e;}.landmark-dot{background:#1e1a14;}
.map-coords{position:absolute;bottom:12px;left:12px;z-index:999;background:rgba(30,26,20,.75);backdrop-filter:blur(4px);color:#c17f3e;font-size:10.5px;font-family:monospace;padding:4px 10px;border-radius:6px;display:flex;align-items:center;gap:5px;}
.map-coords svg{width:11px;height:11px;}
.map-no-coords{margin-top:1rem;display:flex;align-items:center;gap:8px;font-size:13px;color:#8a7968;background:#fff7ee;border:1px solid #f0d9bb;border-radius:10px;padding:10px 14px;}
:deep(.leaflet-popup-content-wrapper){border-radius:12px!important;box-shadow:0 8px 24px rgba(0,0,0,.15)!important;font-family:'DM Sans',sans-serif!important;border:1px solid #e8e2d9!important;}
:deep(.leaflet-popup-tip){background:#fff!important;}
:deep(.leaflet-control-attribution){font-size:9px!important;background:rgba(250,248,244,.8)!important;}

/* KAMAR */
.kamar-section{padding:3rem 0;}
.section-header{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:8px;}
.kamar-count{font-size:13px;color:#8a7968;}
.kamar-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1.25rem;}
.kamar-card{background:#fff;border:1px solid #e8e2d9;border-radius:16px;padding:1.5rem;display:flex;flex-direction:column;gap:10px;animation:fadeUp .45s ease both;transition:transform .2s,box-shadow .2s;}
.kamar-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(0,0,0,.07);}
.kamar-card--unavail{opacity:.7;background:#f7f5f2;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.kamar-header{display:flex;align-items:center;justify-content:space-between;}
.kamar-num{font-weight:500;font-size:14.5px;color:#1a1a1a;}
.kamar-badge{font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px;}
.kamar-badge.avail{background:#d1fae5;color:#065f46;}.kamar-badge.booked{background:#fee2e2;color:#991b1b;}
.kamar-icon{font-size:2.5rem;text-align:center;}
.kamar-harga{display:flex;align-items:baseline;gap:3px;}
.kharga-val{font-family:'Playfair Display',serif;font-size:1.6rem;color:#c17f3e;}
.kharga-unit{font-size:12px;color:#a09080;}
.kamar-fasilitas{font-size:12px;color:#a09080;}
.btn-book{display:flex;align-items:center;justify-content:center;gap:8px;height:44px;background:#1e1a14;color:#faf8f4;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:13.5px;font-weight:500;cursor:pointer;transition:background .2s,transform .1s;margin-top:auto;}
.btn-book:hover{background:#c17f3e;}.btn-book:active{transform:scale(.98);}
.btn-book svg{width:14px;height:14px;}
.kamar-full{display:flex;align-items:center;justify-content:center;gap:6px;font-size:13px;color:#a09080;padding:.5rem 0;margin-top:auto;}
.kamar-full svg{width:14px;height:14px;}

/* ════════════════════════════════════
   MODAL BOOKING
════════════════════════════════════ */
.modal-overlay{position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;padding:1rem;}

.modal-booking{background:#fff;border-radius:20px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;animation:modalIn .25s ease;display:flex;flex-direction:column;}
@keyframes modalIn{from{opacity:0;transform:scale(.95) translateY(12px);}to{opacity:1;transform:scale(1) translateY(0);}}

.modal-booking-head{display:flex;align-items:flex-start;justify-content:space-between;padding:1.5rem 1.5rem 1rem;border-bottom:1px solid #f0ebe3;position:sticky;top:0;background:#fff;z-index:1;}
.modal-eyebrow{font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#c17f3e;font-weight:500;margin-bottom:3px;}
.modal-booking-title{font-family:'Playfair Display',serif;font-size:1.3rem;color:#1a1a1a;}
.modal-close-btn{background:none;border:none;font-size:16px;color:#8a7968;cursor:pointer;padding:4px;line-height:1;margin-top:2px;}

/* Info kamar */
.modal-kamar-info{padding:.75rem 1.5rem;background:#faf8f4;border-bottom:1px solid #f0ebe3;}
.mki-row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;padding:3px 0;}
.mki-label{color:#8a7968;}.mki-val{color:#1a1a1a;font-weight:500;}.mki-harga{color:#c17f3e;font-family:'Playfair Display',serif;font-size:15px;}

/* Form */
.modal-form{padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:1rem;}
.mf-group{display:flex;flex-direction:column;gap:6px;}
.mf-label{font-size:12px;font-weight:500;color:#6b5b47;}
.mf-input{width:100%;padding:9px 12px;border:1px solid #e8e2d9;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;color:#1a1a1a;background:#faf8f4;outline:none;transition:border-color .15s;}
.mf-input:focus{border-color:#c17f3e;}
.mf-textarea{resize:none;min-height:60px;}

/* Durasi options */
.durasi-options{display:flex;gap:6px;flex-wrap:wrap;}
.durasi-btn{padding:7px 14px;border-radius:8px;border:1px solid #e8e2d9;font-family:'DM Sans',sans-serif;font-size:13px;cursor:pointer;background:#fff;color:#6b5b47;transition:all .15s;font-weight:400;}
.durasi-btn.active{background:#1e1a14;color:#faf8f4;border-color:#1e1a14;font-weight:500;}
.durasi-btn:hover:not(.active){background:#f0ebe3;}

/* Payment options */
.pay-options{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.pay-btn{display:flex;flex-direction:column;align-items:center;gap:3px;padding:12px 8px;border-radius:10px;border:1.5px solid #e8e2d9;background:#fff;cursor:pointer;transition:all .15s;text-align:center;}
.pay-btn.active{border-color:#c17f3e;background:#fdf5ec;}
.pay-btn:hover:not(.active){background:#f7f5f2;}
.pay-icon{font-size:1.5rem;}
.pay-name{font-size:13px;font-weight:500;color:#1a1a1a;}
.pay-desc{font-size:10px;color:#8a7968;}

/* Summary */
.booking-summary{background:#faf8f4;border-radius:10px;padding:12px 14px;border:1px solid #e8e2d9;display:flex;flex-direction:column;gap:6px;}
.summary-row{display:flex;justify-content:space-between;font-size:12.5px;color:#6b5b47;}
.summary-divider{height:1px;background:#e8e2d9;margin:2px 0;}
.summary-total{font-weight:600;font-size:14px;color:#1a1a1a;}
.total-harga{color:#c17f3e;font-family:'Playfair Display',serif;font-size:16px;}
.summary-note{font-size:11px;color:#8a7968;margin-top:2px;padding-top:4px;border-top:1px dashed #e8e2d9;}

/* Error */
.booking-error{margin:0 1.5rem;padding:10px 14px;background:#fee2e2;border-radius:8px;font-size:13px;color:#991b1b;border:1px solid #fecaca;}

/* Footer modal */
.modal-booking-foot{display:flex;gap:10px;padding:1rem 1.5rem;border-top:1px solid #f0ebe3;position:sticky;bottom:0;background:#fff;}
.btn-batal{flex:1;height:44px;background:#f0ebe3;color:#6b5b47;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;cursor:pointer;}
.btn-batal:disabled{opacity:.5;cursor:not-allowed;}
.btn-konfirmasi{flex:2;height:44px;background:#c17f3e;color:#fff;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s;}
.btn-konfirmasi:hover:not(:disabled){background:#a96c30;}
.btn-konfirmasi:disabled{opacity:.6;cursor:not-allowed;}
.spinner-sm{width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0;}

/* Modal sukses */
.modal-sukses{background:#fff;border-radius:20px;padding:2rem;max-width:380px;width:100%;display:flex;flex-direction:column;align-items:center;gap:12px;text-align:center;}
.sukses-icon{font-size:3rem;}
.modal-sukses h3{font-family:'Playfair Display',serif;font-size:1.4rem;color:#1a1a1a;}
.modal-sukses p{font-size:13.5px;color:#8a7968;line-height:1.6;}
.sukses-detail{width:100%;background:#faf8f4;border-radius:10px;padding:12px;border:1px solid #e8e2d9;}
.sd-row{display:flex;justify-content:space-between;font-size:12.5px;padding:4px 0;border-bottom:1px solid #f0ebe3;}
.sd-row:last-child{border:none;}
.sd-row span:first-child{color:#8a7968;}
.sd-harga{color:#c17f3e;font-weight:600;}
.btn-oke{width:100%;height:44px;background:#1e1a14;color:#faf8f4;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;cursor:pointer;transition:background .2s;}
.btn-oke:hover{background:#c17f3e;}

/* Modal login */
.modal-login-prompt{background:#fff;border-radius:20px;padding:2.5rem 2rem;max-width:380px;width:100%;display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;}
.modal-icon{font-size:3rem;}
.modal-login-prompt h3{font-family:'Playfair Display',serif;font-size:1.3rem;color:#1a1a1a;}
.modal-login-prompt p{font-size:14px;color:#8a7968;font-weight:300;line-height:1.6;}
.modal-actions{display:flex;gap:10px;margin-top:.75rem;width:100%;}
.modal-cancel{flex:1;height:42px;background:none;border:1px solid #e8e2d9;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:13.5px;color:#6b5b47;cursor:pointer;}
.modal-login{flex:1;height:42px;background:#c17f3e;color:#fff;border-radius:10px;font-size:13.5px;font-weight:500;text-decoration:none;display:flex;align-items:center;justify-content:center;}

/* FOOTER */
.footer{text-align:center;padding:2rem;font-size:13px;color:#b5a898;border-top:1px solid #e8e2d9;margin-top:2rem;}

/* RESPONSIVE */
@media(max-width:640px){
  .navbar{padding:1rem 1.25rem;}
  .hero-inner,.section-inner{padding:0 1.25rem;}
  #kos-map{height:280px;}
  .hero-price-card{min-width:unset;width:100%;}
  .kamar-grid{grid-template-columns:1fr;}
  .modal-overlay{align-items:flex-end;padding:0;}
  .modal-booking{border-radius:20px 20px 0 0;max-width:100%;max-height:95vh;}
  .pay-options{grid-template-columns:1fr 1fr;}
  .modal-sukses{border-radius:20px 20px 0 0;max-width:100%;}
}
</style>