<template>
  <div class="page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-brand">
        <span class="nav-icon">🏠</span>
        <span class="nav-title">KosDili</span>
      </div>
     <div class="nav-actions">
      <template v-if="isLoggedIn">
        <!-- owner det -->
         <router-link
          v-if="userRole === 'owner' || userRole === 'admin'"
          to="/owner/bookings"
          class="btn-add"
        >
          🏚 Kelola Booking
        </router-link>
        <!-- admin det mak hare -->
         <router-link
          v-if="userRole === 'admin'"
          to="/admin/kos"
          class="btn-add"
        >
          🏚 Kelola Kos
        </router-link>

        <!-- ✅ Hanya owner & admin yang lihat tombol ini -->
        <router-link
          v-if="userRole === 'owner' || userRole === 'admin'"
          to="/add-kos"
          class="btn-add"
        >
          + Tambah Kos
        </router-link>
        <router-link
        v-if="userRole === 'owner' || userRole === 'admin'"
        to="/owner/dashboard"
        class="btn-add"
      >
        🏠 Dashboard Saya
      </router-link>
        <button class="btn-logout" @click="handleLogout">Keluar</button>
      </template>
          <router-link v-else to="/login" class="btn-login">Masuk</router-link>
           <LangSwitcher />
    </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
      <div class="hero-content">
        <p class="hero-eyebrow">Timor-Leste · Dili</p>
        <h1 class="hero-title"><em>{{ $t('home.title') }}</em></h1>
        <p class="hero-sub">{{ $t('home.hero') }}</p>
<button>{{ $t('detail.bookNow') }}</button>
<span>{{ $t('detail.available') }}</span>
<!-- <p>{{ $t('common.loading') }}</p> -->
        <div class="hero-stats">
          <div class="stat">
            <span class="stat-num">{{ kosStore.kosList.length }}</span>
            <span class="stat-label">Kos Tersedia</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat">
            <span class="stat-num">5</span>
            <span class="stat-label">Zona Dili</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat">
            <span class="stat-num">24/7</span>
            <span class="stat-label">Support</span>
          </div>
        </div>
      </div>
      <div class="hero-deco">
        <div class="deco-circle c1"></div>
        <div class="deco-circle c2"></div>
        <div class="deco-circle c3"></div>
      </div>
    </section>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <span class="filter-label">Filter zona:</span>
      <button
        class="filter-chip"
        :class="{ active: selectedZona === '' }"
        @click="selectedZona = ''"
      >Semua</button>
      <button
        v-for="zona in uniqueZonas"
        :key="zona"
        class="filter-chip"
        :class="{ active: selectedZona === zona }"
        @click="selectedZona = zona"
      >{{ zona }}</button>
    </div>

    <!-- LOADING -->
    <div v-if="kosStore.loading" class="state-wrap">
      <div class="spinner"></div>
      <p>Memuat data kos...</p>
    </div>

    <!-- EMPTY -->
    <div v-else-if="filteredKos.length === 0" class="state-wrap">
      <div class="empty-icon">🏠</div>
      <p class="empty-text">Belum ada kos tersedia di zona ini.</p>
    </div>

    <!-- GRID KOS -->
    <div v-else class="kos-grid">
      <div
        v-for="(kos, i) in filteredKos"
        :key="kos.id"
        class="kos-card"
        :style="{ animationDelay: i * 0.07 + 's' }"
      >
        <!-- Badge status -->
        <span class="badge" :class="kos.status">{{ kos.status }}</span>

        <!-- Ilustrasi -->
       <div
          class="card-thumb"
          :style="kos.gambar
            ? { backgroundImage: `url(http://127.0.0.1:8000/storage/${kos.gambar})` }
            : {}"
          :class="{ 'card-thumb--image': kos.gambar }"
        >
          <!-- Overlay gelap agar teks tetap terbaca -->
          <div class="thumb-overlay"></div>

          <!-- Konten di atas gambar -->
          <div class="thumb-content">
            <div class="thumb-icon" v-if="!kos.gambar">🏡</div>
            <div class="thumb-zona">{{ kos.zona?.nama_zona ?? '—' }}</div>
          </div>
        </div>

        <div class="card-body">
          <h3 class="card-title">{{ kos.nama_kos }}</h3>
          <p class="card-alamat">
            <svg class="pin-icon" viewBox="0 0 20 20" fill="none">
              <path d="M10 2a6 6 0 016 6c0 4-6 10-6 10S4 12 4 8a6 6 0 016-6z" stroke="currentColor" stroke-width="1.4"/>
              <circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.4"/>
            </svg>
            {{ kos.alamat }}
          </p>
          <p class="card-desc" v-if="kos.deskripsi">{{ kos.deskripsi }}</p>

          <div class="card-footer">
            <div class="card-harga">
              <span class="harga-val">${{ Number(kos.harga_per_bulan).toFixed(0) }}</span>
              <span class="harga-unit">/bulan</span>
            </div>
            <router-link :to="`/kos/${kos.id}`" class="btn-lihat">
              Lihat
              <svg viewBox="0 0 20 20" fill="none">
                <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
      <p>© 2026 KosDili · Dili, Timor-Leste</p>
    </footer>

  </div>
