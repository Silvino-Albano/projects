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
        <router-link to="/" class="btn-back">← Kembali</router-link>
        <button class="btn-logout" @click="handleLogout">Keluar</button>
      </div>
    </nav>

    <!-- HEADER -->
    <section class="form-hero">
      <div class="hero-deco">
        <div class="deco-circle c1"></div>
        <div class="deco-circle c2"></div>
      </div>
      <div class="hero-inner">
        <p class="hero-eyebrow">Owner Dashboard</p>
        <h1 class="hero-title">Daftarkan Kos <em>Anda</em></h1>
        <p class="hero-sub">Isi informasi lengkap agar penyewa mudah menemukan kos Anda.</p>
      </div>
    </section>

    <!-- FORM -->
    <div class="form-wrap">
      <form @submit.prevent="submitKos" novalidate>

        <!-- UPLOAD GAMBAR -->
        <div class="section-block">
          <h2 class="section-label">Foto Kos</h2>
          <div
            class="upload-zone"
            :class="{ 'upload-zone--drag': isDragging, 'upload-zone--filled': previewUrl }"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="handleDrop"
            @click="$refs.fileInput.click()"
          >
            <input ref="fileInput" type="file" accept="image/*" hidden @change="handleFileChange" />

            <template v-if="previewUrl">
              <img :src="previewUrl" class="preview-img" alt="Preview" />
              <button type="button" class="remove-img" @click.stop="removeImage">✕</button>
            </template>
            <template v-else>
              <div class="upload-icon">🏡</div>
              <p class="upload-text">Klik atau drag & drop foto kos</p>
              <p class="upload-hint">JPG, PNG, WEBP — maks 2MB</p>
            </template>
          </div>
          <p class="ferr" v-if="errors.gambar">{{ errors.gambar }}</p>
        </div>

        <!-- INFO DASAR -->
        <div class="section-block">
          <h2 class="section-label">Informasi Dasar</h2>
          <div class="fields-grid">

            <!-- Nama Kos -->
            <div class="field full-width" :class="{ 'field--error': errors.nama_kos }">
              <label>Nama Kos</label>
              <div class="input-wrap">
                <svg class="iico" viewBox="0 0 20 20" fill="none"><path d="M3 9l7-6 7 6v9a1 1 0 01-1 1H4a1 1 0 01-1-1V9z" stroke="currentColor" stroke-width="1.4"/><path d="M8 19V12h4v7" stroke="currentColor" stroke-width="1.4"/></svg>
                <input v-model="form.nama_kos" type="text" placeholder="Contoh: Kos Farol Indah" @blur="validate('nama_kos')" />
              </div>
              <span class="ferr" v-if="errors.nama_kos">{{ errors.nama_kos }}</span>
            </div>

            <!-- Zona -->
            <div class="field" :class="{ 'field--error': errors.zona_id }">
              <label>Zona</label>
              <div class="input-wrap">
                <svg class="iico" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.4"/><path d="M10 3v7l4 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                <select v-model="form.zona_id" @blur="validate('zona_id')">
                  <option value="" disabled>Pilih zona...</option>
                  <option v-for="z in zonas" :key="z.id" :value="z.id">{{ z.nama_zona }}</option>
                </select>
              </div>
              <span class="ferr" v-if="errors.zona_id">{{ errors.zona_id }}</span>
            </div>

            <!-- Harga -->
            <div class="field" :class="{ 'field--error': errors.harga_per_bulan }">
              <label>Harga per Bulan ($)</label>
              <div class="input-wrap">
                <svg class="iico" viewBox="0 0 20 20" fill="none"><path d="M10 3v14M7 5.5h4.5a2 2 0 010 4H8.5a2 2 0 000 4H13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                <input v-model="form.harga_per_bulan" type="number" min="0" placeholder="150" @blur="validate('harga_per_bulan')" />
              </div>
              <span class="ferr" v-if="errors.harga_per_bulan">{{ errors.harga_per_bulan }}</span>
            </div>

            <!-- Alamat -->
            <div class="field full-width" :class="{ 'field--error': errors.alamat }">
              <label>Alamat Lengkap</label>
              <div class="input-wrap">
                <svg class="iico" style="top:14px" viewBox="0 0 20 20" fill="none"><path d="M10 2a6 6 0 016 6c0 4-6 10-6 10S4 12 4 8a6 6 0 016-6z" stroke="currentColor" stroke-width="1.4"/><circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.4"/></svg>
                <textarea v-model="form.alamat" rows="2" placeholder="Rua de Farol, Dili" @blur="validate('alamat')"></textarea>
              </div>
              <span class="ferr" v-if="errors.alamat">{{ errors.alamat }}</span>
            </div>

            <!-- Deskripsi -->
            <div class="field full-width">
              <label>Deskripsi <span class="optional">opsional</span></label>
              <div class="input-wrap">
                <svg class="iico" style="top:14px" viewBox="0 0 20 20" fill="none"><path d="M4 6h12M4 10h8M4 14h6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                <textarea v-model="form.deskripsi" rows="3" placeholder="Ceritakan fasilitas, suasana, dan keunggulan kos Anda..."></textarea>
              </div>
            </div>

          </div>
        </div>

        <!-- KOORDINAT -->
        <div class="section-block">
          <h2 class="section-label">
            Koordinat Lokasi
            <span class="section-hint">— untuk tampil di peta</span>
          </h2>
          <div class="fields-grid">
            <div class="field" :class="{ 'field--error': errors.latitude }">
              <label>Latitude</label>
              <div class="input-wrap">
                <svg class="iico" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.4"/><path d="M3 10h14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                <input v-model="form.latitude" type="number" step="any" placeholder="-8.5586" @blur="validate('latitude')" />
              </div>
              <span class="ferr" v-if="errors.latitude">{{ errors.latitude }}</span>
            </div>
            <div class="field" :class="{ 'field--error': errors.longitude }">
              <label>Longitude</label>
              <div class="input-wrap">
                <svg class="iico" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.4"/><path d="M10 3v14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                <input v-model="form.longitude" type="number" step="any" placeholder="125.5736" @blur="validate('longitude')" />
              </div>
              <span class="ferr" v-if="errors.longitude">{{ errors.longitude }}</span>
            </div>
          </div>
          <p class="coord-tip">
            💡 Buka
            <a href="https://maps.google.com" target="_blank">Google Maps</a>,
            klik kanan lokasi kos → salin koordinat.
          </p>
        </div>

        <!-- SERVER ERROR -->
        <div class="alert" v-if="errorMsg">
          <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.4"/><path d="M10 6v4M10 13.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          {{ errorMsg }}
        </div>

        <!-- SUCCESS -->
        <div class="alert-success" v-if="successMsg">
          <svg viewBox="0 0 20 20" fill="none"><path d="M5 10l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          {{ successMsg }}
        </div>
        <!-- SECTION KAMAR — taruh sebelum tombol submit -->
