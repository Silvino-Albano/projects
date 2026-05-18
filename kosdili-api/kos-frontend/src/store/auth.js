import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user:  null,
    // BUG FIX: Inisialisasi user dari localStorage agar tidak hilang saat refresh
    token: localStorage.getItem('token') || null,
  }),

  // BUG FIX: Tambah getter isLoggedIn untuk digunakan route guard
  getters: {
    isLoggedIn: (state) => !!state.token,
  },

 actions: {
  async login(data) {
    const res = await api.post('/login', data)
    this.token = res.data.token
    this.user  = res.data.user

    localStorage.setItem('token', this.token)
    // ✅ Simpan user + role agar bisa dibaca router guard
    localStorage.setItem('user', JSON.stringify(res.data.user))
  },

  async logout() {
    try { await api.post('/logout') } catch {}
    this.user  = null
    this.token = null
    localStorage.removeItem('token')
    localStorage.removeItem('user') // ← hapus juga
  },
}
})
