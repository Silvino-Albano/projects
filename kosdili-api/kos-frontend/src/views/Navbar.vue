<template>
  <nav class="navbar">

    <!-- BRAND -->
    <div class="nav-brand">
      <router-link to="/" class="nav-brand-link">
        <span class="nav-icon">⌂</span>
        <span class="nav-title">KosDili</span>
      </router-link>
    </div>

    <!-- TENGAH: navigasi utama -->
    <div class="nav-center" v-if="isLoggedIn">
      <router-link to="/" class="nav-link">Cari Kos</router-link>

      <template v-if="userRole === 'owner' || userRole === 'admin'">
        <router-link to="/owner/dashboard" class="nav-link">Dashboard</router-link>
        <router-link to="/add-kos" class="nav-link">+ Tambah Kos</router-link>
        <router-link to="/owner/komisi" class="nav-link">Komisi</router-link>
        <router-link to="/owner/subscription" class="nav-link">Langganan</router-link>
      </template>

      <template v-if="userRole === 'admin'">
        <router-link to="/admin/kos" class="nav-link admin-link">Admin Panel</router-link>
      </template>

      <template v-if="userRole === 'user' || userRole === 'pencari'">
        <router-link to="/booking/saya" class="nav-link">Booking Saya</router-link>
      </template>
    </div>

    <!-- KANAN: user info + dropdown -->
    <div class="nav-right">

      <!-- Belum login -->
      <template v-if="!isLoggedIn">
        <router-link to="/login"    class="btn-login">Masuk</router-link>
        <router-link to="/register" class="btn-register">Daftar</router-link>
      </template>

      <!-- Sudah login -->
      <template v-else>
        <!-- Dropdown user -->
        <div class="user-menu" ref="userMenu">
          <button class="user-btn" @click="dropdownOpen = !dropdownOpen">
            <!-- Avatar inisial -->
            <div class="user-avatar">{{ userInitial }}</div>

            <!-- Info user -->
            <div class="user-info">
              <span class="user-name">{{ userName }}</span>
              <span class="user-role-badge" :class="userRole">
                {{ roleLabel }}
              </span>
            </div>

            <!-- Panah -->
            <svg class="chevron" :class="{ rotated: dropdownOpen }" viewBox="0 0 12 12" fill="none">
              <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <!-- Dropdown menu -->
          <transition name="dropdown">
            <div v-if="dropdownOpen" class="dropdown-menu">

              <!-- Header dropdown -->
              <div class="dropdown-header">
                <div class="dh-avatar">{{ userInitial }}</div>
                <div>
                  <div class="dh-name">{{ userName }}</div>
                  <div class="dh-email">{{ userEmail }}</div>
                  <span class="dh-role" :class="userRole">{{ roleLabel }}</span>
                </div>
              </div>

              <div class="dropdown-divider"></div>

              <!-- Menu sesuai role -->
              <router-link to="/" class="dropdown-item" @click="dropdownOpen = false">
                <span>🏠</span> Cari Kos
              </router-link>

              <template v-if="userRole === 'user' || userRole === 'pencari'">
                <router-link to="/booking/saya" class="dropdown-item" @click="dropdownOpen = false">
                  <span>📋</span> Booking Saya
                </router-link>
              </template>

              <template v-if="userRole === 'owner' || userRole === 'admin'">
                <router-link to="/owner/dashboard" class="dropdown-item" @click="dropdownOpen = false">
                  <span>📊</span> Dashboard Saya
                </router-link>
                <router-link to="/add-kos" class="dropdown-item" @click="dropdownOpen = false">
                  <span>➕</span> Tambah Kos
                </router-link>
                <router-link to="/owner/bookings" class="dropdown-item" @click="dropdownOpen = false">
                  <span>📬</span> Booking Masuk
                </router-link>
                <router-link to="/owner/komisi" class="dropdown-item" @click="dropdownOpen = false">
                  <span>💰</span> Tagihan Komisi
                </router-link>
                <router-link to="/owner/subscription" class="dropdown-item" @click="dropdownOpen = false">
                  <span>📦</span> Paket Langganan
                </router-link>
              </template>

              <template v-if="userRole === 'admin'">
                <div class="dropdown-divider"></div>
                <router-link to="/admin/kos" class="dropdown-item admin-item" @click="dropdownOpen = false">
                  <span>🛡️</span> Verifikasi Kos
                </router-link>
                <router-link to="/admin/komisi" class="dropdown-item admin-item" @click="dropdownOpen = false">
                  <span>💳</span> Kelola Komisi
                </router-link>
                <router-link to="/admin/subscriptions" class="dropdown-item admin-item" @click="dropdownOpen = false">
                  <span>👥</span> Kelola Langganan
                </router-link>
              </template>

              <div class="dropdown-divider"></div>

              <button class="dropdown-item logout-item" @click="handleLogout">
                <span>🚪</span> Keluar
              </button>
            </div>
          </transition>
        </div>
      </template>

    </div>
  </nav>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth.js'