<div class="section-block">
  <h2 class="section-label">
    Daftar Kamar
    <span class="section-hint">— opsional, bisa ditambah nanti</span>
  </h2>

  <!-- List kamar yang sudah ditambah -->
  <div class="kamar-list">
    <div v-for="(k, i) in form.kamar" :key="i" class="kamar-row">
      <div class="kamar-num-wrap">
        <label>Nomor Kamar</label>
        <input v-model="k.nomor_kamar" placeholder="Contoh: A1" />
      </div>
      <div class="kamar-harga-wrap">
        <label>Harga <span class="optional">opsional</span></label>
        <input v-model="k.harga" type="number" :placeholder="form.harga_per_bulan || '0'" />
      </div>
      <div class="kamar-status-wrap">
        <label>Status</label>
        <select v-model="k.status">
          <option value="available">Tersedia</option>
          <option value="booked">Terisi</option>
        </select>
      </div>
      <button type="button" class="btn-remove-kamar" @click="removeKamar(i)">✕</button>
    </div>
  </div>

  <button type="button" class="btn-add-kamar" @click="addKamar">
    + Tambah Kamar
  </button>
</div>

        <!-- SUBMIT -->
        <button type="submit" class="btn-submit" :disabled="loading">
          <span>{{ loading ? 'Menyimpan...' : 'Daftarkan Kos' }}</span>
          <span class="spinner" v-if="loading"></span>
          <svg v-else viewBox="0 0 20 20" fill="none" style="width:16px;height:16px">
            <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

      </form>
    </div>

    <footer class="footer">© 2025 KosDili · Dili, Timor-Leste</footer>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../store/auth.js'

