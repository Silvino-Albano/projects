<template>
  <div class="page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-brand">
        <router-link to="/" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
          <span class="nav-icon">🏠</span>
          <span class="nav-title">KosDili</span>
        </router-link>
      </div>
      <div class="nav-actions">
        <router-link to="/add-kos" class="btn-add">+ Tambah Kos</router-link>
        <button class="btn-logout" @click="handleLogout">Keluar</button>
      </div>
    </nav>

    <!-- HEADER -->
    <section class="dash-header">
      <div class="dash-header-content">
        <p class="dash-eyebrow">Dashboard Pemilik</p>
        <h1 class="dash-title">Kelola Kos <em>Anda</em></h1>
        <p class="dash-sub">Pantau semua kos, kamar, dan status penyewa dalam satu tempat.</p>
      </div>
      <div class="header-deco">
        <div class="deco-circle c1"></div>
        <div class="deco-circle c2"></div>
      </div>
    </section>

    <!-- STATS BAR -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-num">{{ kosList.length }}</span>
        <span class="stat-label">Total Kos</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-num">{{ totalKamar }}</span>
        <span class="stat-label">Total Kamar</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-num">{{ totalBooked }}</span>
        <span class="stat-label">Terisi</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-num">{{ totalAvailable }}</span>
        <span class="stat-label">Kosong</span>
      </div>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="state-wrap">
      <div class="spinner"></div>
      <p>Memuat data kos...</p>
    </div>

    <!-- EMPTY -->
    <div v-else-if="kosList.length === 0" class="state-wrap">
      <div class="empty-icon">🏚️</div>
      <p class="empty-text">Anda belum memiliki kos.</p>
      <router-link to="/add-kos" class="btn-add" style="margin-top:1rem;">+ Tambah Kos Pertama</router-link>
    </div>

    <!-- KOS LIST -->
    <div v-else class="kos-list">
      <div
        v-for="(kos, i) in kosList"
        :key="kos.id"
        class="kos-panel"
        :style="{ animationDelay: i * 0.08 + 's' }"
      >
        <!-- KOS HEADER -->
        <div class="panel-head">
          <div
            class="panel-thumb"
            :style="kos.gambar ? { backgroundImage: `url(http://127.0.0.1:8000/storage/${kos.gambar})` } : {}"
            :class="{ 'has-image': kos.gambar }"
          >
            <div class="thumb-overlay"></div>
            <div class="panel-thumb-content">
              <span v-if="!kos.gambar" class="thumb-emoji">🏡</span>
              <span class="thumb-zona">{{ kos.zona?.nama_zona ?? '—' }}</span>
            </div>
          </div>

          <div class="panel-info">
            <div class="panel-title-row">
              <h2 class="panel-title">{{ kos.nama_kos }}</h2>
              <span class="badge" :class="kos.status">{{ kos.status }}</span>
            </div>
            <p class="panel-alamat">
              <svg viewBox="0 0 20 20" fill="none" class="pin-icon">
                <path d="M10 2a6 6 0 016 6c0 4-6 10-6 10S4 12 4 8a6 6 0 016-6z" stroke="currentColor" stroke-width="1.4"/>
                <circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.4"/>
              </svg>
              {{ kos.alamat }}
            </p>
            <p class="panel-harga">${{ Number(kos.harga_per_bulan).toFixed(0) }}<span>/bulan</span></p>

            <div class="panel-actions">
              <button class="btn-action edit" @click="editKos(kos)">✏️ Edit</button>
              <button class="btn-action danger" @click="confirmHapus(kos)">🗑️ Hapus</button>
              <button class="btn-action toggle" @click="togglePanel(kos.id)">
                {{ openPanels.includes(kos.id) ? '▲ Sembunyikan' : '▼ Lihat Kamar' }}
              </button>
            </div>
          </div>
        </div>

        <!-- KAMAR LIST (collapsible) -->
        <div v-if="openPanels.includes(kos.id)" class="kamar-section">
          <div v-if="loadingKamar[kos.id]" class="kamar-loading">
            <div class="spinner sm"></div> Memuat kamar...
          </div>
          <div v-else-if="kamarData[kos.id]?.length === 0" class="kamar-empty">
            Belum ada kamar terdaftar.
          </div>
          <div v-else class="kamar-grid">
            <div
              v-for="kamar in kamarData[kos.id]"
              :key="kamar.id"
              class="kamar-card"
              :class="kamar.status"
            >
              <div class="kamar-head">
                <span class="kamar-nomor">Kamar {{ kamar.nomor_kamar }}</span>
                <span class="kamar-badge" :class="kamar.status">{{ kamar.status }}</span>
              </div>
              <p class="kamar-harga">${{ Number(kamar.harga).toFixed(0) }}/bulan</p>

              <div v-if="kamar.bookings && kamar.bookings.length > 0" class="kamar-tenant">
                <div class="tenant-info">
                  <span class="tenant-label">Penyewa:</span>
                  <span class="tenant-name">{{ kamar.bookings[0].user?.name ?? '—' }}</span>
                </div>
                <div class="tenant-info">
                  <span class="tenant-label">Keluar:</span>
                  <span class="tenant-date">{{ formatDate(kamar.bookings[0].tanggal_keluar) }}</span>
                </div>
                <div class="tenant-info">
                  <span class="tenant-label">Status:</span>
                  <span class="booking-status" :class="kamar.bookings[0].status">
                    {{ kamar.bookings[0].status }}
                  </span>
                </div>
              </div>

              <button
                v-if="kamar.status === 'booked'"
                class="btn-aktivasi"
                :disabled="aktivasiLoading[kamar.id]"
                @click="aktivasiKamar(kamar, kos.id)"
              >
                {{ aktivasiLoading[kamar.id] ? 'Memproses...' : '✅ Tenant Sudah Keluar' }}
              </button>
              <span v-else-if="kamar.status === 'available'" class="kamar-kosong-label">
                🟢 Kamar Kosong
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL EDIT KOS -->
    <div v-if="editModal" class="modal-overlay" @click.self="editModal = false">
      <div class="modal">
        <div class="modal-head">
          <h3>Edit Kos</h3>
          <button class="modal-close" @click="editModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Kos</label>
            <input v-model="editForm.nama_kos" type="text" class="form-input" />
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <input v-model="editForm.alamat" type="text" class="form-input" />
          </div>
          <div class="form-group">
            <label>Harga per Bulan ($)</label>
            <input v-model="editForm.harga_per_bulan" type="number" class="form-input" />
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea v-model="editForm.deskripsi" class="form-input" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Gambar Baru (opsional)</label>
            <input type="file" accept="image/*" class="form-input" @change="onGambarChange" />
          </div>
          <p v-if="editError" class="form-error">{{ editError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="editModal = false">Batal</button>
          <button class="btn-save" :disabled="editLoading" @click="submitEdit">
            {{ editLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div v-if="hapusModal" class="modal-overlay" @click.self="hapusModal = false">
      <div class="modal modal-sm">
        <div class="modal-head">
          <h3>Hapus Kos</h3>
          <button class="modal-close" @click="hapusModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p class="hapus-warning">
            Apakah Anda yakin ingin menghapus <strong>{{ hapusTarget?.nama_kos }}</strong>?
          </p>
          <p class="hapus-note">⚠️ Kos tidak bisa dihapus jika masih ada booking aktif.</p>
          <p v-if="hapusError" class="form-error">{{ hapusError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="hapusModal = false">Batal</button>
          <button class="btn-hapus" :disabled="hapusLoading" @click="submitHapus">
            {{ hapusLoading ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
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
      <p>© 2026 KosDili · Dili, Timor-Leste</p>
    </footer>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth.js'
import axios from 'axios'

export default {
  name: 'OwnerDashboard',
  setup() {
    const router    = useRouter()
    const authStore = useAuthStore()

    const kosList         = ref([])
    const loading         = ref(true)
    const openPanels      = ref([])
    const kamarData       = reactive({})
    const loadingKamar    = reactive({})
    const aktivasiLoading = reactive({})

    const editModal   = ref(false)
    const editTarget  = ref(null)
    const editForm    = reactive({ nama_kos: '', alamat: '', harga_per_bulan: '', deskripsi: '', gambar: null })
    const editLoading = ref(false)
    const editError   = ref('')

    const hapusModal   = ref(false)
    const hapusTarget  = ref(null)
    const hapusLoading = ref(false)
    const hapusError   = ref('')

    const toast = reactive({ show: false, message: '', type: 'success' })

    const totalKamar     = computed(() => kosList.value.reduce((s, k) => s + (k.kamar?.length ?? 0), 0))
    const totalBooked    = computed(() => kosList.value.reduce((s, k) => s + (k.kamar?.filter(km => km.status === 'booked').length ?? 0), 0))
    const totalAvailable = computed(() => kosList.value.reduce((s, k) => s + (k.kamar?.filter(km => km.status === 'available').length ?? 0), 0))

    const token = () => localStorage.getItem('token')

    const showToast = (message, type = 'success') => {
      toast.message = message; toast.type = type; toast.show = true
      setTimeout(() => { toast.show = false }, 3000)
    }

    const fetchKos = async () => {
      loading.value = true
      try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/owner/kos', {
          headers: { Authorization: `Bearer ${token()}` }
        })
        kosList.value = data.data
      } catch { showToast('Gagal memuat data kos.', 'error') }
      finally { loading.value = false }
    }

    const fetchKamar = async (kosId) => {
      loadingKamar[kosId] = true
      try {
        const { data } = await axios.get(`http://127.0.0.1:8000/api/owner/kos/${kosId}/kamar`, {
          headers: { Authorization: `Bearer ${token()}` }
        })
        kamarData[kosId] = data.data
      } catch { showToast('Gagal memuat kamar.', 'error') }
      finally { loadingKamar[kosId] = false }
    }

    const togglePanel = (kosId) => {
      if (openPanels.value.includes(kosId)) {
        openPanels.value = openPanels.value.filter(id => id !== kosId)
      } else {
        openPanels.value.push(kosId)
        if (!kamarData[kosId]) fetchKamar(kosId)
      }
    }

    const aktivasiKamar = async (kamar, kosId) => {
      aktivasiLoading[kamar.id] = true
      try {
        await axios.post(`http://127.0.0.1:8000/api/owner/kamar/${kamar.id}/aktivasi`, {}, {
          headers: { Authorization: `Bearer ${token()}` }
        })
        showToast(`Kamar ${kamar.nomor_kamar} berhasil diaktifkan kembali!`)
        await fetchKamar(kosId); await fetchKos()
      } catch (e) { showToast(e.response?.data?.message ?? 'Gagal mengaktifkan kamar.', 'error') }
      finally { aktivasiLoading[kamar.id] = false }
    }

    const editKos = (kos) => {
      editTarget.value = kos
      editForm.nama_kos = kos.nama_kos; editForm.alamat = kos.alamat
      editForm.harga_per_bulan = kos.harga_per_bulan
      editForm.deskripsi = kos.deskripsi ?? ''; editForm.gambar = null
      editError.value = ''; editModal.value = true
    }

    const onGambarChange = (e) => { editForm.gambar = e.target.files[0] ?? null }

    const submitEdit = async () => {
      editLoading.value = true; editError.value = ''
      try {
        const fd = new FormData()
        fd.append('nama_kos', editForm.nama_kos); fd.append('alamat', editForm.alamat)
        fd.append('harga_per_bulan', editForm.harga_per_bulan); fd.append('deskripsi', editForm.deskripsi)
        if (editForm.gambar) fd.append('gambar', editForm.gambar)
        await axios.post(`http://127.0.0.1:8000/api/owner/kos/${editTarget.value.id}`, fd, {
          headers: { Authorization: `Bearer ${token()}`, 'Content-Type': 'multipart/form-data' }
        })
        showToast('Kos berhasil diupdate!'); editModal.value = false; await fetchKos()
      } catch (e) { editError.value = e.response?.data?.message ?? 'Gagal mengupdate kos.' }
      finally { editLoading.value = false }
    }

    const confirmHapus = (kos) => {
      hapusTarget.value = kos; hapusError.value = ''; hapusModal.value = true
    }

    const submitHapus = async () => {
      hapusLoading.value = true; hapusError.value = ''
      try {
        await axios.delete(`http://127.0.0.1:8000/api/owner/kos/${hapusTarget.value.id}`, {
          headers: { Authorization: `Bearer ${token()}` }
        })
        showToast('Kos berhasil dihapus.'); hapusModal.value = false; await fetchKos()
      } catch (e) { hapusError.value = e.response?.data?.message ?? 'Gagal menghapus kos.' }
      finally { hapusLoading.value = false }
    }

    const formatDate = (d) => {
      if (!d) return '—'
      return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    }

    const handleLogout = async () => { await authStore.logout(); router.push('/login') }

    onMounted(fetchKos)

    return {
      kosList, loading, openPanels, kamarData, loadingKamar, aktivasiLoading,
      totalKamar, totalBooked, totalAvailable,
      togglePanel, aktivasiKamar, formatDate,
      editModal, editForm, editLoading, editError, editKos, onGambarChange, submitEdit,
      hapusModal, hapusTarget, hapusLoading, hapusError, confirmHapus, submitHapus,
      toast, handleLogout,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.page { min-height: 100vh; background: #faf8f4; font-family: 'DM Sans', sans-serif; color: #1a1a1a; }

/* ─── NAVBAR ─── */
.navbar {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 2.5rem; background: #faf8f4;
  border-bottom: 1px solid #e8e2d9; position: sticky; top: 0; z-index: 100;
}
.nav-brand { display: flex; align-items: center; gap: 8px; }
.nav-icon  { font-size: 20px; }
.nav-title { font-family: 'Playfair Display', serif; font-size: 18px; color: #1a1a1a; }
.nav-actions { display: flex; align-items: center; gap: 8px; }
.btn-add {
  background: #c17f3e; color: #fff; padding: 8px 18px; border-radius: 8px;
  font-size: 13.5px; font-weight: 500; text-decoration: none; border: none;
  cursor: pointer; transition: background .2s; white-space: nowrap;
}
.btn-add:hover { background: #a96c30; }
.btn-logout {
  background: none; border: 1px solid #d4c5b0; color: #6b5b47;
  padding: 8px 16px; border-radius: 8px; font-size: 13.5px; cursor: pointer;
  font-family: 'DM Sans', sans-serif; transition: background .15s; white-space: nowrap;
}
.btn-logout:hover { background: #f0ebe3; }

/* ─── HEADER ─── */
.dash-header {
  position: relative; overflow: hidden;
  background: #1e1a14; padding: 4rem 2.5rem 3rem; color: #faf8f4;
}
.dash-header-content { position: relative; z-index: 2; max-width: 600px; }
.dash-eyebrow {
  font-size: 11px; letter-spacing: .14em; text-transform: uppercase;
  color: #c17f3e; font-weight: 500; margin-bottom: .75rem;
}
.dash-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 4vw, 2.8rem); line-height: 1.15; margin-bottom: .75rem;
}
.dash-title em { color: #c17f3e; font-style: italic; }
.dash-sub { font-size: 14px; color: #b5a898; font-weight: 300; line-height: 1.7; }
.header-deco { position: absolute; inset: 0; z-index: 1; }
.deco-circle { position: absolute; border-radius: 50%; opacity: .07; background: #c17f3e; }
.c1 { width: 360px; height: 360px; right: -60px; top: -100px; }
.c2 { width: 160px; height: 160px; right: 240px; bottom: -50px; }

/* ─── STATS BAR ─── */
.stats-bar {
  display: flex; align-items: center; gap: 1.5rem;
  padding: 1.1rem 2.5rem; background: #fff;
  border-bottom: 1px solid #e8e2d9; flex-wrap: wrap;
}
.stat-item { display: flex; flex-direction: column; }
.stat-num { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #c17f3e; line-height: 1; }
.stat-label { font-size: 10px; color: #8a7968; text-transform: uppercase; letter-spacing: .07em; margin-top: 3px; }
.stat-divider { width: 1px; height: 30px; background: #e8e2d9; flex-shrink: 0; }

/* ─── STATE ─── */
.state-wrap {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 5rem 2rem; gap: 1rem; color: #8a7968;
}
.spinner {
  width: 36px; height: 36px; border: 3px solid #e8e2d9; border-top-color: #c17f3e;
  border-radius: 50%; animation: spin .7s linear infinite;
}
.spinner.sm { width: 20px; height: 20px; border-width: 2px; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-icon { font-size: 3rem; }
.empty-text { font-size: 15px; }

/* ─── KOS LIST ─── */
.kos-list {
  display: flex; flex-direction: column; gap: 1.5rem;
  padding: 2rem 2.5rem; max-width: 1100px; margin: 0 auto;
}
.kos-panel {
  background: #fff; border: 1px solid #e8e2d9; border-radius: 16px;
  overflow: hidden; animation: fadeUp .5s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ─── PANEL HEAD ─── */
.panel-head { display: flex; }
.panel-thumb {
  width: 200px; min-height: 180px; flex-shrink: 0;
  background: linear-gradient(135deg, #2a2218, #1e1a14);
  background-size: cover; background-position: center;
  position: relative; display: flex; flex-direction: column;
  align-items: center; justify-content: flex-end;
}
.panel-thumb.has-image .thumb-overlay {
  background: linear-gradient(to bottom, rgba(0,0,0,.1), rgba(0,0,0,.6));
}
.thumb-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,.05), rgba(0,0,0,.4)); }
.panel-thumb-content {
  position: relative; z-index: 2; display: flex; flex-direction: column;
  align-items: center; gap: 4px; padding-bottom: 12px;
}
.thumb-emoji { font-size: 2rem; }
.thumb-zona  { font-size: 10px; letter-spacing: .1em; text-transform: uppercase; color: #f0c98a; font-weight: 500; }

.panel-info {
  flex: 1; padding: 1.5rem; display: flex; flex-direction: column;
  gap: .5rem; min-width: 0;
}
.panel-title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.panel-title { font-family: 'Playfair Display', serif; font-size: 20px; color: #1a1a1a; word-break: break-word; }
.panel-alamat { display: flex; align-items: flex-start; gap: 5px; font-size: 13px; color: #8a7968; line-height: 1.5; word-break: break-word; }
.pin-icon { width: 14px; height: 14px; flex-shrink: 0; margin-top: 2px; }
.panel-harga { font-family: 'Playfair Display', serif; font-size: 22px; color: #c17f3e; }
.panel-harga span { font-family: 'DM Sans', sans-serif; font-size: 13px; color: #a09080; }

.badge {
  font-size: 11px; font-weight: 500; padding: 3px 10px;
  border-radius: 20px; text-transform: capitalize; flex-shrink: 0;
}
.badge.aktif    { background: #d1fae5; color: #065f46; }
.badge.pending  { background: #fef3c7; color: #92400e; }
.badge.nonaktif { background: #fee2e2; color: #991b1b; }

.panel-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: .5rem; }
.btn-action {
  padding: 7px 14px; border-radius: 8px; border: none;
  font-family: 'DM Sans', sans-serif; font-size: 13px;
  font-weight: 500; cursor: pointer; transition: all .15s; white-space: nowrap;
}
.btn-action.edit   { background: #f0ebe3; color: #6b5b47; }
.btn-action.edit:hover { background: #e8dfd4; }
.btn-action.danger { background: #fee2e2; color: #991b1b; }
.btn-action.danger:hover { background: #fecaca; }
.btn-action.toggle { background: #1a1a1a; color: #faf8f4; }
.btn-action.toggle:hover { background: #333; }

/* ─── KAMAR SECTION ─── */
.kamar-section { border-top: 1px solid #f0ebe3; background: #faf8f4; padding: 1.5rem; }
.kamar-loading { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #8a7968; }
.kamar-empty   { font-size: 13.5px; color: #a09080; padding: .5rem 0; }
.kamar-grid    { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }

.kamar-card {
  background: #fff; border: 1px solid #e8e2d9; border-radius: 12px;
  padding: 1rem 1.1rem; display: flex; flex-direction: column; gap: .5rem; transition: box-shadow .2s;
}
.kamar-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
.kamar-card.booked    { border-color: #fde68a; background: #fffbeb; }
.kamar-card.available { border-color: #a7f3d0; }

.kamar-head { display: flex; justify-content: space-between; align-items: center; }
.kamar-nomor { font-weight: 500; font-size: 14px; color: #1a1a1a; }
.kamar-badge { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; text-transform: capitalize; }
.kamar-badge.available { background: #d1fae5; color: #065f46; }
.kamar-badge.booked    { background: #fef3c7; color: #92400e; }
.kamar-harga { font-size: 13px; color: #c17f3e; font-weight: 500; }

.kamar-tenant { display: flex; flex-direction: column; gap: 4px; margin-top: 4px; padding-top: 8px; border-top: 1px solid #f0ebe3; }
.tenant-info  { display: flex; gap: 6px; font-size: 12.5px; flex-wrap: wrap; }
.tenant-label { color: #8a7968; white-space: nowrap; }
.tenant-name  { color: #1a1a1a; font-weight: 500; word-break: break-word; }
.tenant-date  { color: #1a1a1a; }

.booking-status { font-size: 11px; font-weight: 600; padding: 1px 7px; border-radius: 20px; text-transform: capitalize; }
.booking-status.pending   { background: #fef3c7; color: #92400e; }
.booking-status.confirmed { background: #d1fae5; color: #065f46; }
.booking-status.completed { background: #e0e7ff; color: #3730a3; }

.btn-aktivasi {
  margin-top: 6px; width: 100%; background: #c17f3e; color: #fff; border: none;
  padding: 8px 0; border-radius: 8px; font-family: 'DM Sans', sans-serif;
  font-size: 13px; font-weight: 500; cursor: pointer; transition: background .2s;
}
.btn-aktivasi:hover:not(:disabled) { background: #a96c30; }
.btn-aktivasi:disabled { opacity: .6; cursor: not-allowed; }
.kamar-kosong-label { font-size: 12.5px; color: #065f46; font-weight: 500; }

/* ─── MODAL ─── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; padding: 1rem;
}
.modal {
  background: #fff; border-radius: 16px; width: 100%; max-width: 480px;
  overflow: hidden; animation: modalIn .25s ease; max-height: 90vh; overflow-y: auto;
}
.modal-sm { max-width: 380px; }
@keyframes modalIn {
  from { opacity: 0; transform: scale(.95) translateY(12px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-head {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #e8e2d9;
  position: sticky; top: 0; background: #fff; z-index: 1;
}
.modal-head h3 { font-family: 'Playfair Display', serif; font-size: 18px; color: #1a1a1a; }
.modal-close { background: none; border: none; font-size: 16px; color: #8a7968; cursor: pointer; padding: 4px; }
.modal-body  { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.form-group  { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12.5px; font-weight: 500; color: #6b5b47; }
.form-input {
  width: 100%; padding: 9px 12px; border: 1px solid #d4c5b0; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: 14px; color: #1a1a1a;
  background: #faf8f4; outline: none; transition: border-color .15s; resize: vertical;
}
.form-input:focus { border-color: #c17f3e; }
.form-error { font-size: 13px; color: #991b1b; background: #fee2e2; padding: 8px 12px; border-radius: 8px; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: 10px; padding: 1rem 1.5rem;
  border-top: 1px solid #e8e2d9; position: sticky; bottom: 0; background: #fff;
}
.btn-cancel {
  background: #f0ebe3; color: #6b5b47; border: none; padding: 9px 18px;
  border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 13.5px; cursor: pointer;
}
.btn-save {
  background: #c17f3e; color: #fff; border: none; padding: 9px 20px; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: 13.5px; font-weight: 500; cursor: pointer; transition: background .15s;
}
.btn-save:hover:not(:disabled) { background: #a96c30; }
.btn-save:disabled { opacity: .6; cursor: not-allowed; }
.btn-hapus {
  background: #dc2626; color: #fff; border: none; padding: 9px 20px; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: 13.5px; font-weight: 500; cursor: pointer;
}
.btn-hapus:hover:not(:disabled) { background: #b91c1c; }
.btn-hapus:disabled { opacity: .6; cursor: not-allowed; }
.hapus-warning { font-size: 14.5px; color: #1a1a1a; line-height: 1.6; }
.hapus-note    { font-size: 13px; color: #92400e; background: #fef3c7; padding: 8px 12px; border-radius: 8px; }

/* ─── TOAST ─── */
.toast {
  position: fixed; bottom: 1.5rem; right: 1.5rem;
  padding: 12px 20px; border-radius: 10px; font-size: 14px; font-weight: 500;
  z-index: 300; box-shadow: 0 4px 20px rgba(0,0,0,.15);
}
.toast.success { background: #1a1a1a; color: #faf8f4; }
.toast.error   { background: #dc2626; color: #fff; }
.toast-enter-active, .toast-leave-active { transition: all .3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(12px); }

/* ─── FOOTER ─── */
.footer { text-align: center; padding: 2rem; font-size: 13px; color: #b5a898; border-top: 1px solid #e8e2d9; margin-top: 2rem; }

/* ════════════════════════════════
   TABLET  ≤ 768px
════════════════════════════════ */
@media (max-width: 768px) {
  .navbar { padding: .85rem 1.25rem; }
  .nav-title { font-size: 16px; }
  .btn-add, .btn-logout { padding: 7px 13px; font-size: 12.5px; }

  .dash-header { padding: 2.5rem 1.25rem 2rem; }
  .dash-sub    { font-size: 13px; }

  /* Stats: 2-kolom grid */
  .stats-bar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem 1rem;
    padding: 1rem 1.25rem;
  }
  .stat-divider { display: none; }
  .stat-num     { font-size: 1.4rem; }

  .kos-list { padding: 1.25rem; gap: 1.25rem; }

  /* Panel: thumb di atas, info di bawah */
  .panel-head   { flex-direction: column; }
  .panel-thumb  { width: 100%; min-height: 140px; }
  .panel-info   { padding: 1.1rem; }
  .panel-title  { font-size: 17px; }
  .panel-harga  { font-size: 19px; }

  .kamar-section { padding: 1rem; }
  .kamar-grid    { grid-template-columns: 1fr 1fr; gap: .75rem; }

  /* Modal: slide dari bawah */
  .modal-overlay { align-items: flex-end; padding: 0; }
  .modal         { border-radius: 20px 20px 0 0; max-width: 100%; max-height: 92vh; }
  .modal-sm      { max-width: 100%; }
}

/* ════════════════════════════════
   MOBILE  ≤ 480px
════════════════════════════════ */
@media (max-width: 480px) {
  .navbar    { padding: .75rem 1rem; }
  .nav-title { font-size: 15px; }
  .btn-add, .btn-logout { padding: 6px 11px; font-size: 12px; }

  .dash-header  { padding: 2rem 1rem 1.5rem; }
  .dash-eyebrow { font-size: 10px; }

  .stats-bar  { padding: .85rem 1rem; gap: .6rem .75rem; }
  .stat-num   { font-size: 1.25rem; }
  .stat-label { font-size: 9px; }

  .kos-list    { padding: 1rem; gap: 1rem; }
  .panel-thumb { min-height: 110px; }
  .thumb-emoji { font-size: 1.6rem; }
  .thumb-zona  { font-size: 9px; }

  .panel-info   { padding: .9rem; gap: .4rem; }
  .panel-title  { font-size: 15.5px; }
  .panel-alamat { font-size: 12px; }
  .panel-harga  { font-size: 17px; }

  /* Tombol aksi: susun vertikal */
  .panel-actions { flex-direction: column; gap: 6px; }
  .btn-action    { width: 100%; text-align: center; padding: 9px 12px; }

  /* Kamar: 1 kolom */
  .kamar-grid    { grid-template-columns: 1fr; }
  .kamar-section { padding: .85rem; }
  .kamar-card    { padding: .85rem; }

  /* Modal footer: tombol penuh */
  .modal-body   { padding: 1.1rem; }
  .modal-head   { padding: 1rem 1.1rem; }
  .modal-footer { padding: .85rem 1.1rem; flex-direction: column; }
  .btn-cancel, .btn-save, .btn-hapus { width: 100%; text-align: center; padding: 11px; }

  /* Toast full width */
  .toast { right: 1rem; left: 1rem; bottom: 1rem; font-size: 13px; text-align: center; }
}

/* ════════════════════════════════
   VERY SMALL  ≤ 360px
════════════════════════════════ */
@media (max-width: 360px) {
  .btn-add, .btn-logout { padding: 6px 9px; font-size: 11.5px; }
  .panel-title { font-size: 14.5px; }
  .stat-num    { font-size: 1.1rem; }
  .kamar-nomor { font-size: 13px; }
}
</style>