</template>

<script>
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useKosStore }  from '../store/index.js'   // ✅ path benar
import { useAuthStore } from '../store/auth.js'
import LangSwitcher from '../components/LangSwitcher.vue'


export default {
   components: { LangSwitcher },

 setup() {
  const kosStore     = useKosStore()
  const authStore    = useAuthStore()
  const router       = useRouter()
  const selectedZona = ref('')

  const isLoggedIn = computed(() => !!localStorage.getItem('token'))

  const userRole = computed(() => {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    return user.role || ''
  })

  const uniqueZonas = computed(() => {
    const set = new Set(
      kosStore.kosList.map(k => k.zona?.nama_zona).filter(Boolean)
    )
    return [...set]   // ← return array, bukan object
  })

  const filteredKos = computed(() => {
    if (!selectedZona.value) return kosStore.kosList
    return kosStore.kosList.filter(
      k => k.zona?.nama_zona === selectedZona.value
    )
  })

  const handleLogout = async () => {
    await authStore.logout()
    router.push('/login')
  }

  onMounted(() => {
    kosStore.fetchKos()
  })

  // ✅ return di sini — di luar semua computed
  return {
    kosStore,
    isLoggedIn,
    userRole,
    selectedZona,
    uniqueZonas,
    filteredKos,
    handleLogout,
  }
}
}



</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.page {
  min-height: 100vh;
  background: #faf8f4;
  font-family: 'DM Sans', sans-serif;
  color: #1a1a1a;
}