export default {
  setup() {
    const router    = useRouter()
    const authStore = useAuthStore()

    const loading    = ref(false)
    const errorMsg   = ref('')
    const successMsg = ref('')
    const isDragging = ref(false)
    const previewUrl = ref(null)
    const imageFile  = ref(null)
    const zonas      = ref([])
    const fileInput  = ref(null)

    const form = reactive({
      nama_kos:        '',
      zona_id:         '',
      harga_per_bulan: '',
      alamat:          '',
      deskripsi:       '',
      latitude:        '',
      longitude:       '',
      kamar: [],
    })
    const addKamar = () => {
  form.kamar.push({
    nomor_kamar: '',
    harga:       '',
    status:      'available',
  })
}
const removeKamar = (index) => {
  form.kamar.splice(index, 1)
}

    const errors = reactive({
      nama_kos: '', zona_id: '', harga_per_bulan: '',
      alamat: '', latitude: '', longitude: '', gambar: '',
    })

    // Ambil daftar zona
    const fetchZonas = async () => {
      try {
        const res = await api.get('/zonas')
        zonas.value = res.data.data
      } catch {
        console.error('Gagal load zona')
      }
    }

    // Validasi per field
    const validate = (field) => {
      if (field === 'nama_kos')
        errors.nama_kos = !form.nama_kos ? 'Nama kos wajib diisi' : ''
      if (field === 'zona_id')
        errors.zona_id = !form.zona_id ? 'Zona wajib dipilih' : ''
      if (field === 'harga_per_bulan')
        errors.harga_per_bulan = !form.harga_per_bulan ? 'Harga wajib diisi' : ''
      if (field === 'alamat')
        errors.alamat = !form.alamat ? 'Alamat wajib diisi' : ''
      if (field === 'latitude')
        errors.latitude = form.latitude && (form.latitude < -90 || form.latitude > 90)
          ? 'Latitude harus antara -90 dan 90' : ''
      if (field === 'longitude')
        errors.longitude = form.longitude && (form.longitude < -180 || form.longitude > 180)
          ? 'Longitude harus antara -180 dan 180' : ''
    }

    // Handle upload gambar
    const handleFileChange = (e) => {
      const file = e.target.files[0]
      if (file) processImage(file)
    }

    const handleDrop = (e) => {
      isDragging.value = false
      const file = e.dataTransfer.files[0]
      if (file) processImage(file)
    }

    const processImage = (file) => {
      errors.gambar = ''
      if (file.size > 2 * 1024 * 1024) {
        errors.gambar = 'Ukuran gambar maks 2MB'
        return
      }
      if (!file.type.startsWith('image/')) {
        errors.gambar = 'File harus berupa gambar'
        return
      }
      imageFile.value  = file
      previewUrl.value = URL.createObjectURL(file)
    }

    const removeImage = () => {
      previewUrl.value = null
      imageFile.value  = null
      if (fileInput.value) fileInput.value.value = ''
    }

    // Submit form
    const submitKos = async () => {
      // Validasi semua field wajib
      ['nama_kos', 'zona_id', 'harga_per_bulan', 'alamat', 'latitude', 'longitude'].forEach(validate)
      if (Object.values(errors).some(e => e)) return

      loading.value  = true
      errorMsg.value = ''
      successMsg.value = ''

      try {
        // Pakai FormData agar bisa kirim gambar
        const payload = new FormData()
          Object.entries(form).forEach(([k, v]) => {
            if (k === 'kamar') {
              // FormData tidak support array of objects — kirim sebagai JSON string
              payload.append('kamar', JSON.stringify(v))
            } else if (v !== '') {
              payload.append(k, v)
            }
          })
          if (imageFile.value) payload.append('gambar', imageFile.value)

          await api.post('/kos', payload, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        successMsg.value = 'Kos berhasil didaftarkan! Menunggu persetujuan admin.'

        // Reset form
        Object.keys(form).forEach(k => form[k] = '')
        removeImage()

        // Redirect ke home setelah 2 detik
        setTimeout(() => router.push('/'), 2000)

      } catch (err) {
        const serverErrors = err.response?.data?.errors
        if (serverErrors) {
          Object.keys(serverErrors).forEach(k => {
            if (errors[k] !== undefined) errors[k] = serverErrors[k][0]
          })
        } else {
          errorMsg.value = err.response?.data?.message || 'Gagal mendaftarkan kos.'
        }
      } finally {
        loading.value = false
      }
    }

    const handleLogout = async () => {
      await authStore.logout()
      router.push('/login')
    }

    onMounted(fetchZonas)

    return {
      form, errors, zonas,
      loading, errorMsg, successMsg,
      isDragging, previewUrl, fileInput,
      handleFileChange, handleDrop, removeImage,
      validate, submitKos, handleLogout,
      addKamar,    // ← tambah ini
      removeKamar, // ← tambah ini
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap');
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
.page { min-height: 100vh; background: #faf8f4; font-family: 'DM Sans', sans-serif; color: #1a1a1a; }

/* NAVBAR */
.navbar { display:flex; justify-content:space-between; align-items:center; padding:1rem 2.5rem; background:#faf8f4; border-bottom:1px solid #e8e2d9; position:sticky; top:0; z-index:10; }
.nav-brand-link { display:flex; align-items:center; gap:8px; text-decoration:none; }
.nav-icon { font-size:20px; }
.nav-title { font-family:'Playfair Display',serif; font-size:18px; color:#1a1a1a; }
.nav-actions { display:flex; align-items:center; gap:10px; }
.btn-back { font-size:13.5px; color:#6b5b47; text-decoration:none; padding:8px 16px; border:1px solid #d4c5b0; border-radius:8px; transition:background .15s; }
.btn-back:hover { background:#f0ebe3; }
.btn-logout { background:none; border:1px solid #d4c5b0; color:#6b5b47; padding:8px 16px; border-radius:8px; font-size:13.5px; cursor:pointer; font-family:'DM Sans',sans-serif; }
.btn-logout:hover { background:#f0ebe3; }

/* HERO */
.form-hero { position:relative; overflow:hidden; background:#1e1a14; padding:3rem 2.5rem 3rem; color:#faf8f4; }
.hero-deco { position:absolute; inset:0; }
.deco-circle { position:absolute; border-radius:50%; opacity:.07; background:#c17f3e; }
.c1 { width:340px; height:340px; right:-50px; top:-90px; }
.c2 { width:150px; height:150px; right:220px; bottom:-40px; }
.hero-inner { position:relative; z-index:2; max-width:700px; }
.hero-eyebrow { font-size:11px; letter-spacing:.12em; text-transform:uppercase; color:#c17f3e; font-weight:500; margin-bottom:.75rem; }
.hero-title { font-family:'Playfair Display',serif; font-size:clamp(1.8rem,4vw,2.8rem); line-height:1.2; color:#faf8f4; margin-bottom:.6rem; }
.hero-title em { color:#c17f3e; font-style:italic; }
.hero-sub { font-size:14px; color:#b5a898; font-weight:300; line-height:1.7; }

/* FORM WRAP */
.form-wrap { max-width: 700px; margin: 2.5rem auto; padding: 0 2rem 3rem; }

/* SECTION BLOCK */
.section-block { margin-bottom: 2rem; }
.section-label {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; color: #1a1a1a;
  margin-bottom: 1rem;
  padding-bottom: .6rem;
  border-bottom: 1px solid #e8e2d9;
  display: flex; align-items: baseline; gap: 8px;
}
.section-hint { font-family: 'DM Sans', sans-serif; font-size: 12.5px; color: #a09080; font-weight: 300; }

/* UPLOAD ZONE */
.upload-zone {
  border: 2px dashed #d4c5b0;
  border-radius: 14px;
  height: 200px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 8px;
  cursor: pointer; transition: border-color .2s, background .2s;
  position: relative; overflow: hidden;
  background: #fff;
}
.upload-zone:hover { border-color: #c17f3e; background: #fdf8f3; }
.upload-zone--drag { border-color: #c17f3e; background: #fdf8f3; }
.upload-zone--filled { border-style: solid; border-color: #e8e2d9; }
.upload-icon { font-size: 2.5rem; }
.upload-text { font-size: 14px; color: #6b5b47; font-weight: 500; }
.upload-hint { font-size: 12px; color: #a09080; }
.preview-img { width: 100%; height: 100%; object-fit: cover; }
.remove-img {
  position: absolute; top: 10px; right: 10px;
  background: rgba(0,0,0,.55); color: #fff;
  border: none; border-radius: 50%; width: 28px; height: 28px;
  cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center;
}

/* FIELDS GRID */
.fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.full-width { grid-column: 1 / -1; }

/* FIELD */
.field { display: flex; flex-direction: column; gap: 5px; }
.field label { font-size: 11.5px; font-weight: 500; color: #9ca3af; letter-spacing: .04em; text-transform: uppercase; }
.optional { font-size: 10.5px; color: #c4b8a8; text-transform: none; letter-spacing: 0; margin-left: 4px; }

.input-wrap { position: relative; display: flex; align-items: flex-start; }
.iico { position: absolute; left: 13px; top: 14px; width: 15px; height: 15px; color: #4b5563; pointer-events: none; flex-shrink: 0; }

.input-wrap input,
.input-wrap select,
.input-wrap textarea {
  width: 100%; background: #fff; border: 1px solid #e8e2d9;
  border-radius: 10px; color: #1a1a1a;
  font-family: 'DM Sans', sans-serif; font-size: 13.5px; font-weight: 300;
  padding: 11px 14px 11px 40px; outline: none;
  transition: border-color .2s, box-shadow .2s;
  resize: vertical;
}
.input-wrap input::placeholder,
.input-wrap textarea::placeholder { color: #c4b8a8; }
.input-wrap input:focus,
.input-wrap select:focus,
.input-wrap textarea:focus {
  border-color: #c17f3e;
  box-shadow: 0 0 0 3px rgba(193,127,62,.1);
}
.field--error .input-wrap input,
.field--error .input-wrap select,
.field--error .input-wrap textarea { border-color: #fca5a5; }

.ferr { font-size: 11.5px; color: #ef4444; font-weight: 300; }

/* COORD TIP */
.coord-tip { font-size: 12.5px; color: #a09080; margin-top: .6rem; font-weight: 300; }
.coord-tip a { color: #c17f3e; text-decoration: none; }
.coord-tip a:hover { text-decoration: underline; }

/* ALERTS */
.alert {
  display: flex; align-items: center; gap: 9px;
  background: #fef2f2; border: 1px solid #fecaca;
  border-radius: 10px; padding: 12px 14px;
  font-size: 13.5px; color: #dc2626; margin-bottom: 1rem;
}
.alert svg { width: 16px; height: 16px; flex-shrink: 0; }
.alert-success {
  display: flex; align-items: center; gap: 9px;
  background: #f0fdf4; border: 1px solid #bbf7d0;
  border-radius: 10px; padding: 12px 14px;
  font-size: 13.5px; color: #16a34a; margin-bottom: 1rem;
}
.alert-success svg { width: 16px; height: 16px; flex-shrink: 0; }

/* SUBMIT */
.btn-submit {
  width: 100%; height: 50px;
  background: #1e1a14; color: #faf8f4;
  border: none; border-radius: 12px;
  font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 500;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;
  transition: background .2s, transform .1s;
  letter-spacing: .01em;
}
.btn-submit:hover:not(:disabled) { background: #c17f3e; }
.btn-submit:active:not(:disabled) { transform: scale(.99); }
.btn-submit:disabled { opacity: .55; cursor: not-allowed; }

.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(250,248,244,.3);
  border-top-color: #faf8f4;
  border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.footer { text-align: center; padding: 2rem; font-size: 13px; color: #b5a898; border-top: 1px solid #e8e2d9; }

@media (max-width: 600px) {
  .fields-grid { grid-template-columns: 1fr; }
  .full-width { grid-column: 1; }
  .form-wrap { padding: 0 1.25rem 2rem; }
}

.kamar-list { display: flex; flex-direction: column; gap: .75rem; margin-bottom: 1rem; }

.kamar-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: .75rem;
  align-items: end;
  background: #fff;
  border: 1px solid #e8e2d9;
  border-radius: 12px;
  padding: 1rem;
}

.kamar-row label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; display: block; margin-bottom: 4px; }
.kamar-row input,
.kamar-row select {
  width: 100%; height: 40px; border: 1px solid #e8e2d9;
  border-radius: 8px; padding: 0 12px;
  font-family: 'DM Sans', sans-serif; font-size: 13.5px;
  outline: none; background: #faf8f4;
}
.kamar-row input:focus,
.kamar-row select:focus { border-color: #c17f3e; }

.btn-remove-kamar {
  width: 36px; height: 36px;
  background: #fee2e2; border: none; border-radius: 8px;
  color: #dc2626; cursor: pointer; font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.btn-remove-kamar:hover { background: #fecaca; }

.btn-add-kamar {
  width: 100%; height: 44px;
  background: transparent; border: 2px dashed #d4c5b0;
  border-radius: 12px; color: #8a7968;
  font-family: 'DM Sans', sans-serif; font-size: 13.5px;
  cursor: pointer; transition: all .2s;
}
.btn-add-kamar:hover { border-color: #c17f3e; color: #c17f3e; background: #fdf8f3; }
</style>