export default {
  name: 'Navbar',
  setup() {
    const router      = useRouter()
    const authStore   = useAuthStore()
    const dropdownOpen = ref(false)
    const userMenu    = ref(null)

    // ── Ambil data user dari localStorage ─────────────────────
    const getUser = () => {
      try {
        return JSON.parse(localStorage.getItem('user') || '{}')
      } catch {
        return {}
      }
    }

    const isLoggedIn = computed(() => !!localStorage.getItem('token'))

    const userName = computed(() => {
      const u = getUser()
      return u.name || u.nama || 'Pengguna'
    })

    const userEmail = computed(() => {
      const u = getUser()
      return u.email || '-'
    })

    const userRole = computed(() => {
      const u = getUser()
      return u.role || u.peran || 'user'
    })

    // Ambil inisial nama untuk avatar
    const userInitial = computed(() => {
      const name = userName.value
      return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    })

    // Label role yang tampil di badge
    const roleLabel = computed(() => {
      const map = {
        admin   : '👑 Admin',
        owner   : '🏠 Pemilik Kos',
        pemilik : '🏠 Pemilik Kos',
        user    : '🔍 Pencari Kos',
        pencari : '🔍 Pencari Kos',
      }
      return map[userRole.value] || userRole.value
    })

    // Logout
    const handleLogout = async () => {
      dropdownOpen.value = false
      await authStore.logout()
      router.push('/login')
    }

    // Tutup dropdown kalau klik di luar
    const handleClickOutside = (e) => {
      if (userMenu.value && !userMenu.value.contains(e.target)) {
        dropdownOpen.value = false
      }
    }

    onMounted(() => document.addEventListener('click', handleClickOutside))
    onUnmounted(() => document.removeEventListener('click', handleClickOutside))

    return {
      dropdownOpen, userMenu,
      isLoggedIn, userName, userEmail, userRole, userInitial, roleLabel,
      handleLogout,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap');

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .85rem 2.5rem;
  background: #faf8f4;
  border-bottom: 1px solid #e8e2d9;
  position: sticky;
  top: 0;
  z-index: 200;
  gap: 1rem;
}

/* BRAND */
.nav-brand-link {
  display: flex; align-items: center; gap: 8px; text-decoration: none;
}
.nav-icon  { font-size: 20px; }
.nav-title {
  font-family: 'Playfair Display', serif;
  font-size: 18px; color: #1a1a1a; white-space: nowrap;
}

/* CENTER LINKS */
.nav-center {
  display: flex; align-items: center; gap: 4px; flex: 1;
  justify-content: center; flex-wrap: wrap;
}
.nav-link {
  padding: 6px 12px; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: 13px;
  color: #6b5b47; text-decoration: none;
  transition: background .15s, color .15s;
  white-space: nowrap;
}
.nav-link:hover         { background: #f0ebe3; color: #1a1a1a; }
.nav-link.router-link-active { background: #fdf5ec; color: #c17f3e; font-weight: 500; }
.admin-link { color: #534AB7; }
.admin-link:hover { background: #EEEDFE; }
.admin-link.router-link-active { background: #EEEDFE; color: #3C3489; }

/* RIGHT */
.nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.btn-login {
  padding: 7px 16px; border: 1px solid #d4c5b0; border-radius: 8px;
  font-size: 13px; color: #6b5b47; text-decoration: none;
  transition: background .15s;
}
.btn-login:hover { background: #f0ebe3; }

.btn-register {
  padding: 7px 16px; background: #1e1a14; color: #faf8f4;
  border-radius: 8px; font-size: 13px; font-weight: 500;
  text-decoration: none; transition: background .15s;
}
.btn-register:hover { background: #c17f3e; }

/* USER BUTTON */
.user-menu { position: relative; }

.user-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 5px 10px 5px 5px;
  background: none; border: 1px solid #e8e2d9; border-radius: 30px;
  cursor: pointer; transition: all .15s;
  font-family: 'DM Sans', sans-serif;
}
.user-btn:hover { border-color: #c17f3e; background: #fdf5ec; }

/* Avatar lingkaran dengan inisial */
.user-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: linear-gradient(135deg, #c17f3e, #a96c30);
  color: #fff; font-size: 12px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; letter-spacing: .5px;
}

.user-info {
  display: flex; flex-direction: column; align-items: flex-start; gap: 1px;
}
.user-name {
  font-size: 13px; font-weight: 500; color: #1a1a1a;
  max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* Badge role */
.user-role-badge {
  font-size: 9.5px; font-weight: 600; padding: 1px 7px;
  border-radius: 10px; white-space: nowrap;
}
.user-role-badge.admin   { background: #EEEDFE; color: #3C3489; }
.user-role-badge.owner,
.user-role-badge.pemilik { background: #FAEEDA; color: #854F0B; }
.user-role-badge.user,
.user-role-badge.pencari { background: #E1F5EE; color: #0F6E56; }

.chevron {
  width: 13px; height: 13px; color: #8a7968;
  transition: transform .2s; flex-shrink: 0;
}
.chevron.rotated { transform: rotate(180deg); }

/* DROPDOWN MENU */
.dropdown-menu {
  position: absolute; top: calc(100% + 8px); right: 0;
  background: #fff; border: 1px solid #e8e2d9; border-radius: 14px;
  box-shadow: 0 8px 32px rgba(0,0,0,.12);
  min-width: 220px; overflow: hidden; z-index: 500;
}

/* Header dropdown */
.dropdown-header {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 16px; background: #faf8f4;
}
.dh-avatar {
  width: 38px; height: 38px; border-radius: 50%;
  background: linear-gradient(135deg, #c17f3e, #a96c30);
  color: #fff; font-size: 14px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.dh-name  { font-size: 13.5px; font-weight: 600; color: #1a1a1a; }
.dh-email { font-size: 11px; color: #8a7968; margin-top: 1px; }
.dh-role  {
  display: inline-block; font-size: 9.5px; font-weight: 600;
  padding: 2px 8px; border-radius: 10px; margin-top: 4px;
}
.dh-role.admin   { background: #EEEDFE; color: #3C3489; }
.dh-role.owner,
.dh-role.pemilik { background: #FAEEDA; color: #854F0B; }
.dh-role.user,
.dh-role.pencari { background: #E1F5EE; color: #0F6E56; }

.dropdown-divider { height: 1px; background: #f0ebe3; margin: 4px 0; }

.dropdown-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 16px; font-size: 13.5px; color: #4a3f35;
  text-decoration: none; cursor: pointer; transition: background .12s;
  background: none; border: none; width: 100%; text-align: left;
  font-family: 'DM Sans', sans-serif;
}
.dropdown-item:hover    { background: #faf8f4; }
.dropdown-item span:first-child { font-size: 15px; width: 20px; text-align: center; }

.admin-item { color: #3C3489; }
.admin-item:hover { background: #EEEDFE; }

.logout-item { color: #dc2626; }
.logout-item:hover { background: #fee2e2; }

/* Animasi dropdown */
.dropdown-enter-active, .dropdown-leave-active {
  transition: opacity .15s ease, transform .15s ease;
}
.dropdown-enter-from, .dropdown-leave-to {
  opacity: 0; transform: translateY(-8px);
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .nav-center { display: none; }
  .navbar     { padding: .85rem 1.25rem; }
}

@media (max-width: 480px) {
  .user-info  { display: none; }
  .btn-login span, .btn-register span { display: none; }
}
</style>