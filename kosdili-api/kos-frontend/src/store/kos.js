import { defineStore } from 'pinia'
import api from '../services/api'

export const useKosStore = defineStore('kos', {
  state: () => ({
    kosList: [],
    loading: false,
    error:   '',
  }),

  actions: {
    async fetchKos() {
      this.loading = true
      this.error   = ''
      try {
        const res = await api.get('/kos')
        this.kosList = res.data.data
      } catch (err) {
        this.error = 'Gagal memuat data kos.'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async addKos(data) {
      const res = await api.post('/kos', data)
      this.kosList.push(res.data.data)
    },
  },
})