/* NAVBAR */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2.5rem;
  background: #faf8f4;
  border-bottom: 1px solid #e8e2d9;
  position: sticky;
  top: 0;
  z-index: 10;
}
.nav-brand { display: flex; align-items: center; gap: 8px; }
.nav-icon { font-size: 20px; }
.nav-title { font-family: 'Playfair Display', serif; font-size: 18px; color: #1a1a1a; }
.nav-actions { display: flex; align-items: center; gap: 10px; }
.btn-add {
  background: #c17f3e;
  color: #fff;
  padding: 8px 18px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 500;
  text-decoration: none;
  transition: background .2s;
}
.btn-add:hover { background: #a96c30; }
.btn-logout {
  background: none;
  border: 1px solid #d4c5b0;
  color: #6b5b47;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13.5px;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: background .15s;
}
.btn-logout:hover { background: #f0ebe3; }
.btn-login {
  background: #1a1a1a;
  color: #faf8f4;
  padding: 8px 20px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 500;
  text-decoration: none;
  transition: opacity .2s;
}
.btn-login:hover { opacity: .8; }

/* HERO */
.hero {
  position: relative;
  overflow: hidden;
  background: #1e1a14;
  padding: 5rem 2.5rem 4rem;
  color: #faf8f4;
}
.hero-content { position: relative; z-index: 2; max-width: 620px; }
.hero-eyebrow {
  font-size: 12px;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #c17f3e;
  font-weight: 500;
  margin-bottom: 1rem;
}
.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.2rem, 5vw, 3.5rem);
  line-height: 1.15;
  margin-bottom: 1rem;
  color: #faf8f4;
}
.hero-title em { color: #c17f3e; font-style: italic; }
.hero-sub { font-size: 15px; color: #b5a898; font-weight: 300; line-height: 1.7; margin-bottom: 2rem; }

.hero-stats { display: flex; align-items: center; gap: 1.5rem; }
.stat { display: flex; flex-direction: column; }
.stat-num { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #faf8f4; }
.stat-label { font-size: 11px; color: #7a6e65; text-transform: uppercase; letter-spacing: .08em; margin-top: 2px; }
.stat-divider { width: 1px; height: 36px; background: #3a3228; }

/* Deco circles */
.hero-deco { position: absolute; inset: 0; z-index: 1; }
.deco-circle {
  position: absolute;
  border-radius: 50%;
  opacity: .08;
  background: #c17f3e;
}
.c1 { width: 420px; height: 420px; right: -80px; top: -120px; }
.c2 { width: 200px; height: 200px; right: 260px; bottom: -60px; }
.c3 { width: 80px; height: 80px; right: 180px; top: 40px; opacity: .12; }

/* FILTER BAR */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding: 1.25rem 2.5rem;
  background: #faf8f4;
  border-bottom: 1px solid #e8e2d9;
}
.filter-label { font-size: 12.5px; color: #8a7968; font-weight: 500; margin-right: 4px; }
.filter-chip {
  padding: 5px 14px;
  border-radius: 20px;
  border: 1px solid #d4c5b0;
  background: transparent;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  color: #6b5b47;
  cursor: pointer;
  transition: all .15s;
}
.filter-chip:hover { border-color: #c17f3e; color: #c17f3e; }
.filter-chip.active { background: #c17f3e; border-color: #c17f3e; color: #fff; }

/* STATE */
.state-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 5rem 2rem;
  gap: 1rem;
  color: #8a7968;
}
.spinner {
  width: 36px; height: 36px;
  border: 3px solid #e8e2d9;
  border-top-color: #c17f3e;
  border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.empty-icon { font-size: 3rem; }
.empty-text { font-size: 15px; }

/* KOS GRID */
.kos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  padding: 2.5rem;
}

.kos-card {
  background: #fff;
  border: 1px solid #e8e2d9;
  border-radius: 16px;
  overflow: hidden;        /* ← sudah ada, pastikan tetap ada */
  position: relative;
  animation: fadeUp .5s ease both;
  transition: transform .2s, box-shadow .2s;
}
.kos-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0,0,0,.08);
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Badge */
.badge {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 11px;
  font-weight: 500;
  padding: 3px 10px;
  border-radius: 20px;
  text-transform: capitalize;
  z-index: 2;
}
.badge.aktif    { background: #d1fae5; color: #065f46; }
.badge.pending  { background: #fef3c7; color: #92400e; }
.badge.nonaktif { background: #fee2e2; color: #991b1b; }

/* Thumb */
/* Thumb */
.card-thumb {
  position: relative;
  height: 160px;
  background: linear-gradient(135deg, #2a2218 0%, #1e1a14 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  overflow: hidden;

  /* Jika ada gambar */
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  transition: transform .3s ease;
}

/* Zoom gambar saat card di-hover */
.kos-card:hover .card-thumb {
  transform: scale(1.03);
}

/* Overlay — lebih gelap jika ada gambar */
.thumb-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(0,0,0,0.05) 0%,
    rgba(0,0,0,0.55) 100%
  );
  z-index: 1;
}

/* Jika ada gambar, overlay lebih kuat di bawah */
.card-thumb--image .thumb-overlay {
  background: linear-gradient(
    to bottom,
    rgba(0,0,0,0.1) 0%,
    rgba(0,0,0,0.65) 100%
  );
}

.thumb-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding-bottom: 14px;
  width: 100%;
  text-align: center;
}

.thumb-icon { font-size: 2.5rem; }

.thumb-zona {
  font-size: 11px;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: #f0c98a;
  font-weight: 500;
  text-shadow: 0 1px 4px rgba(0,0,0,.4);
}
.thumb-icon { font-size: 2.5rem; }
.thumb-zona {
  font-size: 11px;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: #c17f3e;
  font-weight: 500;
}

/* Card body */
.card-body { padding: 1.25rem; }
.card-title {
  font-family: 'Playfair Display', serif;
  font-size: 17px;
  color: #1a1a1a;
  margin-bottom: 6px;
}
.card-alamat {
  display: flex;
  align-items: flex-start;
  gap: 5px;
  font-size: 13px;
  color: #8a7968;
  margin-bottom: 8px;
  line-height: 1.5;
}
.pin-icon { width: 14px; height: 14px; flex-shrink: 0; margin-top: 2px; }
.card-desc {
  font-size: 13px;
  color: #a09080;
  line-height: 1.6;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Footer card */
.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #f0ebe3;
  padding-top: .9rem;
  margin-top: .5rem;
}
.card-harga { display: flex; align-items: baseline; gap: 3px; }
.harga-val { font-family: 'Playfair Display', serif; font-size: 20px; color: #c17f3e; }
.harga-unit { font-size: 12px; color: #a09080; }

.btn-lihat {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 16px;
  background: #1a1a1a;
  color: #faf8f4;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
  transition: background .2s;
}
.btn-lihat:hover { background: #333; }
.btn-lihat svg { width: 14px; height: 14px; }

/* FOOTER */
.footer {
  text-align: center;
  padding: 2rem;
  font-size: 13px;
  color: #b5a898;
  border-top: 1px solid #e8e2d9;
}
</style>