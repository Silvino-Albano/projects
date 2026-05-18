import { defineStore } from 'pinia'
import api from '../services/api'

export const useKosStore = defineStore('kos', {
  state: () => ({
    kosList: [],
    loading: false,  // ← tambah ini
    error:   '',     // ← tambah ini
  }),

  actions: {
    async fetchKos() {
      this.loading = true
      this.error   = ''
      try {
        const response = await api.get('/kos')
        this.kosList = response.data.data
      } catch (err) {
        this.error = 'Gagal memuat data kos.'
        console.error(err)
      } finally {
        this.loading = false  // ← selalu matikan loading
      }
    },

    async addKos(data) {
      const res = await api.post('/kos', data)
      this.kosList.push(res.data.data)
    },
  },
})